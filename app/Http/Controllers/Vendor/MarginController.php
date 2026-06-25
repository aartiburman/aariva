<?php
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarginController extends Controller
{
    public function index()
    {
        return view('backend.vendor.margin.index');
    }

    public function calculate(Request $request)
    {
        $price = (float) ($request->input('price', 0));
        $commissionPercent = (float) ($request->input('commission_percent', 0));
        $pgPercent = (float) ($request->input('pg_percent', 0));
        $discount = (float) ($request->input('discount', 0));
        $net = max(0, $price - ($price * $commissionPercent / 100) - ($price * $pgPercent / 100) - $discount);
        return response()->json([
            'status' => true,
            'data' => [
                'price' => $price,
                'commission_percent' => $commissionPercent,
                'pg_percent' => $pgPercent,
                'discount' => $discount,
                'net' => $net,
                'margin_percent' => $price > 0 ? round(($net / $price) * 100, 2) : 0,
            ]
        ]);
    }
}
