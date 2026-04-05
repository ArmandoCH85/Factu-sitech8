<?php

// Modules/ClaimsBook/Models/Tenant/ClaimChannel.php

namespace Modules\ClaimsBook\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;

/**
 * Canal de recepción de reclamos configurado por el tenant.
 * Ejemplos: "Presencial", "Web", "WhatsApp", "Teléfono".
 */
class ClaimChannel extends ModelTenant
{
    use UsesTenantConnection;

    protected $table = 'claim_channels';

    protected $fillable = [
        'name',
    ];

    /**
     * Retorna los datos formateados para respuestas de API.
     */
    public function getCollectionData(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
