<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WalletTransaction;
use App\Models\VendorPayout;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user();
        $mode = $request->get('type') === 'payout' ? 'payout' : 'transactions';
        if ($mode === 'payout') {
            $payouts = VendorPayout::where('vendor_id', $vendor->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString();
            return view('backend.vendor.wallet.index', compact('vendor', 'payouts', 'mode'));
        } else {
            $transactions = WalletTransaction::where('user_id', $vendor->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString();
            return view('backend.vendor.wallet.index', compact('vendor', 'transactions', 'mode'));
        }
    }

    public function requestWithdrawal(Request $request)
    {
        $vendor = Auth::user();
        $amount = (float) ($vendor->wallet_balance ?? 0);
        if ($amount <= 0) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'Insufficient balance'], 422);
            }
            return redirect()->route('vendor.wallet')->withErrors(['balance' => 'Insufficient balance']);
        }
        $p = new \App\Models\VendorPayout();
        $p->vendor_id = $vendor->id;
        $p->order_id = null;
        // Ensure non-null columns get safe defaults
        $p->order_amount = 0;
        $p->commission_amount = 0;
        $p->payout_amount = $amount;
        $p->payment_method = 'Wallet Withdrawal';
        $p->status = 'pending';
        $p->note = 'Vendor requested withdrawal';
        $p->save();
        \App\Helpers\NotificationHelper::notifyAdmins([
            'title' => 'Vendor Withdrawal Requested',
            'message' => 'Vendor ID ' . $vendor->id . ' requested withdrawal of ' . number_format($amount, 2),
            'type' => 'finance',
            'url' => route('vendor.payout.show', $p->id),
            'icon' => 'solar:hand-money-linear',
            'priority' => 'high'
        ]);
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Withdrawal requested', 'id' => $p->id]);
        }
        return redirect()->route('vendor.wallet')->with('success', 'Withdrawal requested successfully');
    }
}
