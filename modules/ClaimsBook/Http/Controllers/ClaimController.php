<?php

// Modules/ClaimsBook/Http/Controllers/ClaimController.php

namespace Modules\ClaimsBook\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Department;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Environment;
use Modules\ClaimsBook\Models\Tenant\Claim;
use Modules\ClaimsBook\Models\Tenant\StatusClaim;
use Modules\ClaimsBook\Models\Tenant\ClaimChannel;
use Modules\ClaimsBook\Http\Resources\ClaimCollection;
use Modules\ClaimsBook\Jobs\SendClaimStatusEmail;

class ClaimController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // Vistas
    // ──────────────────────────────────────────────────────────────

    /**
     * Vista principal del libro de reclamaciones (panel de administración).
     */
    public function index()
    {
        return view('claimsbook::index');
    }

    /**
     * Widget público embebible a través de iframe.
     * Renderiza el formulario en una vista standalone sin el layout del tenant.
     * Agrega el header X-Frame-Options: ALLOWALL para permitir embedding.
     */
    public function widget($slug)
    {
        return response()
            ->view('claimsbook::widget', ['tenant_slug' => $slug])
            ->header('X-Frame-Options', 'ALLOWALL');
    }

    /**
     * Widget interno: ruta limpia sin slug para uso dentro del propio tenant.
     * Resuelve el slug a partir del hostname activo de tenancy.
     */
    public function widgetInternal()
    {
        $hostname = app(CurrentHostname::class);
        $slug     = $hostname ? $hostname->fqdn : request()->getHost();

        return response()
            ->view('claimsbook::widget', ['tenant_slug' => $slug])
            ->header('X-Frame-Options', 'ALLOWALL');
    }

    /**
     * Sirve el script loader JavaScript para integrar el widget en sitios externos.
     * El script crea e inserta el iframe automáticamente usando el hostname del tenant
     * derivado del propio origen de la URL del script.
     */
    public function embedScript()
    {
        $js = <<<'JS'
(function () {
    var script = document.currentScript || (function () {
        var tags = document.getElementsByTagName('script');
        return tags[tags.length - 1];
    }());

    // Derivar el origin y el slug del propio src del script
    var src    = script.src;
    var origin = src.substring(0, src.indexOf('/claims/embed.js'));
    var slug   = (new URL(src)).hostname;

    // Crear el contenedor
    var wrap = document.createElement('div');
    wrap.style.cssText = 'width:100%;';

    // Crear el iframe
    var iframe = document.createElement('iframe');
    iframe.src         = origin + '/claims/widget/' + slug;
    iframe.width       = '100%';
    iframe.height      = '720';
    iframe.frameBorder = '0';
    iframe.scrolling   = 'auto';
    iframe.setAttribute('allow', 'fullscreen');
    iframe.style.cssText = 'border:none;min-height:720px;width:100%;display:block;';

    wrap.appendChild(iframe);
    script.parentNode.insertBefore(wrap, script.nextSibling);
}());
JS;

        return response($js, 200)
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Endpoint público para que el widget obtenga sus datos de referencia
     * (canales, tipos de documento de identidad y cascada de ubicaciones)
     * sin requerir autenticación del tenant.
     */
    public function publicTables($slug)
    {
        // Resolver contexto de tenant por slug para acceder a sus tablas
        $hostname = Hostname::where('fqdn', 'like', "%{$slug}%")->first();

        if (! $hostname) {
            return response()->json(['message' => 'Empresa no encontrada'], 404);
        }

        app(Environment::class)->tenant($hostname->website);

        $claim_channels          = ClaimChannel::orderBy('name')->get(['id', 'name']);
        $identity_document_types = IdentityDocumentType::where('active', true)
            ->orderBy('description')
            ->get(['id', 'description']);
        $locations               = $this->buildLocationCascade();

        return response()->json(compact(
            'claim_channels',
            'identity_document_types',
            'locations'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // API — Datos de tablas auxiliares
    // ──────────────────────────────────────────────────────────────

    /**
     * Retorna los datos de referencia necesarios para cargar la vista index y el formulario:
     * estados, canales, tipos de documento de identidad y ubicaciones en cascada.
     */
    public function tables()
    {
        $status_claims       = StatusClaim::orderBy('sort_order')->get()->map->getCollectionData();
        $claim_channels      = ClaimChannel::orderBy('name')->get()->map->getCollectionData();
        $identity_document_types = IdentityDocumentType::where('active', true)
            ->orderBy('description')
            ->get(['id', 'description']);
        $locations = $this->buildLocationCascade();

        return response()->json(compact(
            'status_claims',
            'claim_channels',
            'identity_document_types',
            'locations'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // API — Listado paginado con filtros
    // ──────────────────────────────────────────────────────────────

    /**
     * Retorna los reclamos con paginación y filtros opcionales:
     * code, name/document, date_from, date_to, status_claim_id, claim_type.
     */
    public function records(Request $request)
    {
        $query = Claim::with('statusClaim', 'identityDocumentType')
            ->orderBy('created_at', 'desc');

        if ($request->filled('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_document_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status_claim_id')) {
            $query->where('status_claim_id', $request->status_claim_id);
        }

        if ($request->filled('claim_type')) {
            $query->where('claim_type', $request->claim_type);
        }

        $records = $query->paginate(config('tenant.items_per_page', 20));

        return new ClaimCollection($records);
    }

    // ──────────────────────────────────────────────────────────────
    // API — Registro de nuevos reclamos
    // ──────────────────────────────────────────────────────────────

    /**
     * Almacena un nuevo reclamo enviado desde el panel del tenant (requiere auth).
     */
    public function store(Request $request)
    {
        return $this->processSave($request);
    }

    /**
     * Endpoint público para registrar reclamos desde el widget embebido.
     * Resuelve el tenant por el campo tenant_slug del payload.
     * Rate limiting aplicado en la definición de la ruta (60/min por IP).
     */
    public function publicStore(Request $request)
    {
        $request->validate([
            'tenant_slug' => 'required|string',
        ]);

        // Resolver contexto de tenant por slug
        $hostname = Hostname::where('fqdn', 'like', "%{$request->tenant_slug}%")->first();

        if (! $hostname) {
            return response()->json(['message' => 'Empresa no encontrada'], 404);
        }

        app(Environment::class)->tenant($hostname->website);

        return $this->processSave($request);
    }

    /**
     * Procesa la validación, generación de código y persistencia de un reclamo.
     * Reutilizado tanto por store() (tenant auth) como publicStore() (widget público).
     */
    protected function processSave(Request $request)
    {
        $request->validate([
            // Paso 1
            'identity_document_type'   => 'required|string|max:10',
            'identity_document_number' => 'required|string|max:20',
            'name'                     => 'required|string|max:200',
            'district_id'              => 'nullable|string|max:6',
            'address'                  => 'nullable|string|max:300',
            'email'                    => 'required|email|max:150',
            'phone'                    => 'nullable|string|max:20',

            // Paso 2
            'asset_type'               => 'required|in:producto,servicio',
            'asset_description'        => 'required|string|max:500',
            'asset_date'               => 'required|date',
            'has_receipt'              => 'boolean',
            'receipt_series'           => 'nullable|string|max:10',
            'receipt_number'           => 'nullable|string|max:20',
            'receipt_amount'           => 'nullable|numeric|min:0',
            'receipt_currency'         => 'nullable|in:PEN,USD',

            // Paso 3
            'claim_type'               => 'required|in:queja,reclamo',
            'detail'                   => 'required|string',
            'expected_result'          => 'required|string',
            'channel'                  => 'nullable|string|max:100',
            'attachment'               => 'nullable|file|max:1024|mimes:jpg,jpeg,png,pdf',
            'terms_accepted'           => 'required|accepted',

            // Reclamo previo (opcional)
            'previous_code'            => 'nullable|string|max:20',
        ]);

        // Resolver el estado inicial para el nuevo reclamo
        $initialStatus = StatusClaim::where('is_initial', true)->first();

        // Verificar si hay un reclamo previo para encadenar
        $previousClaim = null;
        if ($request->filled('previous_code')) {
            $previousClaim = Claim::where('code', $request->previous_code)->first();
        }

        // Procesar adjunto antes de la transacción (operación de I/O fuera del bloque DB)
        $attachmentPath = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $attachmentPath = $request->file('attachment')
                ->store('claims/attachments', 'tenant');
        }

        // Generar código y persistir dentro de una transacción sobre la conexión del tenant.
        // El lockForUpdate() en generateCode requiere una transacción activa para ser efectivo
        // y garantizar que el SELECT y el INSERT sean atómicos (evita correlativos duplicados).
        $connectionName = (new Claim())->getConnectionName();

        $claim = DB::connection($connectionName)->transaction(function () use ($request, $initialStatus, $previousClaim, $attachmentPath) {
            $code           = $this->generateCode($request->claim_type, $previousClaim);
            $trackingNumber = $previousClaim ? ($previousClaim->tracking_number + 1) : 0;
            $parentCode     = $previousClaim
                ? ($previousClaim->parent_code ?? $this->extractParentCode($previousClaim->code))
                : $this->extractParentCode($code);

            return Claim::create([
                'code'                     => $code,
                'tracking_number'          => $trackingNumber,
                'parent_code'              => $parentCode,

                'identity_document_type'   => $request->identity_document_type,
                'identity_document_number' => $request->identity_document_number,
                'name'                     => $request->name,
                'district_id'              => $request->district_id,
                'address'                  => $request->address,
                'email'                    => $request->email,
                'phone'                    => $request->phone,

                'asset_type'               => $request->asset_type,
                'asset_description'        => $request->asset_description,
                'asset_date'               => $request->asset_date,
                'has_receipt'              => $request->boolean('has_receipt'),
                'receipt_series'           => $request->receipt_series,
                'receipt_number'           => $request->receipt_number,
                'receipt_amount'           => $request->receipt_amount,
                'receipt_currency'         => $request->receipt_currency,

                'claim_type'               => $request->claim_type,
                'detail'                   => $request->detail,
                'expected_result'          => $request->expected_result,
                'channel'                  => $request->channel,
                'attachment'               => $attachmentPath,
                'terms_accepted'           => true,

                'status_claim_id'          => $initialStatus ? $initialStatus->id : null,
                'is_closed'                => false,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Reclamo registrado correctamente',
            'code'    => $claim->code,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // API — Detalle
    // ──────────────────────────────────────────────────────────────

    /**
     * Retorna todos los campos de un reclamo para el modal de detalle.
     */
    public function show($id)
    {
        $claim = Claim::with('statusClaim', 'district', 'identityDocumentType')->findOrFail($id);

        return response()->json($claim->getDetailData());
    }

    // ──────────────────────────────────────────────────────────────
    // API — Cambio de estado
    // ──────────────────────────────────────────────────────────────

    /**
     * Actualiza el estado de un reclamo.
     * Si el estado es is_final, exige campo 'resolution' y cierra el reclamo.
     * Encola el job de email si action_send_email está activo en el estado.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_claim_id' => 'required|integer',
            'resolution'      => 'nullable|string',
        ]);

        $claim  = Claim::findOrFail($id);
        // Validar existencia en el tenant con consulta directa (exists: apunta al sistema, no al tenant)
        $status = StatusClaim::find($request->status_claim_id);
        if (! $status) {
            return response()->json(['message' => 'Estado no válido'], 422);
        }

        // Estado final requiere una resolución obligatoria
        if ($status->is_final) {
            $request->validate([
                'resolution' => 'required|string|min:10',
            ]);
        }

        $claim->status_claim_id = $status->id;
        $claim->is_closed       = $status->is_final;

        if ($status->is_final && $request->filled('resolution')) {
            $claim->resolution = $request->resolution;
        }

        $claim->save();

        // Encolar notificación por correo si el estado lo requiere
        if ($status->action_send_email) {
            try {
                dispatch(new SendClaimStatusEmail(
                    $claim->id,
                    $status->id,
                    $this->buildClaimListUrl()
                ));
            } catch (\Throwable $e) {
                Log::error("SendClaimStatusEmail dispatch error: {$e->getMessage()}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // API Pública — Lookup de código
    // ──────────────────────────────────────────────────────────────

    /**
     * Búsqueda pública de un reclamo por código (sin autenticación).
     * Retorna estado y resolución, o 404 si no existe.
     * Usado por el formulario de widget para precargar datos de un reclamo previo.
     */
    public function lookupCode($code)
    {
        $claim = Claim::with('statusClaim', 'identityDocumentType')
            ->where('code', $code)
            ->first();

        if (! $claim) {
            return response()->json(['message' => 'Código no encontrado'], 404);
        }

        return response()->json([
            'found'                    => true,
            'code'                     => $claim->code,
            // Paso 1 — Datos del reclamante
            'identity_document_type'   => $claim->identity_document_type,
            'identity_document_number' => $claim->identity_document_number,
            'name'                     => $claim->name,
            'email'                    => $claim->email,
            'phone'                    => $claim->phone,
            'district_id'              => $claim->district_id,
            'address'                  => $claim->address,
            // Estado y resolución
            'claim_type'               => $claim->claim_type,
            'status'                   => $claim->statusClaim
                ? $claim->statusClaim->getCollectionData()
                : null,
            'resolution'               => $claim->resolution,
            'is_closed'                => $claim->is_closed,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────

    /**
     * Genera el código único para un reclamo siguiendo el formato:
     * {tipo}{MM}{YY}-{correlativo 4d}-{tracking 2d}
     *
     * Si se recibe un $previousClaim, el código se construye a partir de su parent_code:
     * {parent_code}-{tracking_number + 1 con padding 2d}
     */
    private function generateCode(string $claimType, ?Claim $previousClaim): string
    {
        $prefix  = $claimType === 'queja' ? 'Q' : 'R';
        $month   = now()->format('m');
        $year    = now()->format('y');

        if ($previousClaim) {
            // Reclamo relacionado: reutiliza el parent_code y aumenta el tracking
            $parentCode  = $previousClaim->parent_code
                ?? $this->extractParentCode($previousClaim->code);

            $nextTracking = $previousClaim->tracking_number + 1;

            return $parentCode . '-' . str_pad($nextTracking, 2, '0', STR_PAD_LEFT);
        }

        // Nuevo reclamo: calcular el siguiente correlativo para este mes/año/tipo
        $pattern     = "{$prefix}{$month}{$year}-%";
        $lastClaim   = Claim::where('code', 'like', $pattern)
            ->where('tracking_number', 0)
            ->orderBy('code', 'desc')
            ->lockForUpdate()
            ->first();

        $nextCorrelative = 1;
        if ($lastClaim) {
            // Extraer la parte del correlativo: {tipo}{MM}{YY}-{correlativo 4d}-{tracking 2d}
            $parts           = explode('-', $lastClaim->code);
            $nextCorrelative = ((int) ($parts[1] ?? 0)) + 1;
        }

        $correlative = str_pad($nextCorrelative, 4, '0', STR_PAD_LEFT);
        $baseCode    = "{$prefix}{$month}{$year}-{$correlative}";

        return $baseCode . '-00';
    }

    /**
     * Extrae el parent_code a partir del code completo.
     * Ej: "Q032600-0001-00" → "Q032600-0001"
     */
    private function extractParentCode(string $code): string
    {
        $parts = explode('-', $code);
        // El code tiene exactamente 3 segmentos: tipo+mes+año, correlativo, tracking
        return implode('-', array_slice($parts, 0, 2));
    }

    /**
     * Construye la URL absoluta al listado de reclamos para incluir en el email de notificación.
     */
    private function buildClaimListUrl(): string
    {
        $hostname = app(CurrentHostname::class);
        $fqdn     = $hostname ? $hostname->fqdn : config('app.url');
        $protocol = config('tenant.force_https', false) ? 'https' : request()->getScheme();

        return "{$protocol}://{$fqdn}/claims";
    }

    /**
     * Construye la cascada departamento > provincia > distrito para el cascader de Vue.
     * Mismo formato que EcommerceController::getLocationCascade().
     */
    private function buildLocationCascade(): array
    {
        $locations   = [];
        $departments = Department::where('active', true)->get();

        foreach ($departments as $department) {
            $provinces = [];
            foreach ($department->provinces as $province) {
                $districts = [];
                foreach ($province->districts as $district) {
                    $districts[] = [
                        'value' => $district->id,
                        'label' => $district->description,
                    ];
                }
                $provinces[] = [
                    'value'    => $province->id,
                    'label'    => $province->description,
                    'children' => $districts,
                ];
            }
            $locations[] = [
                'value'    => $department->id,
                'label'    => $department->description,
                'children' => $provinces,
            ];
        }

        return $locations;
    }
}
