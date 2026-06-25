<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;

class PaytmService
{
    protected $baseUrl;
    protected $merchantId;
    protected $merchantKey;
    protected $mode;
    protected $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('slug', 'paytm')->first();
        
        $this->mode = $this->gateway && $this->gateway->mode == 'live' ? 'production' : 'sandbox';
        
        if ($this->mode === 'production') {
            $this->merchantId = $this->gateway->live_public_key ?: $this->gateway->merchant_id ?: env('PAYTM_MERCHANT_ID', '');
            $this->merchantKey = $this->gateway->live_secret_key ?: env('PAYTM_MERCHANT_KEY', '');
            $this->baseUrl = $this->gateway->live_base_url ?: env('PAYTM_PROD_URL', 'https://securegw.paytm.in');
        } else {
            $this->merchantId = $this->gateway->test_public_key ?: $this->gateway->merchant_id ?: env('PAYTM_MERCHANT_ID', 'TESTMERCHANT123');
            $this->merchantKey = $this->gateway->test_secret_key ?: env('PAYTM_MERCHANT_KEY', 'test_merchant_key');
            $this->baseUrl = $this->gateway->sandbox_base_url ?: env('PAYTM_SANDBOX_URL', 'https://securegw-stage.paytm.in');
        }
    }

    public static function initiatePayment($order)
    {
        $instance = new self();
        
        $orderId = $order->order_reference_id . '-' . time();
        $amount = number_format($order->total_cost, 2, '.', '');
        
        $callbackUrl = $instance->gateway->success_url ?? config('app.url') . '/api/paytm/success';
        
        $requestData = [
            'body' => [
                'requestType' => 'Payment',
                'mid' => $instance->merchantId,
                'websiteName' => 'WEBSTAGING',
                'orderId' => $orderId,
                'callbackUrl' => $callbackUrl,
                'txnAmount' => [
                    'value' => $amount,
                    'currency' => 'INR',
                ],
                'userInfo' => [
                    'custId' => (string)($order->user_id ?? '0'),
                    'mobile' => $order->user->phone ?? '',
                    'email' => $order->user->email ?? '',
                ],
            ],
        ];

        $checksum = self::generateChecksum($requestData['body'], $instance->merchantKey);
        
        $requestData['head'] = [
            'signature' => $checksum,
        ];

        return [
            'status' => true,
            'payment_url' => $instance->baseUrl . '/theia/api/v1/initiateTransaction?mid=' . $instance->merchantId . '&orderId=' . $orderId,
            'formData' => $requestData,
            'orderId' => $orderId,
        ];
    }

    public static function verifyPayment($request)
    {
        $instance = new self();
        
        $paytmChecksum = $request->CHECKSUMHASH ?? '';
        $paytmParams = $request->except('CHECKSUMHASH');
        
        $isValidChecksum = self::verifyChecksum($paytmParams, $instance->merchantKey, $paytmChecksum);
        
        if ($isValidChecksum && ($request->STATUS === 'TXN_SUCCESS')) {
            return ['status' => true, 'data' => $request->all()];
        }
        
        return ['status' => false, 'message' => 'Payment verification failed'];
    }

    private static function generateChecksum($params, $key)
    {
        $sorted = $params;
        ksort($sorted);
        $data = json_encode($sorted);
        return hash_hmac('sha256', $data, $key);
    }

    private static function verifyChecksum($params, $key, $checksum)
    {
        $sorted = $params;
        ksort($sorted);
        $data = json_encode($sorted);
        $expected = hash_hmac('sha256', $data, $key);
        return hash_equals($expected, $checksum);
    }
}
