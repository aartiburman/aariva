<?php

namespace App\Helpers;

use App\Models\OrderItem;

class PriceHelper
{
    public static function applyDiscount($price, $discountType, $discountValue)
    {
        if (!$discountType || !$discountValue) {
            return round($price, 2);
        }

        if (in_array($discountType, ['percent', '%'])) {
            return round($price - ($price * $discountValue / 100), 2);
        }

        if (in_array($discountType, ['flat', 'off'])) {
            return round(max($price - $discountValue, 0), 2);
        }

        return round($price, 2);
    }

    public static function formatLargeNumber($number)
    {
        if ($number >= 10000000) {
            return number_format($number / 10000000, 2) . ' Cr';
        } elseif ($number >= 100000) {
            return number_format($number / 100000, 2) . ' Lakh';
        } elseif ($number >= 1000) {
            return number_format($number / 1000, 2) . 'k';
        }

        return number_format($number, 2);
    }

    public static function formatPrice($price)
    {
        $symbol = session('currency_symbol', '₹');
        $code = session('currency_code', 'INR');

        $decimalCurrencies = ['JPY', 'KRW', 'VND', 'IDR', 'HUF', 'TWD'];
        $decimals = in_array($code, $decimalCurrencies) ? 0 : 2;

        return $symbol . ' ' . number_format($price, $decimals);
    }

    public static function getVendorTotalSale($vendorId)
    {
        return OrderItem::where('vendor_id', $vendorId)
            ->where('payment_status', '1') // 1 for paid
            ->sum('total_actual_price');
    }
}
