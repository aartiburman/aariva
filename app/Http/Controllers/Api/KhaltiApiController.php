<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Helpers\NotificationHelper;
use App\Helpers\EmailHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\CampaignBudgetHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\Logistics\NCMService;
use App\Services\Payment\KhaltiService;

class KhaltiApiController extends Controller
{
    public function verifyPayment(Request $request)
    {
        Log::info('=== Khalti verifyPayment CALLED ===');
        Log::info('Khalti verifyPayment called with request data:', $request->all());
        
        $pidx = $request->pidx ?? $request->query('pidx');
        $orderReferenceId = $request->purchase_order_id ?? $request->query('purchase_order_id') ?? $request->order_id ?? $request->query('order_id') ?? $request->product_identity ?? $request->query('product_identity');

        Log::info('Khalti verifyPayment extracted:', [
            'pidx' => $pidx,
            'orderReferenceId' => $orderReferenceId
        ]);

        if (!$pidx) {
            Log::error('Khalti verifyPayment: Missing pidx');
            return response()->json(['status' => false, 'message' => 'Missing pidx'], 400);
        }
        
        if (!$orderReferenceId) {
            Log::error('Khalti verifyPayment: Missing order reference ID', ['request' => $request->all()]);
            return response()->json(['status' => false, 'message' => 'Missing order reference ID'], 400);
        }

        $verification = KhaltiService::verifyPayment($pidx);
        Log::info('Khalti verifyPayment verification result:', $verification);

        if ($verification['status'] && strtolower($verification['payment_status'] ?? '') === 'completed') {
            $order = Order::where('order_reference_id', $orderReferenceId)->first();
            Log::info('Khalti verifyPayment existing order check:', [
                'found' => isset($order),
                'orderReferenceId' => $orderReferenceId
            ]);
            
            if (!$order) {
                $cacheKey = 'pending_order_' . $orderReferenceId;
                Log::info('Khalti verifyPayment checking cache for key:', ['key' => $cacheKey]);
                $orderData = Cache::get($cacheKey);
                Log::info('Khalti verifyPayment cache data retrieved:', ['has_data' => isset($orderData), 'data' => $orderData]);
                
                if (!$orderData) {
                    Log::error('Khalti verifyPayment: No order and no cache data');
                    return response()->json(['status' => false, 'message' => 'Order not found and no pending order in cache'], 404);
                }

                Log::info('Khalti verifyPayment starting DB transaction');
                try {
                    $order = DB::transaction(function () use ($orderData, $pidx) {
                        Log::info('Khalti verifyPayment inside DB transaction');
                        $userData = User::where('users.id', $orderData['user_id'])
                            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
                            ->select('users.*', 'countries.currency_code')
                            ->first();
                        
                        Log::info('Khalti verifyPayment user data retrieved', ['user_id' => $orderData['user_id']]);

                        $order = Order::create([
                            'order_reference_id' => $orderData['order_reference_id'],
                            'transaction_id' => $pidx,
                            'user_id' => $orderData['user_id'],
                            'shipping_id' => $orderData['shipping_id'],
                            'status' => 1,
                            'payment_status' => 1,
                            'payment_mode' => $orderData['payment_mode'],
                            'currency_code' => $orderData['currency_code'],
                            'sub_total' => $orderData['sub_total'],
                            'delivery_charges' => $orderData['delivery_charges'],
                            'taxes' => $orderData['taxes'],
                            'total_cost' => $orderData['total_cost'],
                            'product_discounts' => $orderData['product_discounts'],
                            'coupon_discounts' => $orderData['coupon_discounts'],
                            'offer_discounts' => $orderData['offer_discounts'],
                            'total_discount' => $orderData['total_discount'],
                            'coupon_id' => $orderData['coupon_id'] ?? null,
                            'coupon_code' => $orderData['coupon_code'],
                            'reward_used' => $orderData['reward_used'],
                            'order_date' => now(),
                            'delivery_date' => $orderData['delivery_date'],
                        ]);
                        Log::info('Khalti verifyPayment order created', ['order_id' => $order->id]);

                    $delivery_charges = $orderData['delivery_charges'];
                    $item_count = count($orderData['summary_items']);
                    $per_item_delivery_charge = $item_count > 0 ? $delivery_charges / $item_count : 0;

                    $total_before_global_discounts = collect($orderData['summary_items'])->sum(function ($item) {
                        return $item['price_after_discounts'] * $item['qty'];
                    });
                    $coupon_discount_total = (float) ($orderData['coupon_discounts'] ?? 0.0);
                    $reward_discount_total = (float) ($orderData['reward_used'] ?? 0.0);

                    $ncmService = new NCMService();

                    foreach ($orderData['summary_items'] as $item) {
                        $line_subtotal = $item['price_after_discounts'] * $item['qty'];
                        $pro_rated_coupon = 0.0;
                        $pro_rated_reward = 0.0;
                        if ($total_before_global_discounts > 0) {
                            $pro_rated_coupon = round(($line_subtotal / $total_before_global_discounts) * $coupon_discount_total, 2);
                            $pro_rated_reward = round(($line_subtotal / $total_before_global_discounts) * $reward_discount_total, 2);
                        }
                        $final_line_total = max(0, $line_subtotal + $item['tax_amount'] + $per_item_delivery_charge - $pro_rated_coupon - $pro_rated_reward);

                        $orderItem = OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'variant_id' => $item['variant_id'],
                            'vendor_id' => $item['vendor_id'],
                            'campaign_id' => $item['campaign_id'] ?? null,
                            'quantity' => $item['qty'],
                            'price' => $item['unit_price'],
                            'discount' => $item['product_unit_discount'],
                            'offer_discount' => $item['offer_unit_discount'],
                            'campaign_discount' => $item['campaign_unit_discount'],
                            'actual_price' => $item['price_after_discounts'],
                            'total_actual_price' => round($final_line_total, 2),
                            'vendor_tax' => $item['vendor_tax'],
                            'tax_amount' => $item['tax_amount'],
                            'status' => 1,
                            'payment_status' => 1,
                            'payment_mode' => $orderData['payment_mode'],
                            'currency' => $orderData['currency_code'],
                            'delivery_charges' => $per_item_delivery_charge,
                        ]);
                        
                        $orderItem->load(['order', 'order.user', 'order.shippingAddress', 'order.shippingAddress.city', 'vendor', 'vendor.city', 'product']);

                        if (!empty($item['campaign_id']) && (float)$item['campaign_unit_discount'] > 0) {
                            $discountUsage = (float)$item['campaign_unit_discount'] * (int)$item['qty'];
                            CampaignBudgetHelper::applyDiscountUsage((int)$item['campaign_id'], (int)$item['vendor_id'], $discountUsage);
                        }

                        if (isset($item['variant_id'])) {
                            $variant = ProductVariant::find($item['variant_id']);
                            if ($variant) {
                                $variant->decrement('stock', $item['qty']);
                            }
                        }

                        $vendor = User::find($item['vendor_id']);
                        if ($vendor) {
                            if (!empty(trim((string)$vendor->email))) {
                                EmailHelper::send(
                                    $vendor->email,
                                    'New Order Received - #' . $order->order_reference_id,
                                    '',
                                    'emails.vendor-order-notification',
                                    [
                                        'vendor_name' => $vendor->name,
                                        'customer_name' => $userData->name ?? 'Customer',
                                        'order_id' => $order->id,
                                        'product_name' => Product::where('id', $item['product_id'])->value('name') ?? 'Product',
                                        'product_image' => ImageHelper::getProductImage(
                                            (ProductVariant::where('id', $item['variant_id'])->value('image') ?? null)
                                                ?: (Product::where('id', $item['product_id'])->value('thumbnail') ?? null)
                                        ),
                                        'quantity' => $orderItem->quantity,
                                        'earnings' => PriceHelper::formatPrice($orderItem->total_actual_price),
                                        'dashboard_url' => config('app.url') . '/vendor/dashboard'
                                    ]
                                );
                            }
                            NotificationHelper::notifyVendor($vendor->id, [
                                'title' => 'New Order Received',
                                'message' => 'You have received a new order #' . $order->order_reference_id . ' for ' . (Product::where('id', $item['product_id'])->value('name') ?? 'product'),
                                'type' => 'orders',
                                'url' => 'orders-details/' . $order->order_reference_id,
                                'icon' => 'solar:cart-large-minimalistic-bold-duotone'
                            ]);
                        }

                        $ncmService->createShipment($orderItem);
                    }

                    NotificationHelper::notifyAdmins([
                        'title' => 'New Marketplace Order',
                        'message' => 'A new order #' . $order->order_reference_id . ' has been placed by ' . ($userData->name ?? 'Customer'),
                        'type' => 'orders',
                        'url' => 'orders-details/' . $order->order_reference_id,
                        'icon' => 'solar:cart-check-bold-duotone'
                    ]);

                    $admins = User::where('role', '1')->get();
                    foreach ($admins as $admin) {
                        if (!empty(trim((string)$admin->email))) {
                            EmailHelper::send(
                                $admin->email,
                                'New Marketplace Order - #' . $order->order_reference_id,
                                '',
                                'emails.admin-order-notification',
                                [
                                    'customer_name' => $userData->name ?? 'Customer',
                                    'order_id' => $order->order_reference_id,
                                    'total_cost' => PriceHelper::formatPrice($order->total_cost),
                                    'payment_mode' => $order->payment_mode,
                                    'admin_url' => config('app.url') . '/admin/dashboard'
                                ]
                            );
                        }
                    }

                    $customerItems = [];
                    foreach ($orderData['summary_items'] as $item) {
                        $p = Product::find($item['product_id']);
                        $v = ProductVariant::find($item['variant_id']);
                        $customerItems[] = [
                            'name' => $p->name ?? 'Product',
                            'qty' => $item['qty'],
                            'price' => PriceHelper::formatPrice($item['price_after_discounts']),
                            'image' => ImageHelper::getProductImage(($v->image ?? null) ?: ($p->thumbnail ?? null)),
                        ];
                    }
                    if ($order->user && !empty(trim((string)$order->user->email))) {
                        EmailHelper::send(
                            $order->user->email,
                            'Order Confirmed - #' . $order->order_reference_id,
                            '',
                            'emails.order-placed',
                            [
                                'customer_name' => $order->user->name ?? 'Customer',
                                'order_id' => $order->order_reference_id,
                                'items' => $customerItems,
                                'sub_total' => PriceHelper::formatPrice($order->sub_total),
                                'delivery_charges' => PriceHelper::formatPrice($order->delivery_charges),
                                'discount' => PriceHelper::formatPrice($order->total_discount),
                                'total_cost' => PriceHelper::formatPrice($order->total_cost),
                                'order_url' => env('App_URL') . '/api/get-order-detail?user_id=' . $order->user_id . '&order_id=' . $order->id
                            ]
                        );
                    }

                    if (isset($orderData['cart_items'])) {
                        Cart::where('user_id', $orderData['user_id'])->delete();
                    }

                    if (!empty($orderData['card_holder_name']) && !empty($orderData['card_number'])) {
                        $existingCard = \App\Models\UserCard::where('user_id', $orderData['user_id'])
                            ->where('card_number', $orderData['card_number'])
                            ->first();

                        if ($existingCard) {
                            $existingCard->update([
                                'card_holder_name' => $orderData['card_holder_name'],
                                'expiry_month' => $orderData['expiry_month'] ?? null,
                                'expiry_year' => $orderData['expiry_year'] ?? null,
                                'card_type' => $orderData['card_type'] ?? null,
                            ]);
                        } else {
                            $hasExisting = \App\Models\UserCard::where('user_id', $orderData['user_id'])->exists();
                            \App\Models\UserCard::create([
                                'user_id' => $orderData['user_id'],
                                'card_holder_name' => $orderData['card_holder_name'],
                                'card_number' => $orderData['card_number'],
                                'expiry_month' => $orderData['expiry_month'] ?? null,
                                'expiry_year' => $orderData['expiry_year'] ?? null,
                                'card_type' => $orderData['card_type'] ?? null,
                                'is_default' => !$hasExisting,
                            ]);
                        }
                    }

                    $rewardUsed = (float) ($orderData['reward_used'] ?? 0);
                    if ($rewardUsed > 0) {
                        $user = User::find($orderData['user_id']);
                        $refId = 'ORDER-REWARD-' . $order->id;
                        $exists = WalletTransaction::where('reference_id', $refId)->exists();
                        if ($user && ($user->reward_balance ?? 0) >= $rewardUsed && !$exists) {
                            $user->reward_balance -= $rewardUsed;
                            $user->save();
                            WalletTransaction::create([
                                'user_id' => $user->id,
                                'amount' => $rewardUsed,
                                'type' => 'debit',
                                'description' => 'reward_used_for_order',
                                'reference_id' => $refId,
                                'status' => 'completed',
                            ]);
                            NotificationHelper::notifyCustomer($user->id, [
                                'title' => 'Reward Used Successfully',
                                'message' => 'NPR ' . number_format($rewardUsed, 2) . ' reward was used for Order #' . ($order->order_reference_id ?? $order->id) . '.',
                                'type' => 'orders',
                                'url' => '#',
                                'icon' => 'solar:wallet-linear',
                                'priority' => 'medium',
                            ]);
                        }
                    }

                        Cache::forget('pending_order_' . $orderData['order_reference_id']);

                        Log::info('Khalti verifyPayment DB transaction completed, returning order');
                        return $order;
                    });
                    $finalOrder = Order::with('items')->find($order->id);
                    Log::info('Khalti verifyPayment order created from cache successfully', ['order_id' => $order->id]);
                } catch (\Exception $e) {
                    Log::error('Khalti verifyPayment failed to create order from cache: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to create order from cache: ' . $e->getMessage(),
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            if ($order->payment_status == '1') {
                return redirect()->away(config('app.url') . '/payment-success?status=success&message=Payment+successful&user_id=' . $order->user_id . '&lang=en&order_reference_id=' . $order->order_reference_id);
            }

            DB::beginTransaction();
            try {
                $order->update([
                    'payment_status' => '1',
                    'status' => 1,
                    'transaction_id' => $pidx,
                ]);

                $orderItems = OrderItem::where('order_id', $order->id)->get();
                $ncmService = new NCMService();

                foreach ($orderItems as $item) {
                    $item->load(['order', 'order.user', 'order.shippingAddress', 'order.shippingAddress.city', 'vendor', 'vendor.city', 'product']);
                    $item->update([
                        'payment_status' => '1',
                        'status' => 1
                    ]);
                    $ncmService->createShipment($item);
                }

                $rewardUsed = (float) ($order->reward_used ?? 0);
                if ($rewardUsed > 0) {
                    $user = User::find($order->user_id);
                    $refId = 'ORDER-REWARD-' . $order->id;
                    $exists = WalletTransaction::where('reference_id', $refId)->exists();
                    if ($user && ($user->reward_balance ?? 0) >= $rewardUsed && !$exists) {
                        $user->reward_balance -= $rewardUsed;
                        $user->save();
                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'amount' => $rewardUsed,
                            'type' => 'debit',
                            'description' => 'reward_used_for_order',
                            'reference_id' => $refId,
                            'status' => 'completed',
                        ]);
                        NotificationHelper::notifyCustomer($user->id, [
                            'title' => 'Reward Used Successfully',
                            'message' => 'NPR ' . number_format($rewardUsed, 2) . ' reward was used for Order #' . ($order->order_reference_id ?? $order->id) . '.',
                            'type' => 'orders',
                            'url' => '#',
                            'icon' => 'solar:wallet-linear',
                            'priority' => 'medium',
                        ]);
                    }
                }

                DB::commit();

                return redirect()->away(config('app.url') . '/admin/api/my-orders?status=success&message=Payment+successful&user_id=' . $order->user_id . '&lang=en&order_reference_id=' . $order->order_reference_id);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Khalti Verification DB Error: ' . $e->getMessage());
                return response()->json(['status' => false, 'message' => 'Database error during verification', 'error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => false, 'message' => 'Payment verification failed', 'verification' => $verification], 400);
    }
}
