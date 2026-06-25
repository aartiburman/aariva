<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Campaign;

class CampaignBudgetHelper
{
    /**
     * Apply campaign discount usage against a vendor's campaign budget.
     *
     * This should be called whenever an order item uses a campaign discount.
     *
     * @param  int   $campaignId
     * @param  int   $vendorId
     * @param  float $discountAmount  Total discount amount to charge against budget
     * @return array ['status' => bool, 'message' => string]
     */
    public static function applyDiscountUsage(int $campaignId, int $vendorId, float $discountAmount): array
    {
        if ($discountAmount <= 0) {
            return ['status' => true, 'message' => 'No discount to apply'];
        }

        $campaign = Campaign::find($campaignId);
        if (!$campaign) {
            return ['status' => false, 'message' => 'Campaign not found'];
        }

        $now = now();

        // Campaign expired or disabled → participation disabled
        if (!$campaign->status || $now > $campaign->end_date) {
            return ['status' => false, 'message' => 'Campaign is not active'];
        }

        return DB::transaction(function () use ($campaign, $vendorId, $discountAmount) {
            $pivot = DB::table('campaign_vendors')
                ->where('campaign_id', $campaign->id)
                ->where('vendor_id', $vendorId)
                ->lockForUpdate()
                ->first();

            if (!$pivot || !$pivot->active || $pivot->status !== 'approved') {
                return ['status' => false, 'message' => 'Vendor is not approved for this campaign'];
            }

            $budgetTotal = (float) ($pivot->budget_total ?? 0);
            $budgetSpent = (float) ($pivot->budget_spent ?? 0);

            // If no budget configured, treat as unlimited
            if ($budgetTotal <= 0) {
                return ['status' => true, 'message' => 'Unlimited budget (no cap set)'];
            }

            $newSpent = $budgetSpent + $discountAmount;

            if ($newSpent >= $budgetTotal) {
                // Budget exhausted → clamp spent, disable vendor for this campaign and notify admin
                DB::table('campaign_vendors')
                    ->where('campaign_id', $campaign->id)
                    ->where('vendor_id', $vendorId)
                    ->update([
                        'budget_spent' => $budgetTotal,
                        'active'       => false,
                        'status'       => 'exhausted',
                        'updated_at'   => now(),
                    ]);

                NotificationHelper::notifyAdmins([
                    'title'   => 'Campaign Budget Exhausted',
                    'message' => "Vendor #{$vendorId} has exhausted their budget for campaign: {$campaign->name}. Participation has been disabled.",
                    'type'    => 'promotions',
                    'url'     => route('campaign.vendor.requests.page', ['id' => $campaign->id]),
                    'icon'    => 'solar:wallet-bold-duotone',
                    'priority'=> 'high',
                ]);

                return ['status' => false, 'message' => 'Vendor campaign budget exhausted'];
            }

            // Still within budget – just update spent
            DB::table('campaign_vendors')
                ->where('campaign_id', $campaign->id)
                ->where('vendor_id', $vendorId)
                ->update([
                    'budget_spent' => $newSpent,
                    'updated_at'   => now(),
                ]);

            return ['status' => true, 'message' => 'Discount usage applied'];
        });
    }
}
