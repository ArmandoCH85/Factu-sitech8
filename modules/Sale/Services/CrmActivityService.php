<?php

namespace Modules\Sale\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\CrmActivity;
use App\Models\Tenant\CrmPipelineStage;
use Modules\Sale\Models\SaleOpportunity;

/**
 * Class CrmActivityService
 *
 * Service layer for the KISS CRM activity log (crm-comercial-kiss).
 *
 * Every public method wraps its writes in DB::connection('tenant')->transaction()
 * to guarantee atomicity with the per-tenant DB used by hyn/multi-tenant.
 * Internal helpers (recordStageChange, logQuotationIntent) are called by
 * other services within the same transaction.
 */
class CrmActivityService
{
    /**
     * Persist a brand-new activity row and update the parent opportunity's
     * last_activity_at / next_activity_at columns when applicable.
     *
     * @param array $data Validated payload — must include at minimum
     *                    `type`, `sale_opportunity_id` and `description`.
     * @return CrmActivity
     */
    public function store(array $data): CrmActivity
    {
        return DB::connection('tenant')->transaction(function () use ($data) {
            $data['user_id'] = $data['user_id'] ?? auth()->id();
            $data['status'] = $data['status'] ?? 'pending';

            $activity = CrmActivity::create($data);

            $this->touchOpportunityAfterActivity($activity);

            return $activity;
        });
    }

    /**
     * Mark an existing activity as done. For tasks, recompute the parent
     * opportunity's next_activity_at from the remaining pending tasks.
     *
     * @param int $activityId
     * @return CrmActivity
     */
    public function complete(int $activityId): CrmActivity
    {
        return DB::connection('tenant')->transaction(function () use ($activityId) {
            /** @var CrmActivity $activity */
            $activity = CrmActivity::findOrFail($activityId);
            $activity->update([
                'status' => 'done',
                'completed_at' => Carbon::now(),
                'completed_by_user_id' => auth()->id(),
            ]);

            if ($activity->type === 'task') {
                $this->recomputeNextActivityAt($activity->sale_opportunity_id);
            }

            return $activity->fresh();
        });
    }

    /**
     * Internal helper: create the immutable audit row that documents a stage
     * change. Called from CrmCommercialService::changeStage() inside the same
     * transaction.
     *
     * @param SaleOpportunity $opportunity
     * @param string|null     $oldCode
     * @param string          $newCode
     * @param string|null     $reason
     * @return CrmActivity
     */
    public function recordStageChange(
        SaleOpportunity $opportunity,
        ?string $oldCode,
        string $newCode,
        ?string $reason = null
    ): CrmActivity {
        $description = ($oldCode ?? 'none') . ' → ' . $newCode;
        if ($reason) {
            $description .= ': ' . $reason;
        }

        return CrmActivity::create([
            'type' => 'status_change',
            'user_id' => auth()->id(),
            'sale_opportunity_id' => $opportunity->id,
            'description' => $description,
            'status' => 'done',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Internal helper: log a "user intends to create a quotation" event.
     *
     * @param SaleOpportunity $opportunity
     * @return CrmActivity
     */
    public function logQuotationIntent(SaleOpportunity $opportunity): CrmActivity
    {
        return CrmActivity::create([
            'type' => 'quotation',
            'user_id' => auth()->id(),
            'sale_opportunity_id' => $opportunity->id,
            'description' => 'Redirigido a crear cotización',
            'status' => 'done',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Update last_activity_at / next_activity_at on the opportunity tied to
     * the freshly-created activity. Pulled out so it can be reused without
     * creating yet another transaction.
     */
    protected function touchOpportunityAfterActivity(CrmActivity $activity): void
    {
        /** @var SaleOpportunity|null $opportunity */
        $opportunity = SaleOpportunity::find($activity->sale_opportunity_id);
        if (!$opportunity) {
            return;
        }

        $opportunity->last_activity_at = Carbon::now();

        if ($activity->type === 'task' && $activity->due_date) {
            // Only overwrite next_activity_at when this new task is sooner than
            // any other pending task already linked to the opportunity.
            $earlierPending = CrmActivity::query()
                ->where('sale_opportunity_id', $opportunity->id)
                ->where('type', 'task')
                ->where('status', 'pending')
                ->where('id', '!=', $activity->id)
                ->min('due_date');

            $candidate = Carbon::parse($activity->due_date);
            if ($earlierPending === null || Carbon::parse($earlierPending)->gt($candidate)) {
                $opportunity->next_activity_at = $candidate;
            }
        }

        $opportunity->save();
    }

    /**
     * Recompute next_activity_at on the opportunity from the next pending task
     * (in chronological order). If no pending tasks remain, set it to NULL.
     */
    protected function recomputeNextActivityAt(int $opportunityId): void
    {
        $next = CrmActivity::query()
            ->where('sale_opportunity_id', $opportunityId)
            ->where('type', 'task')
            ->where('status', 'pending')
            ->min('due_date');

        SaleOpportunity::where('id', $opportunityId)->update([
            'next_activity_at' => $next ? Carbon::parse($next) : null,
        ]);
    }
}
