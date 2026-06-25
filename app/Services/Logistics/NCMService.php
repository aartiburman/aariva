<?php

namespace App\Services\Logistics;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\GeneralSetting;

class NCMService
{
    public $apiKey;
    public $baseUrl;
    public $mode;
    public $authPrefix;

    public function __construct()
    {
        // Ensure NCM Settings exist in GeneralSetting with defaults if not present
        $this->ensureSettingsExist();

        // Get mode and prefix from DB
        $this->mode = GeneralSetting::where('key', 'ncm_mode')->value('value') 
                      ?? env('NCM_MODE', 'sandbox');
        
        $this->authPrefix = GeneralSetting::where('key', 'ncm_auth_prefix')->value('value') 
                            ?? 'Token';

        if ($this->mode == 'production') {
            $this->apiKey = GeneralSetting::where('key', 'ncm_prod_token')->value('value') ?? env('NCM_PROD_TOKEN', '0c593255a1805c938fd006ab01db5465fa680d8c');
            $this->baseUrl = GeneralSetting::where('key', 'ncm_prod_url')->value('value') ?? env('NCM_PROD_URL', 'https://portal.nepalcanmove.com');
        } else {
            $this->apiKey = GeneralSetting::where('key', 'ncm_demo_token')->value('value') ?? env('NCM_DEMO_TOKEN', '0188e3a02adb5d735535830bff20849d54b967ab');
            $this->baseUrl = GeneralSetting::where('key', 'ncm_demo_url')->value('value') ?? env('NCM_DEMO_URL', 'https://demo.nepalcanmove.com');
        }

        $this->apiKey = trim($this->apiKey);
        $this->baseUrl = rtrim($this->baseUrl, '/');

        // Safety check for common URL mistake
        if ($this->baseUrl === 'https://nepalcanmove.com') {
            $this->baseUrl = 'https://portal.nepalcanmove.com';
        }
    }

    /**
     * Ensure default NCM settings exist in the database
     */
    private function ensureSettingsExist()
    {
        $defaults = [
            'ncm_mode' => 'sandbox',
            'ncm_demo_email' => 'demovendor@ncm.com',
            'ncm_demo_password' => 'm0hgMuP7rP',
            'ncm_demo_token' => '0188e3a02adb5d735535830bff20849d54b967ab',
            'ncm_demo_url' => 'https://demo.nepalcanmove.com',
            'ncm_prod_token' => '0c593255a1805c938fd006ab01db5465fa680d8c',
            'ncm_prod_url' => 'https://portal.nepalcanmove.com',
        ];

        foreach ($defaults as $key => $value) {
            GeneralSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    /**
     * Standard Headers for NCM API
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
     * GET Branch Lists with details
     * This endpoint allows to fetch the list of all branches of NCM.
     */
    public function getBranches()
    {
        try {
            $url = $this->baseUrl . '/api/v2/branches';
            $response = Http::withHeaders($this->getHeaders())->get($url);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getBranches error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * GET Delivery Charges between branches
     */
    public function getDeliveryCharges($creation, $destination, $type = 'Door2Door')
    {
        try {
            $url = $this->baseUrl . '/api/v1/shipping-rate';
            $response = Http::withHeaders($this->getHeaders())->get($url, [
                'creation' => $creation,
                'destination' => $destination,
                'type' => $type,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getDeliveryCharges error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * GET Order Details
     */
    public function getOrderDetails($orderId)
    {
        try {
            $url = $this->baseUrl . '/api/v1/order';
            $response = Http::withHeaders($this->getHeaders())->get($url, [
                'id' => $orderId,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getOrderDetails error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * GET Order Comments
     */
    public function getOrderComments($orderId)
    {
        try {
            $url = $this->baseUrl . '/api/v1/order/comment';
            $response = Http::withHeaders($this->getHeaders())->get($url, [
                'id' => $orderId,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getOrderComments error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * GET LAST 25 Order Comments
     */
    public function getBulkComments()
    {
        try {
            $url = $this->baseUrl . '/api/v1/order/getbulkcomments';
            $response = Http::withHeaders($this->getHeaders())->get($url);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getBulkComments error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * GET Order Status history
     */
    public function getOrderStatusHistory($orderId)
    {
        try {
            $url = $this->baseUrl . '/api/v1/order/status';
            $response = Http::withHeaders($this->getHeaders())->get($url, [
                'id' => $orderId,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getOrderStatusHistory error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Create an order
     */
    public function createOrder(array $data)
    {
        try {
            $url = $this->baseUrl . '/api/v1/order/create';
            
            // Log the request for debugging
            Log::info('NCM Create Order Request', [
                'url' => $url,
                'payload' => $data
            ]);

            $response = Http::withHeaders($this->getHeaders())
                ->asJson()
                ->post($url, $data);

            $result = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'raw' => $response->body()
            ];

            if (!$response->successful()) {
                Log::error('NCM Create Order Failed', $result);
            } else {
                Log::info('NCM Create Order Response', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('NCM Service - createOrder error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * POST Create an order comment
     */
    public function createComment($orderId, $comment)
    {
        try {
            $url = $this->baseUrl . '/api/v1/comment';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url, [
                'orderid' => $orderId,
                'comments' => $comment,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - createComment error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Retrieve Order statuses
     */
    public function getBulkStatuses(array $orderIds)
    {
        try {
            $url = $this->baseUrl . '/api/v1/orders/statuses';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url, [
                'orders' => $orderIds,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getBulkStatuses error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Create Generic Vendor Ticket
     */
    public function createTicket($type, $message)
    {
        try {
            $url = $this->baseUrl . '/api/v2/vendor/ticket/create';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url, [
                'ticket_type' => $type,
                'message' => $message,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - createTicket error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Create COD Transfer Ticket
     */
    public function createCODTicket($bankName, $accountName, $accountNumber)
    {
        try {
            $url = $this->baseUrl . '/api/v2/vendor/ticket/cod/create';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url, [
                'bankName' => $bankName,
                'bankAccountName' => $accountName,
                'bankAccountNumber' => $accountNumber,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - createCODTicket error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Close Vendor Ticket
     */
    public function closeTicket($ticketId)
    {
        try {
            $url = $this->baseUrl . '/api/v2/vendor/ticket/close/' . $ticketId;
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - closeTicket error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * GET Staff List
     */
    public function getStaffList($q = null, $page = 1, $pageSize = 20)
    {
        try {
            $url = $this->baseUrl . '/api/v2/vendor/staffs';
            $response = Http::withHeaders($this->getHeaders())->get($url, [
                'q' => $q,
                'page' => $page,
                'page_size' => $pageSize,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - getStaffList error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Return Order
     */
    public function returnOrder($orderId, $comment = null)
    {
        try {
            $url = $this->baseUrl . '/api/v2/vendor/order/return';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url, [
                'pk' => $orderId,
                'comment' => $comment,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - returnOrder error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Create Exchange Order
     */
    public function exchangeOrder($orderId)
    {
        try {
            $url = $this->baseUrl . '/api/v2/vendor/order/exchange-create';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url, [
                'pk' => $orderId,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - exchangeOrder error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Redirect Order
     */
    public function redirectOrder(array $data)
    {
        try {
            $url = $this->baseUrl . '/api/v2/vendor/order/redirect';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($url, $data);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - redirectOrder error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Create/Update Webhook URL
     */
    public function setWebhook($url)
    {
        try {
            $endpoint = $this->baseUrl . '/api/v2/vendor/webhook';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($endpoint, [
                'webhook_url' => $url,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - setWebhook error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST Test Webhook URL
     */
    public function testWebhook($url)
    {
        try {
            $endpoint = $this->baseUrl . '/api/v2/vendor/webhook/test';
            $response = Http::withHeaders($this->getHeaders())->asJson()->post($endpoint, [
                'webhook_url' => $url,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('NCM Service - testWebhook error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create Shipment for an OrderItem
     * Helper method to map our OrderItem to NCM createOrder format
     */
    public function createShipment($orderItem)
    {
        try {
            Log::info('NCM createShipment called for order item', ['order_item_id' => $orderItem->id ?? 'N/A']);
            
            $order = $orderItem->order;
            if (!$order) {
                Log::error('NCM createShipment: Order not found for order item', ['order_item_id' => $orderItem->id]);
                return ['success' => false, 'error' => 'Order not found'];
            }
            
            $customer = $order->user;
            $shipping = $order->shippingAddress;
            $vendor = $orderItem->vendor;

            Log::info('NCM createShipment retrieved related models', [
                'has_order' => isset($order),
                'has_customer' => isset($customer),
                'has_shipping' => isset($shipping),
                'has_vendor' => isset($vendor)
            ]);

            // Ensure we have the necessary data, or fallback
            $name = $shipping->name ?? $customer->name ?? 'Customer';
            $phone = $shipping->phone ?? $customer->phone ?? '';
            $address = $shipping->address ?? $customer->address ?? 'Nepal';

            // Clean phone number (remove spaces, etc.)
            $phone = preg_replace('/[^0-9+]/', '', $phone);

            // NCM requires specific branch names.
            // We use the city name, but we can add a mapping here if needed.
            $fbranch = 'POKHARA';
            if ($vendor && $vendor->city) {
                $fbranch = strtoupper($vendor->city->name);
            }
            
            $branch = 'POKHARA';
            if ($shipping && $shipping->city) {
                $branch = strtoupper($shipping->city->name);
            }
            
            Log::info('NCM createShipment branch selection', [
                'fbranch' => $fbranch,
                'branch' => $branch,
                'vendor_has_city' => isset($vendor->city),
                'shipping_has_city' => isset($shipping->city)
            ]);

            // COD Charge: Only if payment mode is COD
            $codCharge = (strtoupper($orderItem->payment_mode) === 'COD') 
                         ? round((float)$orderItem->total_actual_price, 2) 
                         : 0;

            $data = [
                'name'          => (string)$name,
                'phone'         => (string)$phone,
                'phone2'        => '',
                'cod_charge'    => $codCharge,
                'address'       => (string)$address,
                'fbranch'       => (string)$fbranch, 
                'branch'        => (string)$branch,
                'package'       => (string)($orderItem->product->name ?? 'Package'),
                'vref_id'       => (string)('ITEM-' . $orderItem->id),
                'instruction'   => 'Handle with care',
                'delivery_type' => 'Door2Door',
                'weight'        => '1',
            ];

            Log::info('NCM createShipment prepared payload', ['payload' => $data]);
            $result = $this->createOrder($data);
            Log::info('NCM createShipment createOrder result', ['result' => $result]);

            if ($result && isset($result['success']) && $result['success']) {
                $resData = $result['data'];
                Log::info('NCM createShipment response data', ['res_data' => $resData]);
                
                // Try all possible tracking ID fields
                $trackingId = null;
                if (isset($resData['orderid'])) {
                    $trackingId = $resData['orderid'];
                } elseif (isset($resData['id'])) {
                    $trackingId = $resData['id'];
                } elseif (isset($resData['order_id'])) {
                    $trackingId = $resData['order_id'];
                }
                
                Log::info('NCM createShipment extracted tracking ID', ['tracking_id' => $trackingId]);
                
                if ($trackingId) {
                    Log::info('NCM createShipment: About to update order item with tracking info', [
                        'order_item_id' => $orderItem->id,
                        'tracking_id' => $trackingId
                    ]);
                    
                    $updateResult = $orderItem->update([
                        'logistics_provider' => 'NCM',
                        'tracking_id'        => (string)$trackingId,
                        'logistics_status'   => 'Order Created',
                    ]);
                    
                    Log::info('NCM createShipment: Order item update result', ['update_result' => $updateResult]);
                    
                    // Refresh the order item from the database to confirm the changes
                    $orderItem->refresh();
                    
                    Log::info('NCM createShipment: Order item after refresh', [
                        'order_item_id' => $orderItem->id,
                        'logistics_provider' => $orderItem->logistics_provider,
                        'tracking_id' => $orderItem->tracking_id,
                        'logistics_status' => $orderItem->logistics_status,
                        'full_order_item' => $orderItem->toArray()
                    ]);
                } else {
                    Log::warning('NCM createShipment: No tracking ID found in response', ['res_data' => $resData]);
                }
            } else {
                Log::error('NCM Shipment Creation Failed for Item ' . $orderItem->id, [
                    'result' => $result,
                    'payload' => $data
                ]);
            }
            
            return $result; 
        } catch (\Exception $e) {
            Log::error('NCM Service - createShipment exception: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }

    /**
     * Map NCM Status to local status
     */
    public function mapStatus($ncmStatus)
    {
        $map = [
            'Pickup Order Created' => 'Order Created',
            'Sent for Pickup' => 'Processing',
            'Pickup Complete' => 'At Warehouse',
            'pickup_completed' => 'At Warehouse',
            'Arrived' => 'At Warehouse',
            'order_arrived' => 'At Warehouse',
            'Sent for Delivery' => 'In Transit',
            'sent_for_delivery' => 'In Transit',
            'Dispatched' => 'In Transit',
            'order_dispatched' => 'In Transit',
            'Delivered' => 'Delivered',
            'delivery_completed' => 'Delivered',
            'Cancelled' => 'Cancelled',
            'Return Received' => 'Returned',
        ];

        return $map[$ncmStatus] ?? $ncmStatus;
    }

    /**
     * Map NCM Status to local numeric status codes
     * 0: Pending, 1: Confirmed, 2: Shipped, 3: Delivered, 4: Cancelled, 5: Returned
     */
    public function mapNumericStatus($ncmStatus)
    {
        $map = [
            'Pickup Order Created' => 1, // Confirmed
            'Sent for Pickup' => 1,      // Confirmed/Processing
            'Pickup Complete' => 2,      // Shipped
            'pickup_completed' => 2,
            'Arrived' => 2,              // Shipped
            'order_arrived' => 2,
            'Sent for Delivery' => 2,    // Shipped/In Transit
            'sent_for_delivery' => 2,
            'Dispatched' => 2,
            'order_dispatched' => 2,
            'Delivered' => 3,            // Delivered
            'delivery_completed' => 3,
            'Cancelled' => 4,            // Cancelled
            'Return Received' => 5,      // Returned
        ];

        return $map[$ncmStatus] ?? null;
    }

    /**
     * GET Track Shipment status
     */
    public function trackShipment($trackingId)
    {
        try {
            // NCM tracking endpoint
            $url = $this->baseUrl . '/api/v1/order';
            $response = Http::withHeaders($this->getHeaders())->get($url, [
                'id' => $trackingId,
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('NCM Service - trackShipment error: ' . $e->getMessage());
            return null;
        }
    }
}
