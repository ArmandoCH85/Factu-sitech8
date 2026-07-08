<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CrmPipelineStage
 *
 * Catalog table for the KISS CRM (crm-comercial-kiss).
 * Pre-populated via migration seed with 7 stages (new, contacted, interested,
 * proposal, negotiation, won, lost).
 *
 * @property int    $id
 * @property string $code
 * @property string $name
 * @property string $type ('lead' | 'deal' | 'terminal')
 * @property int    $position
 * @property bool   $is_won
 * @property bool   $is_lost
 * @property string|null $color
 *
 * @mixin ModelTenant
 * @mixin \Eloquent
 */
class CrmPipelineStage extends ModelTenant
{
    protected $table = 'crm_pipeline_stages';

    protected $fillable = [
        'code',
        'name',
        'type',
        'position',
        'is_won',
        'is_lost',
        'color',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Scope to filter only lead stages (new, contacted, interested).
     */
    public function scopeLeads(Builder $query): Builder
    {
        return $query->where('type', 'lead');
    }

    /**
     * Scope to filter only deal stages (proposal, negotiation).
     */
    public function scopeDeals(Builder $query): Builder
    {
        return $query->where('type', 'deal');
    }

    /**
     * Scope to filter active stages (excludes terminal won/lost).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('code', ['won', 'lost']);
    }
}
