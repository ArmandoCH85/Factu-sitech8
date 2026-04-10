<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Client;
use App\Models\System\Configuration as SystemConfiguration;
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
        $form = $this->defaultForm();
        $form['tenant_slug'] = $slug;

        return $this->responseView($form, null, null, null, true, $slug, $this->resolveBrandingBySlug($slug));
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
        $request->merge(['tenant_slug' => $slug]);
        $payload = $this->resolveSearch($request);

        return $this->responseView($payload['validated'], $payload['result'], $payload['statusMessage'], $payload['statusType'], true, $slug, $payload['branding']);
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
        ])->header('X-Frame-Options', 'ALLOWALL');
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

        return $branding;
    }

    private function resolveBrandingBySlug(string $slug): array
    {
        return $this->resolveBranding($this->resolveClientBySlug($slug));
    }

    private function defaultBranding(): array
    {
        return [
            'name' => 'Buscador de comprobantes',
            'logo' => $this->resolveSystemLoginLogo(),
            'color' => '#0d8796',
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

