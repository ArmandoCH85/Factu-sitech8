<?php
namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\System\Configuration;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Models\PaymentConfiguration;
use Modules\Payment\Traits\CulqiTrait;
use Modules\Payment\Traits\IzipayTrait;

class PaymentGatewayController extends Controller 
{
    use CulqiTrait;
    use IzipayTrait;

    public function enabledCheckouts(Request $request)
    {

        $is_tenant = $request->boolean('isTenant', false);
        $checkout = $is_tenant ? PaymentConfiguration::enabledCheckout() : Configuration::enabledCheckout();
        return [
            'checkout' => $checkout,
            // 'is_tenant' => app(CurrentHostname::class) ? true : false
        ];
    }

    //Culqi 

    /**
     * Crear cargo en Culqi (flujo de checkout)
     */
    public function culqiCreateCharge(Request $request)
    {
        $is_tenant = $request->boolean('isTenant', false);
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'currency_code' => 'required|string',
            'email' => 'required|email',
            'source_id' => 'required|string',
        ]);

        $privateKey = $this->culqiCredentials($is_tenant);
        $charge = $this->charge([
            'private_key' => $privateKey
        ], [
            'amount' => $validated['amount'],
            'currency_code' => $validated['currency_code'],
            'email' => $validated['email'],
            'source_id' => $validated['source_id'],
        ]);

        return [
            'success' => $charge ? true : false,
            'charge' => $charge
        ];

    }

    public function culqiRecord(Request $request)
    {
        $is_tenant = $request->boolean('isTenant', false);
        $publickey_culqi =  $is_tenant ? 
            PaymentConfiguration::select('publickey_culqi')->first() : 
            Configuration::select('token_public_culqui')->first();
        return [
            'publickey_culqi' => $publickey_culqi->publickey_culqi ?? $publickey_culqi->token_public_culqui
        ];
    }

    /**
     * Private key de culqi
     */
    private function culqiCredentials(bool $is_tenant = false)
    {
        if (
            $is_tenant
        ) {
            $private_key_culqi = PaymentConfiguration::select('privatekey_culqi')->first()->privatekey_culqi;
        } else {
            $private_key_culqi = Configuration::select('token_private_culqui')->first()->token_private_culqui;
        }

        return $private_key_culqi;

    }


    //Izipay


    public function izipayCreatePayment(Request $request)
    {

        $is_tenant = $request->boolean('isTenant', false);
        $validated = $request->validate([
            'amount'                              => 'required|numeric',
            'currency'                            => 'required|string',
            'orderId'                             => 'nullable|string',
            'customer.email'                      => 'nullable|email',
            'customer.billingDetails.firstName'   => 'nullable|string',
            'customer.billingDetails.lastName'    => 'nullable|string',
            'customer.billingDetails.phoneNumber' => 'nullable',
        ]);


        $credentials = $this->izipayCredentials($is_tenant);
        $result = $this->createPayment($credentials, $validated);

        return [
            'success' => $result ? true : false,
            'formToken' => $result
        ];
    }


    private function izipayCredentials(bool $is_tenant = false)
    {
        if (
            $is_tenant
        ) {
            $credentials = PaymentConfiguration::accessIzipay();
        } else {
            $credentials = Configuration::accessIzipay();
        }
    
        return $credentials;
    }


    public function izipayRecord(Request $request)
    {

        $is_tenant = $request->boolean('isTenant', false);
        $publickey_izipay = $is_tenant ? 
            PaymentConfiguration::select('publickey_izipay')->first()->publickey_izipay : 
            Configuration::select('publickey_izipay')->first()->publickey_izipay;

        return [
            'publickey_izipay' => $publickey_izipay,
        ];
    }

    public function izipayTransaction(Request $request)
    {
        $is_tenant = $request->boolean('isTenant', false);
        $credentials = $this->izipayCredentials($is_tenant);

        $uuid = $request->validate([
            'uuid' => 'required|string'
        ])['uuid'];

        $result = $this->getTransaction($credentials, $uuid);
        $paid = $result['answer']['status'] === 'PAID' ? true : false;
    
        return [
            'success' => $result ? true : false,
            'paid' => $paid,
            'result' => $result
        ];
    }
}