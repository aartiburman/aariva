<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\VendorPayout;
use App\Models\GeneralSetting;
use App\Helpers\ReferralHelper;
use App\Helpers\NotificationHelper;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\Campaign;
use App\Helpers\EmailHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PayoutHelper;



class OrderController extends Controller
{
    private function getStatusCounts($vendorIds)
    {
        return OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.vendor_id', $vendorIds)
            ->selectRaw("
                COUNT(order_items.id) as total,
                SUM(order_items.status = 0) as pending,
                SUM(order_items.status = 1) as confirmed,
                SUM(order_items.status = 2) as shipped,
                SUM(order_items.status = 3) as delivered,
                SUM(order_items.status = 4) as cancelled,
                SUM(order_items.status = 5) as returned,
                SUM(order_items.status = 6) as dispute
            ")
            ->first();
    }

    private function getVendorIds($user)
    {
        if ((string)$user->role === '1') {
            $vendorIds = User::where('role', '2')->pluck('id')->values()->toArray();
            $vendorIds[] = $user->id;
            return $vendorIds;
        }
        return [$user->id];

    }

    private function applyFilters(Request $request, $query, $user)
    {
        /* ===============================
           STATUS FILTER
        =============================== */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

          /* ===============================
           PAYMENT STATUS FILTER
        =============================== */
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }
        /* ===============================
           PAYMENT STATUS FILTER
        =============================== */
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        /* ===============================
           VENDOR FILTER (For Admin)
        =============================== */
        if ((string)$user->role === '1' && $request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        /* ===============================
           DATE RANGE FILTER
        =============================== */
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('created_at', $dates[0]);
            }
        }

        /* ===============================
           SEARCH
        =============================== */
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($oq) use ($search) {
                    $oq->where('order_reference_id', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'LIKE', "%{$search}%");
                        });
                })
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function new_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);
        /* ===============================
       BASE QUERY (ORDER ITEMS)
    =============================== */

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order'); // ensure valid order

        $query = $this->applyFilters($request, $query, $user);

        /* ===============================
       SORT & PAGINATION
    =============================== */
        $orders = $query
            ->latest('updated_at')
            ->paginate(15)->withQueryString();

        $vendors = [];
        // if ((string)$user->role === '1') {
        //     $vendors = User::where('role', '2')->get();
        // }



        if ($request->ajax()) {
             return view('backend.orders.partials.orders-table', compact('orders'))->render();
        }

        return view(
            'backend/orders/orders-list',
            compact('orders', 'statusCounts', 'vendors')
        );
    }

    public function pending_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 0); // Pending status

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend/orders/pending-orders', compact('orders', 'statusCounts', 'vendors'));
    }

   

  

 
    public function cancelled_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 4); // Cancelled status

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend/orders/cancelled_orders', compact('orders', 'statusCounts', 'vendors'));
    }

 

    public function confirmed_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 1); // Confirmed status

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend/orders/confirmed-orders', compact('orders', 'statusCounts', 'vendors'));
    }

    public function shipped_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 2); // Shipped status

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend/orders/shipped-orders', compact('orders', 'statusCounts', 'vendors'));
    }

    public function delivered_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 3); // Delivered status (wait, was it 4? let's check)

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend/orders/delivered-orders', compact('orders', 'statusCounts', 'vendors'));
    }

    public function rejected_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 5); // Rejected status

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend.orders.rejected-orders', compact('orders', 'statusCounts', 'vendors'));
    }

    public function returned_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 5); // Returned status

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend.orders.returned-orders', compact('orders', 'statusCounts', 'vendors'));
    }

    public function dispute_orders(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $this->getVendorIds($user);
        $statusCounts = $this->getStatusCounts($vendorIds);

        $query = OrderItem::with([
            'product',
            'order.user',
            'order.shippingAddress',
            'vendor'
        ])
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order')
            ->where('status', 6); // In Dispute status

        $query = $this->applyFilters($request, $query, $user);

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        $vendors = [];
        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->get();
        }

        return view('backend.orders.dispute-orders', compact('orders', 'statusCounts', 'vendors'));
    }

    public function orders_details($reference_id)
    {
        $user = Auth::user();
        $order = Order::with(['user', 'items.product', 'items.variant', 'shippingAddress', 'items.vendor.country'])
            ->where('order_reference_id', $reference_id)
            ->firstOrFail();

        // If vendor, only show their items?
        // Actually, the view shows $order->items.
        // If we want to filter items for vendors, we should handle it in the view or here.
        if ($user->role == '2') {
            $order->setRelation('items', $order->items->where('vendor_id', $user->id));
        }

        return view('backend/orders/orders-details', compact('order'));
    }
    

   public function bulk_update_order_status(Request $request)
{
    $user = Auth::user();
    $orderIds = $request->order_ids;
    $newStatus = (int) $request->status;

    if (empty($orderIds)) {
        return response()->json(['status' => false, 'message' => 'No orders selected']);
    }

    $query = OrderItem::whereIn('id', $orderIds);

    if ($user->role != 1) {
        $query->where('vendor_id', $user->id);
    }

    $items = $query->get();
    $processedOrderIds = [];

    foreach ($items as $item) {

        // Skip delivered items
        if ((int)$item->status === 3) {
            continue;
        }

        // Dispute restriction
        if ((int)$item->status === 6 && $newStatus !== 5) {
            continue;
        }

        /* =====================================================
           ⭐ FIX: ACTUAL STATUS UPDATE
        ===================================================== */
        $item->status = $newStatus;

        // If delivered
        if ($newStatus === 3) {
            $item->payment_status = 1;

            if ($item->order) {
                $item->order->payment_status = '1';
                $item->order->save();
            }
        }

        $item->save();

        /* =====================================================
           EMAIL NOTIFICATION
        ===================================================== */
        $order = $item->order;
        $customerEmail = $order?->user?->email ?? null;

        if ($order && $order->user && !empty(trim((string)$customerEmail))) {

            $statusText = 'Pending';

            switch ($newStatus) {
                case 1: $statusText = 'Confirmed'; break;
                case 2: $statusText = 'Shipped'; break;
                case 3: $statusText = 'Delivered'; break;
                case 4: $statusText = 'Cancelled'; break;
                case 5: $statusText = 'Returned'; break;
            }

            $productImage = ($item->variant && $item->variant->image) ? $item->variant->image : ($item->product ? $item->product->thumbnail : null);

            $emailData = [
                'customer_name' => $order?->user?->name ?? 'Customer',
                'product_name'  => $item->product?->name ?? 'Product',
                'product_image' => ImageHelper::getProductImage($productImage),
                'order_id'      => $order?->order_id ?? 'N/A',
                'quantity'      => $item->quantity,
                'price'         => PriceHelper::formatPrice($item->price),
                'status_text'   => $statusText,
                'order_url'     => $order && $order->user ? (config('app.url').'/api/get-order-detail?user_id='.$order->user->id.'&order_id='.$order->id) : '#',
            ];

            EmailHelper::send(
                $customerEmail,
                'Order Status Updated - ' . $statusText,
                '',
                'emails.order-status',
                $emailData
            );
        }

        /* =====================================================
           STOCK REDUCTION + PAYOUT ON DELIVERY
        ===================================================== */
        if ($newStatus === 3) {
            $variant = ProductVariant::find($item->variant_id);
            if ($variant && $variant->stock >= $item->quantity) {
                $variant->decrement('stock', $item->quantity);
            }
            
            // Generate unpaid payout record on delivery
            PayoutHelper::createPayoutForItem($item);
        }

        /* =====================================================
           REFERRAL REWARD (ONCE PER ORDER)
        ===================================================== */
        $order = $item->order;

        if ($order && !in_array($order->id, $processedOrderIds)) {

            if ($order->items()->where('status', '!=', '3')->doesntExist()) {
                ReferralHelper::processReferralReward($order);
                $processedOrderIds[] = $order->id;
            }
        }

        /* =====================================================
           REFUND / RETURN LOGIC (FOR CANCELLED/RETURNED)
        ===================================================== */
        if (in_array((int)$newStatus, [4, 5])) {
            $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
            $pgFeePercent   = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);
            $itemAmount = $item->total_actual_price ?? ($item->price * $item->quantity);
            $commission = ($itemAmount * $commissionRate) / 100;
            $pgFee      = ($itemAmount * $pgFeePercent) / 100;
            $vendorNetRefund = max(0, $itemAmount - $commission - $pgFee);
            $vendor = $item->vendor;

            if ($vendor && $vendorNetRefund > 0) {
                // Find any existing payout for this item
                $payout = VendorPayout::where('order_item_id', $item->id)
                    ->whereIn('status', ['paid', 'unpaid'])
                    ->first();
                
                $settlementRef = 'VENDOR-SETTLEMENT-' . ($order->id ?? 0) . '-' . $item->vendor_id;
                $hasOldSettlement = WalletTransaction::where('reference_id', $settlementRef)->exists();

                // Only debit wallet if it was actually credited (status was 'paid' or old settlement exists)
                if (($payout && $payout->status === 'paid') || $hasOldSettlement) {
                    $vendor->wallet_balance = max(0, ($vendor->wallet_balance ?? 0) - $vendorNetRefund);
                    $vendor->save();
                    WalletTransaction::create([
                        'user_id'      => $vendor->id,
                        'amount'       => $vendorNetRefund,
                        'type'         => 'debit',
                        'description'  => 'Refund adjustment for Order #' . ($order?->order_reference_id ?? ($order?->id ?? 'N/A')),
                        'reference_id' => 'REFUND-' . $item->id,
                        'status'       => 'completed',
                    ]);
                }

                // Always mark any existing payout as failed if the item is returned/cancelled
                if ($payout) {
                    $payout->status = 'failed';
                    $payout->note = ($payout->note ? $payout->note . ' | ' : '') . 'Payout cancelled due to return/cancellation (bulk)';
                    $payout->save();
                }
            }
        }
    }

    return response()->json([
        'status'  => true,
        'message' => 'Statuses updated successfully'
    ]);
}

 public function update_order_status(Request $request)
{
    $user = Auth::user();
    $orderItem = OrderItem::find($request->order_id);

    if (!$orderItem) {
        return response()->json(['status' => false, 'message' => 'Order item not found']);
    }

    // Authorization check
    if ($user->role != 1 && $orderItem->vendor_id != $user->id) {
        return response()->json(['status' => false, 'message' => 'Unauthorized']);
    }

    // Prevent updates once delivered
    if ((int)$orderItem->status === 3) {
        return response()->json(['status' => false, 'message' => 'Delivered items cannot be updated']);
    }

    // Dispute rule
    if ((int)$orderItem->status === 6 && (int)$request->status !== 5) {
        return response()->json(['status' => false, 'message' => 'For disputed orders, only "Returned" status is allowed']);
    }

    /* =====================================================
       ⭐ IMPORTANT FIX: ACTUALLY UPDATE STATUS HERE
    ===================================================== */
    $oldStatus = $orderItem->status;
    $orderItem->status = (int) $request->status;

    // If delivered → payment paid
    if ((int)$request->status === 3) {
        $orderItem->payment_status = 1;

        if ($orderItem->order) {
            $orderItem->order->payment_status = '1';
            $orderItem->order->save();
        }
    }

    $orderItem->save();

    /* =====================================================
       STOCK REDUCTION + PAYOUT (ONLY WHEN NEW STATUS = 3)
    ===================================================== */
 
    if ((int)$request->status === 3) {
      
        
        $variant = ProductVariant::find($orderItem->variant_id);

        if ($variant && $variant->stock >= $orderItem->quantity) {
            $variant->decrement('stock', $orderItem->quantity);
        }

        // Generate unpaid payout record on delivery
        PayoutHelper::createPayoutForItem($orderItem);
    }
    /* =====================================================
       EMAIL NOTIFICATION
    ===================================================== */
    $order = $orderItem->order;
    $customerEmail = $order?->user?->email ?? null;

    if ($order && $order->user && !empty(trim((string)$customerEmail))) {

        $statusText = 'Pending';

        switch ((int)$orderItem->status) {
            case 1: $statusText = 'Confirmed'; break;
            case 2: $statusText = 'Shipped'; break;
            case 3: $statusText = 'Delivered'; break;
            case 4: $statusText = 'Cancelled'; break;
            case 5: $statusText = 'Returned'; break;
        }

        $productImage = ($orderItem->variant && $orderItem->variant->image) ? $orderItem->variant->image : ($orderItem->product ? $orderItem->product->thumbnail : null);

        $emailData = [
            'customer_name' => $order?->user?->name ?? 'Customer',
            'product_name'  => $orderItem->product?->name ?? 'Product',
            'product_image' => ImageHelper::getProductImage($productImage),
            'order_id'      => $order?->order_id ?? 'N/A',
            'quantity'      => $orderItem->quantity,
            'price'         => PriceHelper::formatPrice($orderItem->price),
            'status_text'   => $statusText,
            'order_url'     => $order && $order->user ? (config('app.url').'/api/get-order-detail?user_id='.$order->user->id.'&order_id='.$order->id) : '#',
        ];

        EmailHelper::send(
            $customerEmail,
            'Order Status Updated - ' . $statusText,
            '',
            'emails.order-status',
            $emailData
        );
    }

    /* =====================================================
       REFUND / RETURN LOGIC
    ===================================================== */
    if (in_array((int)$orderItem->status, [4,5])) {

        $order = $orderItem->order;

        if ($order) {

            $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
            $pgFeePercent   = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);

            $itemAmount = $orderItem->total_actual_price ?? ($orderItem->price * $orderItem->quantity);

            $commission = ($itemAmount * $commissionRate) / 100;
            $pgFee      = ($itemAmount * $pgFeePercent) / 100;

            $vendorNetRefund = max(0, $itemAmount - $commission - $pgFee);

            $vendor = $orderItem->vendor;

            if ($vendor && $vendorNetRefund > 0) {

                // Find any existing payout for this item
                $payout = VendorPayout::where('order_item_id', $orderItem->id)
                    ->whereIn('status', ['paid', 'unpaid', 'pending', 'approved', 'processing'])
                    ->first();
                    
                $settlementRef = 'VENDOR-SETTLEMENT-' . $order->id . '-' . $orderItem->vendor_id;
                $hasOldSettlement = WalletTransaction::where('reference_id', $settlementRef)->exists();

                // Only debit wallet if it was actually credited (status was 'paid' or old settlement exists)
                if (($payout && $payout->status === 'paid') || $hasOldSettlement) {

                    $vendor->wallet_balance = max(0, ($vendor->wallet_balance ?? 0) - $vendorNetRefund);
                    $vendor->save();

                    WalletTransaction::create([
                        'user_id'      => $vendor->id,
                        'amount'       => $vendorNetRefund,
                        'type'         => 'debit',
                        'description'  => 'Refund adjustment for Order #' . ($order->order_reference_id ?? $order->id),
                        'reference_id' => 'REFUND-' . $orderItem->id,
                        'status'       => 'completed',
                    ]);
                }

                // Always mark any existing payout as failed if the item is returned/cancelled
                if ($payout) {
                    $payout->status = 'failed';
                    $payout->note = ($payout->note ? $payout->note . ' | ' : '') . 'Payout cancelled due to return/cancellation';
                    $payout->save();
                }
            }
        }
    }

    /* =====================================================
       FINAL SUCCESS RESPONSE
    ===================================================== */
    return response()->json([
        'status'  => true,
        'message' => 'Order status updated successfully'
    ]);
}

    public function update_payment_status(Request $request)
    {
        $user = Auth::user();
        $orderItem = OrderItem::find($request->order_id);

        if (!$orderItem) {
            return response()->json(['status' => false, 'message' => 'Order item not found']);
        }

        // Authorization check: Admin can update any item, Vendor only their own
        if ($user->role != 1 && $orderItem->vendor_id != $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        }

        $orderItem->payment_status = $request->payment_status;
        $orderItem->save();

        // When payment marked as paid (COD flow)
        if ($request->payment_status == '1') {
            $order = $orderItem->order;

            // Update Order-level payment_status so referral reward logic can run
            $order->payment_status = '1';
            $order->save();

            // Trigger referral reward when order is fully delivered + now paid
            if ($order->items()->where('status', '!=', '3')->doesntExist() && $order->items()->exists()) {
                ReferralHelper::processReferralReward($order);
            }

            // Deduct reward_used if not already done (COD flow)
            $rewardUsed = (float) ($order->reward_used ?? 0);
            if ($rewardUsed > 0) {
                $exists = WalletTransaction::where('reference_id', 'ORDER-REWARD-' . $order->id)->exists();
                if (!$exists) {
                    $orderUser = $order->user;
                    if ($orderUser && ($orderUser->reward_balance ?? 0) >= $rewardUsed) {
                        $orderUser->reward_balance -= $rewardUsed;
                        $orderUser->save();
                        WalletTransaction::create([
                            'user_id' => $orderUser->id,
                            'amount' => $rewardUsed,
                            'type' => 'debit',
                            'description' => 'reward_purchase',
                            'reference_id' => 'ORDER-REWARD-' . $order->id,
                            'status' => 'completed',
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => true, 'message' => 'Payment status updated successfully']);
    }

    public function orders_invoice($reference_id)
    {
        $user = Auth::user();
        $order = Order::with(['user', 'items.product', 'items.variant', 'shippingAddress', 'items.vendor.country'])
            ->where('order_reference_id', $reference_id)
            ->firstOrFail();

        if ($user->role == '2') {
            $order->setRelation('items', $order->items->where('vendor_id', $user->id));
        }

        return view('backend.orders.invoice', compact('order'));
    }

    public function restore_order($id)
    {
        $user = Auth::user();
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return redirect()->back()->with('error', 'Order item not found');
        }

        // Authorization check: Admin can update any item, Vendor only their own
        if ($user->role != 1 && $orderItem->vendor_id != $user->id) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        // Restore to Pending status (0)
        $orderItem->status = 0;
        $orderItem->save();

        return redirect()->back()->with('success', 'Order restored to pending status');
    }
}
