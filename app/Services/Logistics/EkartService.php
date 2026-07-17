<?php

namespace App\Services\Logistics;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\GeneralSetting;
use Illuminate\Support\Str;

/**
 * eKart Logistics service (India).
 *
 * NOTE: Ekart does not expose a public merchant API. This is a config-driven
 * scaffold so the integration points (shipment creation, tracking, webhook)
 * are in place. Real API base URL / token / endpoints should be filled in the
 * GeneralSetting rows or .env once credentials are available; until then the
 * methods fall back to dummy responses so the flow keeps working.
 */
class EkartService
{
    public $apiKey;
    public $baseUrl;
    public $mode;
    public $authPrefix;
    public $isDummy = false;

    public function __construct()
    {
        $this->ensureSettingsExist();

        $this->mode = GeneralSetting::where('key', 'ekart_mode')->value('value')
            ?? env('EKART_MODE', 'sandbox');

        $this->authPrefix = GeneralSetting::where('key', 'ekart_auth_prefix')->value('value')
            ?? 'Bearer';

        if ($this->mode == 'production') {
            $this->apiKey = GeneralSetting::where('key', 'ekart_prod_token')->value('value')
                ?? env('EKART_PROD_TOKEN', '');
            $this->baseUrl = GeneralSetting::where('key', 'ekart_prod_url')->value('value')
                ?? env('EKART_PROD_URL', 'https://api.ekart.com');
        } else {
            $this->apiKey = GeneralSetting::where('key', 'ekart_demo_token')->value('value')
                ?? env('EKART_DEMO_TOKEN', '');
            $this->baseUrl = GeneralSetting::where('key', 'ekart_demo_url')->value('value')
                ?? env('EKART_DEMO_URL', 'https://sandbox.ekart.com');
        }

        $this->apiKey = trim($this->apiKey);
        $this->baseUrl = rtrim(trim($this->baseUrl), '/');

        // If no real credentials are configured, mark as dummy mode.
        $this->isDummy = empty($this->apiKey) || empty($this->baseUrl);
    }

    /**
     * Ensure default eKart settings exist in the database.
     */
    private function ensureSettingsExist()
    {
        $defaults = [
            'ekart_mode'          => 'sandbox',
            'ekart_demo_email'    => '',
            'ekart_demo_password' => '',
            'ekart_demo_token'    => '',
            'ekart_demo_url'      => 'https://sandbox.ekart.com',
            'ekart_prod_token'    => '',
            'ekart_prod_url'      => 'https://api.ekart.com',
            'ekart_auth_prefix'   => 'Bearer',
        ];

        foreach ($defaults as $key => $value) {
            GeneralSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    /**
     * Standard headers for eKart API.
     */
    protected function getHeaders()
    {
        return [
            'Authorization' => $this->authPrefix . ' ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Generate a dummy tracking id (used when no real API is configured).
     */
    public function generateTrackingId($reference = null)
    {
        $suffix = $reference ? preg_replace('/[^A-Za-z0-9]/', '', $reference) : '';
        return 'EK' . strtoupper(Str::random(10)) . $suffix;
    }

    /**
     * Create a shipment with eKart.
     * Falls back to a dummy booking when no real credentials are set.
     */
    public function createShipment(array $data)
    {
        if ($this->isDummy) {
            Log::info('EkartService: dummy shipment created (no real API configured).', ['reference' => $data['reference_id'] ?? null]);
            return [
                'success'     => true,
                'dummy'       => true,
                'tracking_id' => $this->generateTrackingId($data['reference_id'] ?? null),
                'status'      => 'Booked',
                'message'     => 'Dummy shipment created. Configure eKart API to go live.',
            ];
        }

        try {
            $url = $this->baseUrl . '/api/v1/shipment/create';
            $response = Http::withHeaders($this->getHeaders())
                ->asJson()
                ->post($url, $data);

            $body = $response->json();

            return [
                'success'     => $response->successful(),
                'status'      => $response->status(),
                'tracking_id' => $body['tracking_id'] ?? $body['awb'] ?? null,
                'data'        => $body,
                'raw'         => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('EkartService - createShipment error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Track a shipment by tracking id / AWB.
     */
    public function trackShipment($trackingId)
    {
        if ($this->isDummy) {
            return [
                'success'     => true,
                'dummy'       => true,
                'tracking_id' => $trackingId,
                'status'      => 'In Transit',
                'scans'       => [
                    ['status' => 'Booked', 'location' => 'Origin Hub', 'timestamp' => now()->toDateTimeString()],
                    ['status' => 'In Transit', 'location' => 'Transit Hub', 'timestamp' => now()->toDateTimeString()],
                ],
            ];
        }

        try {
            $url = $this->baseUrl . '/api/v1/shipment/track';
            $response = Http::withHeaders($this->getHeaders())
                ->get($url, ['tracking_id' => $trackingId]);

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
                'raw'     => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('EkartService - trackShipment error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get shipment status history.
     */
    public function getStatus($trackingId)
    {
        return $this->trackShipment($trackingId);
    }

    /**
     * Handle an incoming eKart webhook payload and update the order item.
     * Expects: reference_id (or tracking_id) and status.
     */
    public function handleWebhook(array $payload)
    {
        try {
            $reference = $payload['reference_id']
                ?? $payload['order_reference']
                ?? $payload['reference']
                ?? null;
            $trackingId = $payload['tracking_id'] ?? $payload['awb'] ?? null;
            $status     = $payload['status'] ?? $payload['shipment_status'] ?? null;

            if (!$reference && !$trackingId) {
                return ['success' => false, 'message' => 'Missing reference or tracking id'];
            }

            $query = \App\Models\OrderItem::query();
            if ($trackingId) {
                $query->where('tracking_id', $trackingId);
            } elseif ($reference) {
                $query->whereHas('order', function ($q) use ($reference) {
                    $q->where('reference_id', $reference);
                });
            }

            $updated = 0;
            foreach ($query->get() as $item) {
                $item->logistics_provider = 'eKart';
                if ($trackingId) {
                    $item->tracking_id = $trackingId;
                }
                if ($status) {
                    $item->logistics_status = $status;
                }
                $item->save();
                $updated++;
            }

            return ['success' => true, 'updated' => $updated];
        } catch (\Exception $e) {
            Log::error('EkartService - handleWebhook error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
