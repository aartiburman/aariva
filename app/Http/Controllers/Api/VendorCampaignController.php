<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VendorCampaignOptIn;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;

class VendorCampaignController extends Controller
{
    public function join(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'budget' => 'nullable|numeric|min:0.01'
        ]);

        $vendor = User::find($request->vendor_id);
        if ((string)$vendor->role !== '2') {
            return response()->json(['status' => false, 'message' => 'Only vendors can join campaign'], 403);
        }

        $campaign = \App\Models\Campaign::withCount(['vendors' => function($q) {
            $q->where('status', '!=', 'rejected');
        }])->find($request->campaign_id);
        $now = now();
        // Allow join request only BEFORE the campaign starts and if enabled
        if (!$campaign->status || $now >= $campaign->start_date) {
            return response()->json(['status' => false, 'message' => 'Join requests are allowed only before the campaign starts'], 400);
        }

        // Check max vendors limit: Allow 10% extra applications for the pool
        $maxApplications = $campaign->max_vendors ? (int) ceil(((int) $campaign->max_vendors) * 1.10) : 0;
        if ($max_vendors = $campaign->max_vendors) {
            if ($campaign->vendors_count >= $maxApplications) {
                // Check if vendor is already joined to allow updates
                $isJoined = DB::table('campaign_vendors')
                    ->where('campaign_id', $campaign->id)
                    ->where('vendor_id', $vendor->id)
                    ->exists();
                if (!$isJoined) {
                    return response()->json(['status' => false, 'message' => "Campaign has reached its maximum application limit ($maxApplications)."], 400);
                }
            }
        }

        if ($campaign->budget_per_vendor > 0) {
            $budget = (float) $campaign->budget_per_vendor;
        } else {
            if (!$request->budget) {
                return response()->json(['status' => false, 'message' => 'Budget is required'], 400);
            }
            $budget = (float) $request->budget;
        }

        // Edge case: block join if wallet is insufficient
        if ($vendor->wallet_balance < $budget) {
            return response()->json(['status' => false, 'message' => 'Insufficient wallet balance to allocate this campaign budget'], 400);
        }

        DB::transaction(function () use ($vendor, $campaign, $budget) {
            VendorCampaignOptIn::updateOrCreate(
                ['vendor_id' => $vendor->id],
                ['opted_in' => true]
            );

            // Attach/update vendor pivot with budget (pending approval: active = false)
            $existing = DB::table('campaign_vendors')
                ->where('campaign_id', $campaign->id)
                ->where('vendor_id', $vendor->id)
                ->first();
            if ($existing) {
                DB::table('campaign_vendors')
                    ->where('campaign_id', $campaign->id)
                    ->where('vendor_id', $vendor->id)
                    ->update([
                        'budget_total' => $budget,
                        'budget_spent' => 0,
                        'active' => false,
                        'status' => 'pending',
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('campaign_vendors')->insert([
                    'campaign_id' => $campaign->id,
                    'vendor_id' => $vendor->id,
                    'budget_total' => $budget,
                    'budget_spent' => 0,
                    'active' => false,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Do NOT deduct wallet here; deduction happens when admin approves the request
        });

        // Notify Admin
        NotificationHelper::notifyAdmins([
            'title' => 'New Campaign Join Request',
            'message' => "Vendor {$vendor->store_name} has requested to join campaign: {$campaign->name} with budget " . number_format((float) $budget, 2) . ".",
            'type' => 'promotions',
            'url' => route('campaign.vendor.requests.page', ['id' => $campaign->id]),
            'icon' => 'solar:hand-money-linear',
            'priority' => 'medium'
        ]);

        return response()->json(['status' => true, 'message' => 'Join request submitted. Awaiting admin approval.']);
    }

    public function optIn(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
        ]);

        $vendor = User::find($request->vendor_id);
        if ((string)$vendor->role !== '2') {
            return response()->json(['status' => false, 'message' => 'Only vendors can opt-in'], 403);
        }

        VendorCampaignOptIn::updateOrCreate(
            ['vendor_id' => $vendor->id],
            ['opted_in' => true]
        );

        return response()->json(['status' => true, 'message' => 'Vendor opted into campaign']);
    }

    public function optOut(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
        ]);

        $vendor = User::find($request->vendor_id);
        if ((string)$vendor->role !== '2') {
            return response()->json(['status' => false, 'message' => 'Only vendors can opt-out'], 403);
        }

        VendorCampaignOptIn::updateOrCreate(
            ['vendor_id' => $vendor->id],
            ['opted_in' => false]
        );

        return response()->json(['status' => true, 'message' => 'Vendor opted out of campaign']);
    }
}
