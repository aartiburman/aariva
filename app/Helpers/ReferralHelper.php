<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Order;
use App\Models\ReferralReward;
use App\Models\WalletTransaction;
use App\Models\GeneralSetting;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\DB;

class ReferralHelper
{
    public const MIN_CART_VALUE = 1000;

    /**
     * Get referral settings from admin config.
     */
    public static function getReferrerReward(): float
    {
        return (float) (GeneralSetting::where('key', 'referral_referrer_reward')->value('value') ?? 200);
    }

    public static function getReferredReward(): float
    {
        return (float) (GeneralSetting::where('key', 'referral_referred_reward')->value('value') ?? 100);
    }

    public static function getMinCartValue(): float
    {
        return (float) (GeneralSetting::where('key', 'referral_min_cart_value')->value('value') ?? self::MIN_CART_VALUE);
    }

    public static function isReferralEnabled(): bool
    {
        return (bool) (GeneralSetting::where('key', 'referral_enabled')->value('value') ?? true);
    }

    /**
     * Process referral reward when referred user's first order is completed.
     * Called when order status = Delivered (4) and payment successful.
     */
    public static function processReferralReward(Order $order): void
    {
        if (!self::isReferralEnabled()) {
            return;
        }

        $user = $order->user;
        if (!$user || (string) $user->role !== '3') {
            return; // Only for customers
        }

        if (!$user->referred_by) {
            return; // Not referred
        }

        if ((string) ($order->payment_status ?? '') !== '1') {
            return; // Payment must be successful
        }

        $orderTotal = (float) ($order->total_cost ?? 0);
        $minCart = self::getMinCartValue();
        if ($orderTotal < $minCart) {
            return;
        }

        // Order must be fully delivered (all items status 3)
        $allDelivered = $order->items()->where('status', '!=', '3')->doesntExist();
        if (!$allDelivered) {
            return;
        }

        // First successful order only (no prior order with all items delivered + paid)
        $priorOrders = Order::where('user_id', $user->id)
            ->where('id', '!=', $order->id)
            ->where('payment_status', '1')
            ->get();
        $hasPriorCompletedOrder = false;
        foreach ($priorOrders as $prior) {
            if ($prior->items()->where('status', '!=', '3')->doesntExist() && $prior->items()->exists()) {
                $hasPriorCompletedOrder = true;
                break;
            }
        }

        if ($hasPriorCompletedOrder) {
            return;
        }

        // One reward per referred user (duplicate prevention)
        $alreadyRewarded = ReferralReward::where('referred_id', $user->id)->exists();
        if ($alreadyRewarded) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        $referrerAmount = self::getReferrerReward();
        $referredAmount = self::getReferredReward();

        if ($referrerAmount <= 0 && $referredAmount <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $user, $referrer, $referrerAmount, $referredAmount) {
            ReferralReward::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'order_id' => $order->id,
                'referrer_amount' => $referrerAmount,
                'referred_amount' => $referredAmount,
            ]);

            if ($referrerAmount > 0) {
                $referrer->reward_balance = ($referrer->reward_balance ?? 0) + $referrerAmount;
                $referrer->save();
                WalletTransaction::create([
                    'user_id' => $referrer->id,
                    'amount' => $referrerAmount,
                    'type' => 'credit',
                    'description' => 'referral_reward',
                    'reference_id' => 'REFERRAL-REFERRER-' . $order->id . '-' . $user->id,
                    'status' => 'completed',
                ]);
            }

            if ($referredAmount > 0) {
                $user->reward_balance = ($user->reward_balance ?? 0) + $referredAmount;
                $user->save();
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $referredAmount,
                    'type' => 'credit',
                    'description' => 'referral_reward',
                    'reference_id' => 'REFERRAL-REFERRED-' . $order->id . '-' . $user->id,
                    'status' => 'completed',
                ]);
            }
        });

        // Customer notifications: Referral reward credited
        if ($referrerAmount > 0) {
            NotificationHelper::notifyCustomer($referrer->id, [
                'title' => 'Referral Reward Credited',
                'message' => 'NPR ' . number_format($referrerAmount, 2) . ' has been credited to your reward balance for referring a friend.',
                'type' => 'promotions',
                'url' => '#',
                'icon' => 'solar:gift-linear',
                'priority' => 'medium',
            ]);
        }
        if ($referredAmount > 0) {
            NotificationHelper::notifyCustomer($user->id, [
                'title' => 'Referral Reward Credited',
                'message' => 'NPR ' . number_format($referredAmount, 2) . ' has been credited to your reward balance as a referred user.',
                'type' => 'promotions',
                'url' => '#',
                'icon' => 'solar:gift-linear',
                'priority' => 'medium',
            ]);
        }
    }

    /**
     * Check if email/phone was already used by a referred user who got reward (duplicate prevention).
     */
    public static function canUseReferralCode(?string $email, ?string $phone): bool
    {
        if (empty($email) && empty($phone)) {
            return true;
        }

        $referredIds = ReferralReward::pluck('referred_id')->toArray();
        if (empty($referredIds)) {
            return true;
        }

        $query = User::whereIn('id', $referredIds);
        if (!empty($email)) {
            $query->where('email', $email);
        } elseif (!empty($phone)) {
            $query->where('phone', $phone);
        }
        return !$query->exists();
    }
}
