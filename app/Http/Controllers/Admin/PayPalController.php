<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    
    public function checkout(Request $request){
         return view('backend/checkout');

    }

      public function createOrder()
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('services.paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "USD",
                    "value" => "50.00"
                ]
            ]]
        ]);

        return response()->json($order);
    }

    public function captureOrder(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('services.paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $response = $provider->capturePaymentOrder($request->orderID);

        return response()->json($response);
    }

}
