<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\FullSuscription\Models\Tenant\SuscriptionOrder;
use Modules\FullSuscription\Models\Tenant\SuscriptionPaymentReminder;

class SuscriptionSendRemindersCommand extends Command
{
    protected $signature = 'suscription:send-reminders';
    protected $description = 'Envía recordatorios de pago de suscripciones según configuración';

    public function handle()
    {
        $currentTime = Carbon::now()->format('H:i');
        $today       = Carbon::today();

        $reminders = SuscriptionPaymentReminder::all();

        foreach ($reminders as $reminder) {
            if ($reminder->reminder_time->format('H:i') !== $currentTime) {
                continue;
            }

            $targetDate = $this->resolveTargetDate($today, $reminder->reminder_type, (int) $reminder->reminder_days);

            if (!$targetDate) {
                continue;
            }

            $orders = SuscriptionOrder::where('status', SuscriptionOrder::STATUS_PENDING)
                ->whereDate('date_of_due', $targetDate->toDateString())
                ->get();

            foreach ($orders as $order) {
                try {
                    $order->notification([$reminder->shipping_medium]);
                } catch (\Throwable $th) {
                    Log::error('suscription:send-reminders error al enviar notificación:', [
                        'order_id'    => $order->id,
                        'reminder_id' => $reminder->id,
                        'error'       => $th->getMessage(),
                        'line'        => $th->getLine(),
                        'file'        => $th->getFile(),
                    ]);
                }
            }

            $this->info("Recordatorio {$reminder->id} procesado: {$orders->count()} órdenes notificadas para {$targetDate->toDateString()}");
        }
    }

    private function resolveTargetDate(Carbon $today, string $reminderType, int $reminderDays): ?Carbon
    {
        return match ($reminderType) {
            SuscriptionPaymentReminder::REMINDER_TYPE_BEFORE   => $today->copy()->addDays($reminderDays),
            SuscriptionPaymentReminder::REMINDER_TYPE_SAME_DAY => $today->copy(),
            SuscriptionPaymentReminder::REMINDER_TYPE_AFTER    => $today->copy()->subDays($reminderDays),
            default => null,
        };
    }
}
