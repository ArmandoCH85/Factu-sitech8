<?php

namespace Modules\Sale\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Models\SaleOpportunity;
use App\Models\Tenant\CrmPipelineStage;

/**
 * Class CrmCommercialService
 *
 * Business logic for the KISS CRM's pipeline mutations (crm-comercial-kiss).
 * changeStage() is the only "real" update path; markWon() and markLost() are
 * thin conveniences that delegate to it.
 */
class CrmCommercialService
{
    /**
     * @var CrmActivityService
     */
    protected $activityService;

    public function __construct(CrmActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Move an opportunity to a different pipeline stage. Records the change
     * as a status_change activity and updates won_at / lost_at when the new
     * stage is terminal.
     *
     * @param int         $opportunityId
     * @param string      $stageCode
     * @param string|null $lostReason
     * @return SaleOpportunity
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function changeStage(int $opportunityId, string $stageCode, ?string $lostReason = null): SaleOpportunity
    {
        return DB::connection('tenant')->transaction(function () use ($opportunityId, $stageCode, $lostReason) {
            /** @var CrmPipelineStage $stage */
            $stage = CrmPipelineStage::where('code', $stageCode)->firstOrFail();
            /** @var SaleOpportunity $opportunity */
            $opportunity = SaleOpportunity::findOrFail($opportunityId);

            $oldCode = $opportunity->crmStage ? $opportunity->crmStage->code : null;

            $opportunity->crm_stage_id = $stage->id;
            $opportunity->last_activity_at = Carbon::now();

            if ($stage->is_won) {
                $opportunity->won_at = Carbon::now();
            } elseif ($stage->is_lost) {
                $opportunity->lost_at = Carbon::now();
            }

            $opportunity->save();

            $this->activityService->recordStageChange($opportunity, $oldCode, $stage->code, $lostReason);

            return $opportunity->fresh();
        });
    }

    /**
     * Convenience wrapper for the "Mark Won" shortcut.
     *
     * @param int $opportunityId
     * @return SaleOpportunity
     */
    public function markWon(int $opportunityId): SaleOpportunity
    {
        return $this->changeStage($opportunityId, 'won');
    }

    /**
     * Convenience wrapper for the "Mark Lost" shortcut.
     *
     * @param int    $opportunityId
     * @param string $lostReason
     * @return SaleOpportunity
     */
    public function markLost(int $opportunityId, string $lostReason): SaleOpportunity
    {
        return $this->changeStage($opportunityId, 'lost', $lostReason);
    }
}
