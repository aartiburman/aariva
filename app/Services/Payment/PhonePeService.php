<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;

class PhonePeService
{
    protected $baseUrl;
    protected $merchantId;
    protected $saltKey;
    protected $saltIndex;
    protected $mode;
    protected $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('slug', 'phonepe')->first();
        
        $this->mode = $this->gateway && $this->gateway->mode == 'live' ? 'production' : 'sandbox';
        
        if ($this->mode === 'production') {
            $this->merchantId = $this->gateway->live_public_key ?: $this->gateway->merchant_id ?: env('PHONEPE_MERCHANT_ID', '');
            $this->saltKey = $this->gateway->live_secret_key ?: env('PHONEPE_SALT_KEY', '');
            $this->saltIndex = $this->gateway->app_id ?: env('PHONEPE_SALT_INDEX', '1');
            $this->baseUrl = $this->gateway->live_base_url ?: env('PHONEPE_PROD_URL', 'https://api.phonepe.com/apis/hermes');
        } else {
            $this->merchantId = $this->gateway->test_public_key ?: $this->gateway->merchant_id ?: env('PHONEPE_MERCHANT_ID', 'PGTESTPAYUAT');
            $this->saltKey = $this->gateway->test_secret_key ?: env('PHONEPE_SALT_KEY', '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399');
            $this->saltIndex = $this->gateway->app_id ?: env('PHONEPE_SALT_INDEX', '1');
            $this->baseUrl = $this->gateway->sandbox_base_url ?: env('PHONEPE_SANDBOX_URL', 'https://api-preprod.phonepe.com/apis/pg-sandbox');
        }
    }

    public static function initiatePayment($order)
    {
        $instance = new self();
        
        $merchantTransactionId = $order->order_reference_id . '-' . time();
        $amount = (int) round($order->total_cost * 100); // Convert to paise
        
        $data = [
            'merchantId' => $instance->merchantId,
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => 'MUID-' . ($order->user_id ?? '0'),
            'amount' => $amount,
            'redirectUrl' => $instance->gateway->success_url ?? config('app.url') . '/api/phonepe/success',
            'redirectMode' => 'POST',
            'callbackUrl' => $instance->gateway->failure_url ?? config('app.url') . '/api/phonepe/failure',
            'mobileNumber' => $order->user->phone ?? '',
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        $jsonData = json_encode($data);
        $base64Data = base64_encode($jsonData);
        $hashString = $base64Data . '/pg/v1/pay' . $instance->saltKey;
        $hash = hash('sha256', $hashString) . '###' . $instance->saltIndex;

        return [
            'status' => true,
            'payment_url' => $instance->baseUrl . '/pg/v1/pay',
            'formData' => [
                'request' => $base64Data,
                'merchantId' => $instance->merchantId,
                'salt_key' => $instance->saltKey,
                'hash' => $hash,
            ],
            'merchantTransactionId' => $merchantTransactionId,
        ];
    }

    public static function verifyPayment($transactionId)
    {
        $instance = new self();
        
        $hashString = '/pg/v1/status/' . $instance->merchantId . '/' . $transactionId . $instance->saltKey;
        $hash = hash('sha256', $hashString) . '###' . $instance->saltIndex;
        
        $url = $instance->baseUrl . '/pg/v1/status/' . $instance->merchantId . '/' . $transactionId;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'accept: application/json',
                'X-VERIFY: ' . $hash,
                'X-MERCHANT-ID: ' . $instance->merchantId,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        Log::info('PhonePe verify response', ['http_code' => $httpCode, 'response' => $response]);
        
        if ($httpCode == 200) {
            $result = json_decode($response, true);
            if (isset($result['code']) && $result['code'] === 'PAYMENT_SUCCESS') {
                return ['status' => true, 'data' => $result];
            }
            return ['status' => false, 'message' => $result['message'] ?? 'Payment not successful', 'data' => $result];
        }
        
        return ['status' => false, 'message' => 'Failed to verify payment'];
    }
}
