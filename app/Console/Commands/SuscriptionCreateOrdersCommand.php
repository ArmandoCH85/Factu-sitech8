<?php

namespace App\Console\Commands;

use App\Models\Tenant\Configuration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\FullSuscription\Models\Tenant\CatPeriod;
use Modules\FullSuscription\Models\Tenant\SuscriptionOrder;
use Modules\FullSuscription\Models\Tenant\UserRelSuscriptionPlan;

class SuscriptionCreateOrdersCommand extends Command
{
    protected $signature = 'suscription:create-orders';
    protected $description = 'Crea órdenes de cobro para suscripciones próximas a vencer';

    public function handle()
    {
        $config             = Configuration::first();
        $daysBeforeCreation = (int) ($config->before_day_creation_suscription_order ?? 1);
        $today              = Carbon::today();

        $suscriptions = UserRelSuscriptionPlan::with(['suscription_plan', 'cat_period'])
            ->where(function ($query) {
                $query->whereNull('subscription_status')
                      ->orWhere('subscription_status', 'authorized');
            })
            ->get();

        foreach ($suscriptions as $suscription) {
            try {
                $this->processSubscription($suscription, $today, $daysBeforeCreation);
            } catch (\Throwable $th) {
                Log::error('suscription:create-orders error al procesar suscripción:', [
                    'suscription_id' => $suscription->id,
                    'error'          => $th->getMessage(),
                    'line'           => $th->getLine(),
                    'file'           => $th->getFile(),
                ]);
            }
        }
    }

    private function processSubscription(UserRelSuscriptionPlan $suscription, Carbon $today, int $daysBeforeCreation): void
    {
        if (!$suscription->start_date || !$suscription->cat_period_id) {
            return;
        }

        $catPeriod = $suscription->cat_period ?? CatPeriod::find($suscription->cat_period_id);
        if (!$catPeriod) {
            return;
        }

        $ordersCreated = (int) ($suscription->orders_created ?? 0);

        // Si el plan es finito y ya se crearon todas las órdenes, no hacer nada
        $plan = $suscription->suscription_plan;
        if ($plan && !$plan->unlimited && $suscription->quantity_period && $ordersCreated >= $suscription->quantity_period) {
            return;
        }

        $nextDueDate = $this->calculateNextDueDate(
            Carbon::parse($suscription->start_date->format('Y-m-d')),
            $catPeriod->period,
            $ordersCreated
        );

        $creationDate = $nextDueDate->copy()->subDays($daysBeforeCreation);

        if ($today->lt($creationDate) || $today->gt($nextDueDate)) {
            return;
        }

        $exists = SuscriptionOrder::where('suscription_id', $suscription->id)
            ->whereDate('date_of_due', $nextDueDate->toDateString())
            ->exists();

        if ($exists) {
            return;
        }

        SuscriptionOrder::create([
            'suscription_id' => $suscription->id,
            'amount'         => $suscription->total ?? 0,
            'date_of_issue'  => $today,
            'date_of_due'    => $nextDueDate,
            'status'         => SuscriptionOrder::STATUS_PENDING,
        ]);

        $suscription->orders_created = $ordersCreated + 1;
        $suscription->save();

        $this->info("Orden creada para suscripción #{$suscription->id} con vencimiento {$nextDueDate->toDateString()}");
    }

    private function calculateNextDueDate(Carbon $startDate, string $period, int $ordersCreated): Carbon
    {
        return match ($period) {
            'Y' => $startDate->addYears($ordersCreated),
            'D' => $startDate->addDays($ordersCreated),
            'W' => $startDate->addWeeks($ordersCreated),
            'Q' => $startDate->addDays($ordersCreated * 15),
            'B' => $startDate->addMonths($ordersCreated * 2),
            'T' => $startDate->addMonths($ordersCreated * 3),
            'S' => $startDate->addMonths($ordersCreated * 6),
            default => $startDate->addMonths($ordersCreated),
        };
    }
}
