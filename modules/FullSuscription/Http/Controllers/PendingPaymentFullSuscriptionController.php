<?php

namespace Modules\FullSuscription\Http\Controllers;

use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FullSuscription\Models\Tenant\SuscriptionOrder;

class PendingPaymentFullSuscriptionController extends Controller
{
    public function index()
    {
        return view('full_suscription::pending_payments.index');
    }

    public function tables()
    {
        $status_types = SuscriptionOrder::getStatusTypes();
        $message      = Configuration::first()->message_notify_to_suscription_orders;

        return [
            'status_types' => $status_types,
            'message'      => $message,
        ];
    }

    public function getMessage()
    {
        $message = Configuration::first()->message_notify_to_suscription_orders;

        return response()->json(['message' => $message]);
    }

    public function saveMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $configuration = Configuration::first();
        $configuration->message_notify_to_suscription_orders = $request->input('message');
        $configuration->save();

        return response()->json(['success' => true]);
    }

    public function changeStatus(string $id, Request $request)
    {
        $order         = SuscriptionOrder::findOrFail($id);
        $order->status = $request->input('status');

        if ($order->status === SuscriptionOrder::STATUS_PAID) {
            $order->date_of_payment = now();
            $order->generate();
        }

        $order->save();

        return response()->json(['success' => true]);
    }

    public function changeStatusOrders(string $id, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|boolean', // Este es el estado que me devulve los checkouts, true es pagado y falso es pendiente o cualquier otro estado
            'order_id' => 'required|integer', // Este es el id de la orden que me devuelve los checkouts
        ]);

        $order= SuscriptionOrder::findOrFail($validated['order_id']);

        

        // $order->update([
        //     'status' => $validated['status'] ? SuscriptionOrder::STATUS_PAID : SuscriptionOrder::STATUS_PENDING,
        // ])

        // if ($order->status === SuscriptionOrder::STATUS_PAID) {
        //     $order->date_of_payment = now();
        //     $order->generate();
        // }

        // $order->save();

        return response()->json(['success' => true]);
    }

    public function viewOrder(Request $request, string $external_id)
    {
        $order = SuscriptionOrder::with('suscription', 'suscription.suscription_plan')
            ->where('external_id', $external_id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        $co      = Company::select('name', 'trade_name', 'number', 'logo')->first();
        $company = [
            'name' => $co->trade_name ?: $co->name,
            'ruc'  => $co->number,
            'logo' => $co->logo ? asset('storage/uploads/logos/' . $co->logo) : null,
        ];

        return view('full_suscription::pending_payments.view-order', compact('order', 'company'));
    }

    public function sendNotify(Request $request)
    {
        $orderIds = $request->input('multipleIds');
        $orders   = SuscriptionOrder::whereIn('id', $orderIds)->get();

        foreach ($orders as $order) {
            $order->notification(['email']);
        }

        return response()->json(['success' => true, 'message' => 'Notificaciones enviadas correctamente']);
    }

    public function sendNotifyOne(string $id)
    {
        $order = SuscriptionOrder::findOrFail($id);
        $order->notification(['email']);

        return response()->json(['success' => true]);
    }
}
