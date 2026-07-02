<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
 
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\GeneralSetting;
use App\Models\VendorPayout;
use App\Models\Campaign;
use App\Models\Coupon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tickets:escalate')->hourly();
Schedule::command('inventory:check-low')->dailyAt('09:00');

// Uncomment if you want a nightly backfill as well
Schedule::command('payouts:backfill-history')->dailyAt('02:00');
// Schedule::command('payouts:backfill-history')->everyFiveMinutes();
// Schedule::command('payouts:auto-process')->everyFiveMinutes();
Schedule::command('wallet:reconcile')->hourly();

Artisan::command('payouts:auto-process', function () {
    $createdCredits = 0;
    $createdDebits = 0;
    $markedPaid = 0;
    DB::beginTransaction();
    try {
        $paid = VendorPayout::with('vendor')->where('status', 'paid')->where('payout_amount', '>', 0)->get();
        foreach ($paid as $p) {
            $refPayout = 'PAYOUT-' . $p->id;
            $hasPayout = WalletTransaction::where('reference_id', $refPayout)->exists();
            $hasSettlement = (!empty($p->order_id) && !empty($p->vendor_id)) ? WalletTransaction::where('reference_id', 'VENDOR-SETTLEMENT-' . $p->order_id . '-' . $p->vendor_id)->exists() : false;
            if (!$hasPayout && !$hasSettlement && $p->vendor && $p->payout_amount > 0) {
                $v = $p->vendor;
                $v->wallet_balance = ($v->wallet_balance ?? 0) + (float) $p->payout_amount;
                $v->save();
                WalletTransaction::create([
                    'user_id' => $v->id,
                    'amount' => (float) $p->payout_amount,
                    'type' => 'credit',
                    'description' => 'Vendor payout #' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
                    'reference_id' => $refPayout,
                    'status' => 'completed',
                ]);
                $createdCredits++;
            }
        }
        $pending = VendorPayout::with('vendor')->where('status', 'pending')->whereNotNull('order_id')->where('payout_amount', '>', 0)->get();
        foreach ($pending as $p) {
            $deliveredAll = OrderItem::where('order_id', $p->order_id)->where('vendor_id', $p->vendor_id)->where('status', '!=', 3)->doesntExist();
            if ($deliveredAll) {
                $refSett = 'VENDOR-SETTLEMENT-' . $p->order_id . '-' . $p->vendor_id;
                $hasSett = WalletTransaction::where('reference_id', $refSett)->exists();
                $hasPayout = WalletTransaction::where('reference_id', 'PAYOUT-' . $p->id)->exists();
                if (!$hasSett && !$hasPayout && $p->vendor && $p->payout_amount > 0) {
                    $v = $p->vendor;
                    $v->wallet_balance = ($v->wallet_balance ?? 0) + (float) $p->payout_amount;
                    $v->save();
                    WalletTransaction::create([
                        'user_id' => $v->id,
                        'amount' => (float) $p->payout_amount,
                        'type' => 'credit',
                        'description' => 'Vendor settlement for Order #' . $p->order_id,
                        'reference_id' => $refSett,
                        'status' => 'completed',
                    ]);
                    $p->status = 'paid';
                    $p->paid_at = now();
                    $p->payment_method = $p->payment_method ?: 'auto';
                    $p->save();
                    $createdCredits++;
                    $markedPaid++;
                }
            }
        }
        $withdrawals = VendorPayout::with('vendor')->whereNull('order_id')->where('status', 'paid')->where('payout_amount', '>', 0)->get();
        foreach ($withdrawals as $p) {
            $refW = 'WITHDRAWAL-' . $p->id;
            $hasW = WalletTransaction::where('reference_id', $refW)->exists();
            if (!$hasW && $p->vendor) {
                $v = $p->vendor;
                $d = min((float)$p->payout_amount, (float)($v->wallet_balance ?? 0));
                if ($d > 0) {
                    $v->wallet_balance = max(0, ($v->wallet_balance ?? 0) - $d);
                    $v->save();
                    WalletTransaction::create([
                        'user_id' => $v->id,
                        'amount' => $d,
                        'type' => 'debit',
                        'description' => 'Withdrawal #' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
                        'reference_id' => $refW,
                        'status' => 'completed',
                    ]);
                    $createdDebits++;
                }
            }
        }
        $refundItems = OrderItem::whereIn('status', [4,5])->get();
        $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);
        foreach ($refundItems as $it) {
            $orderId = $it->order_id;
            $vendorId = $it->vendor_id;
            $settRef = 'VENDOR-SETTLEMENT-' . $orderId . '-' . $vendorId;
            $hasSett = WalletTransaction::where('reference_id', $settRef)->exists();
            $refRefund = 'REFUND-' . $it->id;
            $hasRefund = WalletTransaction::where('reference_id', $refRefund)->exists();
            if ($hasSett && !$hasRefund) {
                $amount = $it->total_actual_price ?? ($it->price * $it->quantity);
                $commission = ($amount * $commissionRate) / 100;
                $pgFee = ($amount * $pgFeePercent) / 100;
                $campaignShare = ($it->campaign_discount ?? 0) * ($it->quantity ?? 1);
                $netRefund = max(0, $amount - $commission - $pgFee - $campaignShare);
                $v = User::find($vendorId);
                if ($v && $netRefund > 0) {
                    $v->wallet_balance = max(0, ($v->wallet_balance ?? 0) - $netRefund);
                    $v->save();
                    WalletTransaction::create([
                        'user_id' => $v->id,
                        'amount' => $netRefund,
                        'type' => 'debit',
                        'description' => 'Refund adjustment for Order Item #' . $it->id,
                        'reference_id' => $refRefund,
                        'status' => 'completed',
                    ]);
                    $vp = VendorPayout::where('order_id', $orderId)->where('vendor_id', $vendorId)->first();
                    if ($vp) {
                        $vp->payout_amount = max(0, (float)$vp->payout_amount - $netRefund);
                        $vp->save();
                    }
                    $createdDebits++;
                }
            }
        }
        DB::commit();
        $this->info('Self-test complete. Credits: ' . $createdCredits . ', Debits: ' . $createdDebits . ', Marked Paid: ' . $markedPaid);
    } catch (\Throwable $e) {
        DB::rollBack();
        $this->error('Self-test failed: ' . $e->getMessage());
    }
})->purpose('Check and fix wallet/payout inconsistencies');

Artisan::command('wallet:reconcile', function () {
    $fixed = 0;
    $checked = 0;
    DB::beginTransaction();
    try {
        $users = User::select('id', 'wallet_balance')->get();
        foreach ($users as $u) {
            $checked++;
            $sumCredits = (float) WalletTransaction::where('user_id', $u->id)->where('type', 'credit')->sum('amount');
            $sumDebits = (float) WalletTransaction::where('user_id', $u->id)->where('type', 'debit')->sum('amount');
            $calc = round($sumCredits - $sumDebits, 2);
            if ($calc < 0) $calc = 0.0;
            if ((float)$u->wallet_balance !== $calc) {
                User::where('id', $u->id)->update(['wallet_balance' => $calc]);
                $fixed++;
            }
        }
        DB::commit();
        $this->info('Wallet reconcile complete. Checked: ' . $checked . ', Fixed: ' . $fixed);
    } catch (\Throwable $e) {
        DB::rollBack();
        $this->error('Wallet reconcile failed: ' . $e->getMessage());
    }
})->purpose('Recalculate users.wallet_balance from wallet_transactions');

Artisan::command('payouts:normalize-status', function () {
    $updated = VendorPayout::where('status', 'processing')->update(['status' => 'pending']);
    $this->info('Normalized payout statuses: ' . $updated . ' records changed to pending');
})->purpose('Replace processing status with pending in vendor_payouts');

Artisan::command('promotions:expire', function () {
    $now = now();
    $expiredCampaignIds = Campaign::where('status', 1)
        ->whereNotNull('end_date')
        ->where('end_date', '<', $now)
        ->pluck('id')
        ->all();
    $cUpdated = 0;
    if (!empty($expiredCampaignIds)) {
        $cUpdated = Campaign::whereIn('id', $expiredCampaignIds)->update(['status' => 3, 'is_active' => 0]);
        DB::table('campaign_vendors')->whereIn('campaign_id', $expiredCampaignIds)->update(['active' => false, 'status' => 'closed', 'updated_at' => $now]);
    }
    $couponUpdated = Coupon::where('status', 1)
        ->whereNotNull('valid_until')
        ->where('valid_until', '<', $now)
        ->update(['status' => 0]);
    $this->info('Expired campaigns deactivated: ' . $cUpdated . ', expired coupons deactivated: ' . $couponUpdated);
})->purpose('Deactivate expired campaigns and coupons');

Schedule::command('promotions:expire')->hourly();

