<?php

// Modules/ClaimsBook/Models/Tenant/Claim.php

namespace Modules\ClaimsBook\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use Hyn\Tenancy\Traits\UsesTenantConnection;

/**
 * Modelo principal del libro de reclamaciones.
 * Representa un reclamo o queja registrado por un consumidor,
 * con sus datos de reclamante, producto/servicio, detalle del caso y estado.
 */
class Claim extends ModelTenant
{
    use UsesTenantConnection;

    protected $table = 'claims';

    protected $fillable = [
        // Identificación
        'code',
        'tracking_number',
        'parent_code',

        // Paso 1: datos del reclamante
        'identity_document_type',
        'identity_document_number',
        'name',
        'district_id',
        'address',
        'email',
        'phone',

        // Paso 2: producto/servicio
        'asset_type',
        'asset_description',
        'asset_date',
        'has_receipt',
        'receipt_series',
        'receipt_number',
        'receipt_amount',
        'receipt_currency',

        // Paso 3: detalles del caso
        'claim_type',
        'detail',
        'expected_result',
        'channel',
        'attachment',
        'terms_accepted',

        // Gestión interna
        'status_claim_id',
        'resolution',
        'is_closed',
    ];

    protected $casts = [
        'tracking_number' => 'integer',
        'has_receipt'     => 'boolean',
        'receipt_amount'  => 'float',
        'terms_accepted'  => 'boolean',
        'is_closed'       => 'boolean',
        'asset_date'      => 'date',
    ];

    // ──────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────

    /**
     * Estado actual del reclamo.
     */
    public function statusClaim()
    {
        return $this->belongsTo(StatusClaim::class, 'status_claim_id');
    }

    /**
     * Distrito (ubigeo) del domicilio del reclamante.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Tipo de documento de identidad del reclamante.
     * Usa la columna identity_document_type como FK al catálogo (PK string, sin migración adicional).
     */
    public function identityDocumentType()
    {
        return $this->belongsTo(IdentityDocumentType::class, 'identity_document_type', 'id');
    }

    // ──────────────────────────────────────────────────────────────
    // Datos para API / colecciones Vue
    // ──────────────────────────────────────────────────────────────

    /**
     * Datos resumidos para listar en la tabla de la vista index.
     */
    public function getCollectionData(): array
    {
        return [
            'id'                      => $this->id,
            'code'                    => $this->code,
            'tracking_number'         => $this->tracking_number,
            'parent_code'             => $this->parent_code,

            // Reclamante
            'identity_document_type'        => $this->identity_document_type,
            'identity_document_type_label'  => $this->identityDocumentType
                ? $this->identityDocumentType->description
                : $this->identity_document_type,
            'identity_document_number'      => $this->identity_document_number,
            'name'                          => $this->name,
            'email'                   => $this->email,
            'phone'                   => $this->phone,

            // Tipo de caso
            'claim_type'              => $this->claim_type,
            'claim_type_label'        => $this->claim_type === 'queja' ? 'Queja' : 'Reclamo',

            // Fechas
            'date'                    => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            'date_formatted'          => $this->created_at ? $this->created_at->format('d/m/Y') : null,
            'asset_date'              => $this->asset_date ? $this->asset_date->format('Y-m-d') : null,
            'created_at'              => $this->created_at ? $this->created_at->toDateTimeString() : null,

            // Comprobante
            'has_receipt'             => $this->has_receipt,
            'receipt_series'          => $this->receipt_series,
            'receipt_number'          => $this->receipt_number,
            'receipt_amount'          => $this->receipt_amount,
            'receipt_currency'        => $this->receipt_currency,
            'receipt_label'           => $this->has_receipt
                ? "{$this->receipt_series}-{$this->receipt_number}"
                : null,

            // Estado
            'status_claim_id'         => $this->status_claim_id,
            'status_claim'            => $this->statusClaim
                ? $this->statusClaim->getCollectionData()
                : null,

            // Canal
            'channel'                 => $this->channel,

            // Gestión
            'is_closed'               => $this->is_closed,
            'resolution'              => $this->resolution,
        ];
    }

    /**
     * Datos completos para el modal de detalle (incluye todos los campos del formulario).
     */
    public function getDetailData(): array
    {
        return array_merge($this->getCollectionData(), [
            // Paso 1 completo
            'district_id'             => $this->district_id,
            'district_name'           => $this->district ? $this->district->description : null,
            'address'                 => $this->address,

            // Paso 2 completo
            'asset_type'              => $this->asset_type,
            'asset_description'       => $this->asset_description,

            // Paso 3 completo
            'detail'                  => $this->detail,
            'expected_result'         => $this->expected_result,
            'channel'                 => $this->channel,
            'attachment'              => $this->attachment,
            'attachment_url'          => $this->attachment
                ? asset('storage/' . $this->attachment)
                : null,
            'terms_accepted'          => $this->terms_accepted,
        ]);
    }
}
