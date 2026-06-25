<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Models\GeneralSetting;

class VendorMetricsController extends Controller
{
    public function marginSummary(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|integer|exists:users,id'
        ]);

        $vendorId = (int) $request->vendor_id;
        $grossSales = (float) OrderItem::where('vendor_id', $vendorId)
            ->where('payment_status', '1')
            ->sum('total_actual_price');

        $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $commissionAmount = ($grossSales * $commissionRate) / 100;
        $netPayout = $grossSales - $commissionAmount;

        return response()->json([
            'status' => true,
            'data' => [
                'gross_sales' => $grossSales,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'net_payout' => $netPayout
            ]
        ]);
    }
}

