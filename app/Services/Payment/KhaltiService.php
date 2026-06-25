<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;

class KhaltiService
{
    protected $baseUrl;
    protected $secretKey;
    protected $mode;
    protected $gateway;
    protected $returnUrl;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('slug', 'khalti')->first();
        
        $this->mode = $this->gateway && $this->gateway->mode == 'live' ? 'production' : 'sandbox';
        
        if ($this->mode === 'production') {
            $this->secretKey = $this->gateway->live_secret_key ?: $this->gateway->secret_key ?: env('KHALTI_LIVE_SECRET_KEY', '');
            $this->baseUrl = $this->gateway->live_base_url ?: env('KHALTI_LIVE_BASE_URL', 'https://khalti.com/api/v2');
        } else {
            $this->secretKey = $this->gateway->test_secret_key ?: $this->gateway->secret_key ?: env('KHALTI_TEST_SECRET_KEY', env('KHALTI_LIVE_SECRET_KEY', ''));
            $this->baseUrl = $this->gateway->sandbox_base_url ?: env('KHALTI_TEST_BASE_URL', env('KHALTI_BASE_URL', 'https://dev.khalti.com/api/v2'));
        }

        $this->returnUrl = $this->gateway?->success_url ?: env('KHALTI_RETURN_URL', env('KHALTI_VERIFY_URL', config('app.url') . '/api/khalti/verify'));
    }

    /**
     * Initiate Khalti Payment
     */
    public static function initiatePayment($order, $user)
    {
        $instance = new self();
        
        Log::info('Khalti Service Config:', [
            'mode' => $instance->mode,
            'baseUrl' => $instance->baseUrl,
            'returnUrl' => $instance->returnUrl,
            'secretKeyLength' => strlen($instance->secretKey),
        ]);

        $amount = (int) ($order->total_cost * 100);
        if ($amount < 1000) {
            Log::warning('Khalti Initiation: Amount too low (' . $amount . ' paisa). Adjusting to minimum 1000.');
            $amount = 1000;
        }

        $cleanOrderId = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $order->order_reference_id);
        $payload = [
            'return_url' => $instance->returnUrl,
            'website_url' => config('app.url'),
            'amount' => $amount,
            'purchase_order_id' => $cleanOrderId,
            'purchase_order_name' => 'Order ' . $cleanOrderId,
            'customer_info' => [
                'name' => $user->name,
                'email' => $user->email ?? 'test@example.com',
                'phone' => $user->phone ?? '9800000000',
            ]
        ];

        try {
            $key = $instance->secretKey;
            if ($instance->mode === 'production' && !str_starts_with($key, 'live_secret_key_')) {
                $key = 'live_secret_key_' . $key;
            }

            Log::info('Khalti Initiation Payload:', $payload);
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $instance->baseUrl . '/epayment/initiate/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Key ' . $key,
                    'Content-Type: application/json',
                ),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            Log::info('Khalti Initiation Response:', [
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $curlError
            ]);

            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($response, true);
                Log::info('Khalti Initiation Successful:', $data);

                $paymentUrl = $data['payment_url'];
                $parsedUrl = parse_url($paymentUrl);
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                    unset($queryParams['return_url']);
                    $newQuery = http_build_query($queryParams);
                    $paymentUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . ($newQuery ? '?' . $newQuery : '');
                }

                return [
                    'status' => true,
                    'payment_url' => $paymentUrl,
                    'pidx' => $data['pidx'],
                    'payload' => $payload
                ];
            }

            $errorData = json_decode($response, true);
            Log::error('Khalti Initiation Failed', [
                'http_code' => $httpCode,
                'error' => $errorData,
                'response' => $response
            ]);
            return [
                'status' => false,
                'message' => isset($errorData['detail']) ? $errorData['detail'] : 'Khalti payment initiation failed.',
                'error' => $errorData,
                'payload' => $payload
            ];

        } catch (\Exception $e) {
            Log::error('Khalti Initiation Exception: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return [
                'status' => false,
                'message' => 'An error occurred during Khalti initiation: ' . $e->getMessage(),
                'payload' => $payload
            ];
        }
    }

    /**
     * Verify Khalti Payment Status (Lookup)
     */
    public static function verifyPayment($pidx)
    {
        $instance = new self();

        try {
            $key = $instance->secretKey;
            if ($instance->mode === 'production' && !str_starts_with($key, 'live_secret_key_')) {
                $key = 'live_secret_key_' . $key;
            }

            $payload = ['pidx' => $pidx];
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $instance->baseUrl . '/epayment/lookup/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Key ' . $key,
                    'Content-Type: application/json',
                ),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode >= 200 && $httpCode < 300) {
                    $data = json_decode($response, true);
                return [
                    'status' => true,
                    'payment_status' => $data['status'] ?? null,
                    'data' => $data
                ];
            }

            Log::error('Khalti Verification Failed: ' . $response);
            return [
                'status' => false,
                'message' => 'Khalti verification failed.'
            ];

        } catch (\Exception $e) {
            Log::error('Khalti Verification Error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'An error occurred during Khalti verification.'
            ];
        }
    }
}
