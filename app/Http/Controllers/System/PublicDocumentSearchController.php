<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Client;
use App\Models\Tenant\Document;
use App\Models\Tenant\Person;
use Hyn\Tenancy\Environment;
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
        return view('system.public-search.index', [
            'documentTypes' => self::DOCUMENT_TYPES,
            'form' => $this->defaultForm(),
            'result' => null,
            'statusMessage' => null,
            'statusType' => null,
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'ruc_emisor' => ['required', 'digits:11'],
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

        $client = Client::query()
            ->where('number', $validated['ruc_emisor'])
            ->whereHas('hostname.website')
            ->first();

        if (!$client || !$client->hostname) {
            $statusMessage = 'No se encontró una empresa activa para el RUC emisor ingresado.';
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
                $series = strtoupper(trim($validated['series']));
                $number = (int) $validated['number'];
                $total = round((float) $validated['total'], 2);

                $document = Document::query()
                    ->where('date_of_issue', $validated['date_of_issue'])
                    ->where('document_type_id', $validated['document_type_id'])
                    ->where('series', $series)
                    ->where('number', $number)
                    ->whereBetween('total', [$total - 0.01, $total + 0.01])
                    ->where('customer_id', $customer->id)
                    ->first();

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

        return $this->responseView($validated, $result, $statusMessage, $statusType);
    }

    private function responseView(array $form, ?array $result, ?string $statusMessage, ?string $statusType)
    {
        return view('system.public-search.index', [
            'documentTypes' => self::DOCUMENT_TYPES,
            'form' => array_merge($this->defaultForm(), $form),
            'result' => $result,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
        ]);
    }

    private function defaultForm(): array
    {
        return [
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

