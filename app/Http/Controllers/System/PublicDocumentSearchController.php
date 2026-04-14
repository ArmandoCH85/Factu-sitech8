<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Client;
use App\Models\System\Configuration as SystemConfiguration;
use App\Models\System\PublicSearchCustomization;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Person;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Http\Request;

class PublicDocumentSearchController extends Controller
{
    /**
     * Catálogo de tipos permitidos para consulta pública.
     */
    private const DOCUMENT_TYPES = [
        '01' => 'Factura Electrónica',
        '03' => 'Boleta Electrónica',
        '07' => 'Nota de Crédito',
        '08' => 'Nota de Débito',
    ];

    public function index()
    {
        return $this->responseView($this->defaultForm(), null, null, null, false, null, $this->resolveBranding());
    }

    public function widget(string $slug)
    {
        $resolvedSlug = $this->resolveWidgetSlugOrDefault($slug);
        if ($resolvedSlug !== $slug) {
            return redirect()->route('system.public_search.widget', ['slug' => $resolvedSlug]);
        }

        $form = $this->defaultForm();
        $form['tenant_slug'] = $resolvedSlug;

        return $this->responseView($form, null, null, null, true, $resolvedSlug, $this->resolveBrandingBySlug($resolvedSlug));
    }

    public function widgetInternal()
    {
        $hostname = app(CurrentHostname::class);
        $slug = $hostname ? $hostname->fqdn : request()->getHost();

        return $this->widget($slug);
    }

    public function tenantForm()
    {
        $slug = $this->currentTenantSlug();
        $form = $this->defaultForm();
        $form['tenant_slug'] = $slug;

        return $this->responseView(
            $form,
            null,
            null,
            null,
            false,
            $slug,
            $this->resolveBrandingBySlug($slug)
        );
    }

    public function tenantSearch(Request $request)
    {
        $slug = $this->currentTenantSlug();
        $request->merge(['tenant_slug' => $slug]);

        $payload = $this->resolveSearch($request);

        return $this->responseView(
            $payload['validated'],
            $payload['result'],
            $payload['statusMessage'],
            $payload['statusType'],
            false,
            $slug,
            $payload['branding']
        );
    }

    public function updateTenantBackground(Request $request)
    {
        return $this->storeBackgroundColor($request);
    }

    public function updateWidgetBackground(Request $request, string $slug)
    {
        return $this->storeBackgroundColor($request, $this->resolveWidgetSlugOrDefault($slug));
    }

    public function embedScript()
    {
        $js = <<<'JS'
(function () {
    var script = document.currentScript || (function () {
        var tags = document.getElementsByTagName('script');
        return tags[tags.length - 1];
    }());

    var src = script.src;
    var origin = src.substring(0, src.indexOf('/consultas/embed.js'));
    var parsed = new URL(src, window.location.href);
    var slug = parsed.searchParams.get('tenant') || parsed.searchParams.get('slug') || parsed.hostname;

    var wrap = document.createElement('div');
    wrap.style.cssText = 'width:100%;';

    var iframe = document.createElement('iframe');
    iframe.src = origin + '/consultas/widget/' + slug;
    iframe.width = '100%';
    iframe.height = '720';
    iframe.frameBorder = '0';
    iframe.scrolling = 'auto';
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

    public function search(Request $request)
    {
        $payload = $this->resolveSearch($request);

        $tenantSlug = $payload['validated']['tenant_slug'] ?? null;

        return $this->responseView($payload['validated'], $payload['result'], $payload['statusMessage'], $payload['statusType'], false, $tenantSlug, $payload['branding']);
    }

    public function searchWidget(Request $request, string $slug)
    {
        $resolvedSlug = $this->resolveWidgetSlugOrDefault($slug);
        if ($resolvedSlug !== $slug) {
            return redirect()->route('system.public_search.widget', ['slug' => $resolvedSlug]);
        }

        $request->merge(['tenant_slug' => $resolvedSlug]);
        $payload = $this->resolveSearch($request);

        return $this->responseView($payload['validated'], $payload['result'], $payload['statusMessage'], $payload['statusType'], true, $resolvedSlug, $payload['branding']);
    }

    public function searchApi(Request $request)
    {
        $payload = $this->resolveSearch($request);

        return response()->json([
            'success' => $payload['result'] !== null,
            'message' => $payload['statusMessage'] ?? ($payload['result'] ? 'Comprobante encontrado' : 'Sin resultados'),
            'data' => $payload['result'],
        ]);
    }

    private function responseView(
        array $form,
        ?array $result,
        ?string $statusMessage,
        ?string $statusType,
        bool $embedded = false,
        ?string $tenantSlug = null,
        array $branding = []
    )
    {
        $allowTenantCustomization = request()->routeIs('tenant.public_search.form')
            || request()->routeIs('tenant.public_search.form.search');

        $backgroundUpdateUrl = null;
        if ($allowTenantCustomization) {
            $backgroundUpdateUrl = route('tenant.public_search.background.update');
        } elseif (!empty($tenantSlug)) {
            $backgroundUpdateUrl = route('system.public_search.widget.background.update', ['slug' => $tenantSlug]);
        }

        return response()->view('system.public-search.index', [
            'documentTypes' => self::DOCUMENT_TYPES,
            'form' => array_merge($this->defaultForm(), $form),
            'result' => $result,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'embedded' => $embedded,
            'tenantSlug' => $tenantSlug,
            'brand' => array_merge($this->defaultBranding(), $branding),
            'allowTenantCustomization' => $allowTenantCustomization,
            'backgroundUpdateUrl' => $backgroundUpdateUrl,
        ])->header('X-Frame-Options', 'ALLOWALL');
    }

    private function storeBackgroundColor(Request $request, ?string $slug = null)
    {
        $validated = $request->validate([
            'background_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $slug = $slug ?: $this->currentTenantSlug();
        if (empty($slug)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar el slug para guardar la personalización.',
            ], 422);
        }

        $color = !empty($validated['background_color']) ? strtolower($validated['background_color']) : null;

        PublicSearchCustomization::query()->updateOrCreate(
            ['slug' => $slug],
            ['background_color' => $color]
        );

        $client = $this->resolveClientBySlug($slug);
        if ($client && $client->hostname) {
            /** @var Environment $tenancy */
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);

            $configuration = Configuration::first();
            if ($configuration) {
                $configuration->public_search_bg_color = $color;
                $configuration->save();
            }
        }

        return response()->json([
            'success' => true,
            'background_color' => $color,
        ]);
    }

    private function resolveSearch(Request $request): array
    {
        $validated = $request->validate([
            'tenant_slug' => ['nullable', 'string', 'max:255'],
            'ruc_emisor' => ['nullable', 'digits:11', 'required_without:tenant_slug'],
            'document_type_id' => ['required', 'in:01,03,07,08'],
            'series' => ['required', 'string', 'max:10'],
            'number' => ['required', 'string', 'max:20'],
            'customer_number' => ['required', 'string', 'max:15'],
            'total' => ['required', 'numeric', 'min:0'],
            'date_of_issue' => ['required', 'date_format:Y-m-d'],
        ]);

        $result = null;
        $statusMessage = null;
        $statusType = null;

        $client = $this->resolveClient($validated);
        $branding = $this->resolveBranding($client);

        if (!$client || !$client->hostname) {
            $statusMessage = 'No se encontró una empresa activa con los datos ingresados.';
            $statusType = 'warning';
        } elseif ((bool) $client->locked_tenant) {
            $statusMessage = 'La empresa emisora se encuentra temporalmente inactiva.';
            $statusType = 'warning';
        } else {
            /** @var Environment $tenancy */
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);

            $customer = Person::query()
                ->where('number', $validated['customer_number'])
                ->where('type', 'customers')
                ->first();

            if (!$customer) {
                $statusMessage = 'El número de documento del cliente no fue encontrado.';
                $statusType = 'warning';
            } else {
                $document = $this->resolveDocument($validated, (int) $customer->id);
                if (!$document) {
                    $statusMessage = 'No se encontró un comprobante con los datos ingresados.';
                    $statusType = 'warning';
                } else {
                    $baseUrl = '//' . $client->hostname->fqdn;
                    $result = [
                        'customer' => $document->customer->number,
                        'number' => $document->series . '-' . $document->number,
                        'total' => number_format((float) $document->total, 2, '.', ''),
                        'download_xml' => $baseUrl . '/downloads/document/xml/' . $document->external_id,
                        'download_pdf' => $baseUrl . '/downloads/document/pdf/' . $document->external_id,
                    ];
                }
            }
        }

        return [
            'validated' => $validated,
            'result' => $result,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'branding' => $branding,
        ];
    }

    private function resolveClient(array $validated): ?Client
    {
        if (!empty($validated['tenant_slug'])) {
            $hostname = Hostname::query()
                ->where('fqdn', $validated['tenant_slug'])
                ->first();

            if ($hostname) {
                $clientBySlug = Client::query()
                    ->whereHas('hostname', function ($query) use ($hostname) {
                        $query->where('id', $hostname->id);
                    })
                    ->whereHas('hostname.website')
                    ->first();

                if ($clientBySlug) {
                    return $clientBySlug;
                }
            }
        }

        if (!empty($validated['ruc_emisor'])) {
            return Client::query()
                ->where('number', $validated['ruc_emisor'])
                ->whereHas('hostname.website')
                ->first();
        }

        return null;
    }

    private function resolveClientBySlug(string $slug): ?Client
    {
        $hostname = Hostname::query()
            ->where('fqdn', $slug)
            ->first();

        if (!$hostname) {
            return null;
        }

        return Client::query()
            ->whereHas('hostname', function ($query) use ($hostname) {
                $query->where('id', $hostname->id);
            })
            ->whereHas('hostname.website')
            ->first();
    }

    private function resolveBranding(?Client $client = null): array
    {
        $branding = $this->defaultBranding();

        if (!$client || !$client->hostname) {
            return $branding;
        }

        /** @var Environment $tenancy */
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);

        $company = Company::active();
        $configuration = Configuration::first();

        if ($company) {
            $branding['name'] = $company->trade_name ?: $company->name ?: $branding['name'];
            $branding['ruc'] = $company->number;

            if (!empty($company->logo)) {
                $branding['logo'] = asset('storage/uploads/logos/' . $company->logo);
            } elseif (!empty($company->app_logo)) {
                $branding['logo'] = asset('storage/uploads/logos/' . $company->app_logo);
            }
        }

        $branding = $this->applyConfigurationBranding($branding, $configuration);

        return $branding;
    }

    private function applyConfigurationBranding(array $branding, ?Configuration $configuration): array
    {
        if (!$configuration) {
            return $branding;
        }

        if (!empty($configuration->login_bg_color)) {
            $branding['color'] = $configuration->login_bg_color;
        }

        if (!empty($configuration->public_search_bg_color)) {
            $branding['bg_color'] = $configuration->public_search_bg_color;
        }

        return $branding;
    }

    private function resolveBrandingBySlug(string $slug): array
    {
        $branding = $this->resolveBranding($this->resolveClientBySlug($slug));
        $customBackground = $this->resolveBackgroundBySlug($slug);

        if (!empty($customBackground)) {
            $branding['bg_color'] = $customBackground;
        }

        return $branding;
    }

    private function resolveBackgroundBySlug(?string $slug): ?string
    {
        if (empty($slug)) {
            return null;
        }

        $customization = PublicSearchCustomization::query()
            ->where('slug', $slug)
            ->first();

        return $customization ? $customization->background_color : null;
    }

    private function defaultBranding(): array
    {
        return [
            'name' => 'Buscador de comprobantes',
            'logo' => $this->resolveSystemLoginLogo(),
            'color' => '#0d8796',
            'bg_color' => null,
            'ruc' => null,
        ];
    }

    private function resolveSystemLoginLogo(): ?string
    {
        try {
            $configuration = SystemConfiguration::first();

            if (!$configuration || !$configuration->login || empty($configuration->login->logo)) {
                return null;
            }

            return $configuration->login->logo;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function currentTenantSlug(): ?string
    {
        $hostname = app(CurrentHostname::class);

        if ($hostname && !empty($hostname->fqdn)) {
            return $hostname->fqdn;
        }

        return request()->getHost();
    }

    private function resolveWidgetSlugOrDefault(string $slug): string
    {
        if ($this->resolveClientBySlug($slug)) {
            return $slug;
        }

        $defaultSlug = $this->currentTenantSlug();

        if (!empty($defaultSlug)) {
            return $defaultSlug;
        }

        return $slug;
    }

    private function resolveDocument(array $validated, int $customerId): ?Document
    {
        $series = strtoupper(trim($validated['series']));
        $number = (int) $validated['number'];
        $total = round((float) $validated['total'], 2);

        return Document::query()
            ->where('date_of_issue', $validated['date_of_issue'])
            ->where('document_type_id', $validated['document_type_id'])
            ->where('series', $series)
            ->where('number', $number)
            ->whereBetween('total', [$total - 0.01, $total + 0.01])
            ->where('customer_id', $customerId)
            ->first();
    }

    private function defaultForm(): array
    {
        return [
            'tenant_slug' => null,
            'ruc_emisor' => null,
            'document_type_id' => '01',
            'date_of_issue' => now()->format('Y-m-d'),
            'series' => null,
            'number' => null,
            'total' => null,
            'customer_number' => null,
        ];
    }
}

