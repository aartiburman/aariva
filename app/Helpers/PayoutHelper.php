<?php

namespace App\Helpers;

use App\Models\VendorPayout;
use App\Models\GeneralSetting;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class PayoutHelper
{
    /**
     * Generate a payout record for a delivered order item (Status: unpaid)
     * 
     * @param OrderItem $item
     * @return VendorPayout|null
     */
    public static function createPayoutForItem(OrderItem $item)
    {
        // try {
            // Check if payout already exists for this order item
            $exists = VendorPayout::where('order_item_id', $item->id)
                ->whereIn('status', ['unpaid', 'paid'])
                ->exists();

            if ($exists) {
                return null;
            }

            $vendor = $item->vendor;
            if (!$vendor) {
                return null;
            }

            $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
            $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);

            $grossAmount = $item->total_actual_price ?? ($item->price * $item->quantity);
            $commissionAmount = ($grossAmount * $commissionRate) / 100;
            $pgFeeAmount = ($grossAmount * $pgFeePercent) / 100;
            
            $payoutAmount = max(0, $grossAmount - $commissionAmount - $pgFeeAmount);

            $payout = new VendorPayout();
            $payout->vendor_id = $item->vendor_id;
            $payout->order_id = $item->order_id;
            $payout->order_item_id = $item->id;
            $payout->order_amount = $grossAmount;
            $payout->commission_amount = $commissionAmount;
            $payout->payment_method = 'Wallet';
            $payout->transaction_id = $item->transaction_id;
            $payout->items_qty = $item->quantity;
            $payout->pg_fee_amount = $pgFeeAmount;
            $payout->payout_amount = $payoutAmount;
            $payout->status = 'unpaid';
            $payout->payout_frequency = $vendor->payout_frequency ?? 'daily';
            $payout->note = 'Automatic payout generated on delivery for item #' . $item->id;
            $payout->save();

            return $payout;
        // } catch (\Exception $e) {
        //     Log::error('Failed to create payout for item #' . $item->id . ': ' . $e->getMessage());
        //     return null;
        // }
    }
}
