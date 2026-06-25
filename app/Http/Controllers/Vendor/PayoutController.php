<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorPayout;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\GeneralSetting;



class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = Auth::id();
        $query = VendorPayout::where('vendor_id', $vendorId)
            ->select('vendor_payouts.*')
            ->addSelect(DB::raw('(SELECT SUM(quantity) FROM order_items WHERE order_items.order_id = vendor_payouts.order_id AND order_items.vendor_id = vendor_payouts.vendor_id) as items_qty'))
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) >= 1 && trim($dates[0]) !== '') {
                $query->whereDate('created_at', '>=', trim($dates[0]));
            }
            if (count($dates) >= 2 && trim($dates[1]) !== '') {
                $query->whereDate('created_at', '<=', trim($dates[1]));
            }
        }
        $payouts = $query->paginate(15)->withQueryString();

        // Fetch allowed payout frequencies
        $payoutFrequencies = GeneralSetting::where('key', 'payout_frequencies')->first();
        $allowedFrequencies = $payoutFrequencies ? json_decode($payoutFrequencies->value, true) : ['weekly', 'monthly', 'bi-weekly', 'daily'];

        return view('backend.vendor.payout.index', compact('payouts', 'allowedFrequencies'));
    }

    public function show($id)
    {
        $vendorId = Auth::id();
        $payout = VendorPayout::with(['order.user', 'vendor.country' ])
            ->where('vendor_id', $vendorId)
            ->findOrFail($id);
        $refs = ['PAYOUT-' . $payout->id];
        if (!empty($payout->order_id)) {
            $refs[] = 'VENDOR-SETTLEMENT-' . $payout->order_id . '-' . $payout->vendor_id;
        }
        $transactions = WalletTransaction::where('user_id', $vendorId)
            ->whereIn('reference_id', $refs)
            ->orderBy('created_at', 'desc')
            ->get();
        $items = collect();
        if (!empty($payout->order_id)) {
            $items = OrderItem::with('product')
                ->where('order_id', $payout->order_id)
                ->where('vendor_id', $vendorId)
                ->get();
        }
        return view('backend.vendor.payout.show', compact('payout', 'transactions', 'items'));
    }

    public function export_selected(Request $request)
    {
        $vendorId = Auth::id();
        $ids = (array) $request->input('ids', []);
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No payouts selected for export.');
        }
        $rows = VendorPayout::where('vendor_id', $vendorId)
            ->whereIn('id', $ids)
            ->orderBy('id', 'desc')
            ->get();
        $filename = "my_payouts_selected_" . date('Ymd_His') . ".csv";
        $headers = ['Payout ID', 'Items Qty', 'Order Amount', 'Commission', 'Payout Amount', 'Status', 'Date'];
        $file = fopen('php://temp', 'w');
        fputcsv($file, $headers);
        foreach ($rows as $p) {
            fputcsv($file, [
                'VP-' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
                (int) ($p->items_qty ?? 0),
                $p->order_amount ?? 0,
                $p->commission_amount ?? 0,
                $p->payout_amount ?? 0,
                $p->status ?? 'pending',
                $p->paid_at ? $p->paid_at->format('Y-m-d') : ($p->created_at ? $p->created_at->format('Y-m-d') : ''),
            ]);
        }
        rewind($file);
        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function requestPayout(Request $request)
    {
        $vendorId = Auth::id();
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'order_item_id' => 'nullable|exists:order_items,id',
        ]);

        if (!$request->order_id && !$request->order_item_id) {
            return redirect()->back()->withErrors(['order_id' => 'Provide order_id or order_item_id']);
        }

        $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);

        $orderId = null;
        $grossOrder = 0;
        $commissionTotal = 0;
        $pgFeeTotal = 0;
        $campaignDiscountShare = 0;

        if ($request->order_item_id) {
            $item = OrderItem::with('order')
                ->where('id', $request->order_item_id)
                ->where('vendor_id', $vendorId)
                ->where('status', 3) // delivered
                ->first();
            if (!$item) {
                return redirect()->back()->withErrors(['order_item_id' => 'Delivered item not found or not accessible']);
            }
            $orderId = $item->order_id;
            $amt = $item->total_actual_price ?? ($item->price * $item->quantity);
            $grossOrder += $amt;
            $commissionTotal += ($amt * $commissionRate) / 100;
            $pgFeeTotal += ($amt * $pgFeePercent) / 100;
            // Campaign discount share disabled for all calculations
            $campaignDiscountShare += 0;
        } else {
            // order-level for this vendor (only delivered items)
            $order = Order::find($request->order_id);
            if (!$order) {
                return redirect()->back()->withErrors(['order_id' => 'Order not found']);
            }
            // Ensure this vendor has delivered items in this order
            $items = OrderItem::where('order_id', $order->id)
                ->where('vendor_id', $vendorId)
                ->where('status', 3)
                ->get();
            if ($items->isEmpty()) {
                return redirect()->back()->withErrors(['order_id' => 'No delivered items for this order']);
            }
            $orderId = $order->id;
            foreach ($items as $it) {
                $amt = $it->total_actual_price ?? ($it->price * $it->quantity);
                $grossOrder += $amt;
                $commissionTotal += ($amt * $commissionRate) / 100;
                $pgFeeTotal += ($amt * $pgFeePercent) / 100;
                // Campaign discount share disabled for all calculations
                $campaignDiscountShare += 0;
            }
        }

        // Prevent duplicate payout
        $exists = VendorPayout::where('vendor_id', $vendorId)
            ->where('order_id', $orderId)
            ->whereIn('status', ['unpaid', 'paid'])
            ->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['order_id' => 'Payout already requested for this order']);
        }

        $payoutAmount = max(0, $grossOrder - $commissionTotal - $pgFeeTotal - $campaignDiscountShare);
        if ($payoutAmount <= 0) {
            return redirect()->back()->withErrors(['order_id' => 'Computed payout is zero after fees']);
        }

        $p = new VendorPayout();
        $p->vendor_id = $vendorId;
        $p->order_id = $orderId;
        $p->order_amount = $grossOrder;
        $p->commission_amount = $commissionTotal;
        $p->payout_amount = $payoutAmount;
        $p->payment_method = 'Wallet';
        $p->status = 'pending';
        $p->note = $request->order_item_id ? ('Vendor requested payout for item #' . $request->order_item_id) : 'Vendor requested payout';
        $p->save();

        \App\Helpers\NotificationHelper::notifyAdmins([
            'title' => 'Vendor Payout Request',
            'message' => 'Vendor #' . $vendorId . ' requested payout for Order #' . $orderId . ' amount ' . number_format($payoutAmount, 2),
            'type' => 'finance',
            'url' => route('vendor.payout.show', $p->id),
            'icon' => 'solar:hand-money-linear',
            'priority' => 'high'
        ]);

        return redirect()->back()->with('success', 'Payout request submitted for approval');
    }

    public function updateFrequency(Request $request)
    {
        $request->validate([
            'frequency' => 'required|in:weekly,bi-weekly,monthly,daily'
        ]);

        $user = Auth::user();

        // Fetch global allowed payout frequencies
        $payoutFrequencies = GeneralSetting::where('key', 'payout_frequencies')->first();
        $globalAllowed = $payoutFrequencies ? json_decode($payoutFrequencies->value, true) : ['weekly', 'monthly', 'bi-weekly', 'daily'];

        if (!in_array($request->frequency, $globalAllowed)) {
            return response()->json(['status' => false, 'message' => 'This payout frequency is currently disabled by the administrator.']);
        }

        $user->payout_frequency = $request->frequency;
        $user->allowed_payout_frequencies = $request->allowed_payout_frequencies;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Payout frequency updated successfully.']);
    }
}
