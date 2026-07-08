<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Sale\Models\SaleOpportunity;

/**
 * Class CrmActivity
 *
 * Central activity log for the KISS CRM (crm-comercial-kiss). Each row is one
 * note, call, task, status_change or quotation event tied to a sale_opportunity.
 *
 * @property int    $id
 * @property string $type ('note' | 'call' | 'task' | 'status_change' | 'quotation')
 * @property int    $user_id
 * @property int    $sale_opportunity_id
 * @property string|null $description
 * @property array|null $payload
 * @property string $status ('pending' | 'done')
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $completed_at
 * @property int|null    $completed_by_user_id
 *
 * @mixin ModelTenant
 * @mixin \Eloquent
 */
class CrmActivity extends ModelTenant
{
    protected $table = 'crm_activities';

    protected $with = ['user', 'opportunity'];

    protected $fillable = [
        'type',
        'user_id',
        'sale_opportunity_id',
        'description',
        'payload',
        'status',
        'due_date',
        'completed_at',
        'completed_by_user_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Sale opportunity this activity belongs to.
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(SaleOpportunity::class, 'sale_opportunity_id');
    }

    /**
     * The user that created the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to filter only pending task activities.
     */
    public function scopePendingTasks(Builder $query): Builder
    {
        return $query->where('type', 'task')->where('status', 'pending');
    }

    /**
     * Scope to filter overdue task activities (pending + due_date < now).
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->where('type', 'task')
            ->where('status', 'pending')
            ->where('due_date', '<', now());
    }
}
