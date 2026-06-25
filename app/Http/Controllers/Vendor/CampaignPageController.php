<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Product;
use App\Models\GeneralSetting;
use App\Models\WalletTransaction;
use App\Models\VendorCampaignOptIn;
use App\Helpers\NotificationHelper;

class CampaignPageController extends Controller
{
    
    public function index(Request $request)
    {
        $vendorId = Auth::id();
        // Show all campaigns; action availability depends on status & date window
        $campaigns = Campaign::query()
            ->with(['vendors' => function($q) use ($vendorId) {
                $q->where('users.id', $vendorId);
            }])
            ->withCount('vendors')
            ->orderBy('is_active', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)->withQueryString();

        $pivotByCampaign = DB::table('campaign_vendors')
            ->where('vendor_id', $vendorId)
            ->pluck('budget_total', 'campaign_id');

        $joinedPivot = DB::table('campaign_vendors')
            ->where('vendor_id', $vendorId)
            ->get(['campaign_id', 'active', 'budget_total', 'budget_spent', 'status']);
        $joinedMap = [];
        foreach ($joinedPivot as $row) {
            $joinedMap[$row->campaign_id] = [
                'active' => (bool) ($row->active ?? false),
                'status' => $row->status ?? '0',
                'budget_total' => (float) ($row->budget_total ?? 0),
                'budget_spent' => (float) ($row->budget_spent ?? 0),
            ];
        }

        $optIn = VendorCampaignOptIn::where('vendor_id', $vendorId)->value('opted_in') ?? false;
        $commissionPercent = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);
        return view('backend.vendor.campaigns.index', compact('campaigns', 'pivotByCampaign', 'optIn', 'joinedMap', 'commissionPercent', 'pgFeePercent'));
    }

    public function join(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'budget' => 'nullable|numeric|min:0.01'
        ]);

        $vendor = Auth::user();
        $campaign = Campaign::withCount('vendors')->findOrFail($request->campaign_id);
        $now = now();
        // Allow join request only BEFORE the campaign starts and if enabled
        if (!$campaign->status || $now >= $campaign->start_date) {
            return redirect()->back()->withErrors(['campaign' => 'Join requests are allowed only before the campaign starts']);
        }

        // Check if vendor is already in another active campaign
        $activeCampaign = DB::table('campaign_vendors')
            ->join('campaigns', 'campaigns.id', '=', 'campaign_vendors.campaign_id')
            ->where('campaign_vendors.vendor_id', $vendor->id)
            ->where('campaigns.id', '!=', $campaign->id)
            ->where('campaigns.is_active', 1)
            ->where('campaigns.status', 1)
            ->where('campaigns.end_date', '>', now())
            ->where('campaign_vendors.status', 'approved')
            ->first();

        if ($activeCampaign) {
            return redirect()->back()->withErrors(['campaign' => 'You are already an approved participant in another active campaign.']);
        }


        if ($campaign->max_vendors) {
            $maxApplications = (int) ceil(((int) $campaign->max_vendors) * 10);
            if ($maxApplications < 1) {
                $maxApplications = 1;
            }

            if ($campaign->vendors_count >= $maxApplications) {
                $isJoined = DB::table('campaign_vendors')
                    ->where('campaign_id', $campaign->id)
                    ->where('vendor_id', $vendor->id)
                    ->exists();

                if (!$isJoined) {
                    return redirect()->back()->withErrors(['campaign' => 'Campaign is full for vendor applications']);
                }
            }
        }

        // Use fixed budget if set, otherwise require input
        if ($campaign->budget_per_vendor > 0) {
            $budget = (float) $campaign->budget_per_vendor;
        } else {
            if (!$request->budget) {
                return redirect()->back()->withErrors(['budget' => 'Budget is required']);
            }
            $budget = (float) $request->budget;
        }

        // Edge case: block join if wallet is insufficient
        if ($vendor->wallet_balance < $budget) {
            return redirect()->back()->withErrors(['budget' => 'Insufficient wallet balance to allocate this campaign budget']);
        }

        DB::transaction(function () use ($vendor, $campaign, $budget) {
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

            // Do NOT deduct wallet here; admin will approve and deduct later
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

        return redirect()->route('vendor.campaigns')->with('success', 'Join request submitted. Awaiting admin approval.');
    }

    public function optIn(Request $request)
    {
        $vendorId = Auth::id();
        VendorCampaignOptIn::updateOrCreate(
            ['vendor_id' => $vendorId],
            ['opted_in' => true]
        );
        return redirect()->route('vendor.campaigns')->with('success', 'You have opted into campaign promotions.');
    }

    public function optOut(Request $request)
    {
        $vendorId = Auth::id();
        VendorCampaignOptIn::updateOrCreate(['vendor_id' => $vendorId], ['opted_in' => false]);
        return redirect()->route('vendor.campaigns')->with('success', 'You have opted out of campaign promotions.');
    }

    public function manageProducts($id)
    {
        $vendorId = Auth::id();
        $campaign = Campaign::findOrFail($id);
        
        // Check if vendor joined this campaign (accept 'approved' or '1')
        $joined = DB::table('campaign_vendors')
            ->where('campaign_id', $campaign->id)
            ->where('vendor_id', $vendorId)
            ->whereIn('status', ['approved', '1'])
            ->exists();

        if (!$joined) {
            return redirect()->route('vendor.campaigns')->with('error', 'You must be an approved participant to add products.');
        }

        // Get vendor's products already in this campaign
        $campaignProducts = Product::join('campaign_products', 'products.id', '=', 'campaign_products.product_id')
            ->where('campaign_products.campaign_id', $campaign->id)
            ->where('products.vendor_id', $vendorId)
            ->select('products.*', 'campaign_products.status as campaign_status')
            ->get();

        // Get vendor's products NOT in any active campaign and are approved (status = 1)
        // AND are currently active (is_active = 1)
        $availableProducts = Product::select('*')
            ->where('status', 1) // approved by admin
            ->where('is_active', 1) // active by vendor
            ->where('vendor_id', $vendorId)
            ->whereNotIn('id', function($query) {
                $query->select('campaign_products.product_id')
                    ->from('campaign_products')
                    ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
                    ->where('campaigns.is_active', 1)
                    ->where('campaigns.status', 1)
                    ->where('campaigns.end_date', '>', now());
            })
            ->get();
            // echo '<pre>' . print_r($availableProducts, true) . '</pre>';die;

        return view('backend.vendor.campaigns.manage_products', compact('campaign', 'campaignProducts', 'availableProducts'));
    }

    public function addProducts(Request $request, $id)
    {        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $vendorId = Auth::id();
        $campaign = Campaign::findOrFail($id);

        // Verify all products belong to this vendor and are not in any other campaign
        $products = \App\Models\Product::where('vendor_id', $vendorId)
            ->whereIn('id', $request->product_ids)
            ->get();

        if ($products->count() !== count($request->product_ids)) {
            return redirect()->back()->with('error', 'Invalid products selected.');
        }

        // Check if any of these products are already in any active campaign
        $alreadyAssigned = DB::table('campaign_products')
            ->whereIn('product_id', $request->product_ids)
            ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
            ->where('campaigns.is_active', 1)
            ->where('campaigns.status', 1)
            ->where('campaigns.end_date', '>', now())
            ->pluck('product_id')
            ->toArray();
        
        if (!empty($alreadyAssigned)) {
            $productNames = $products->whereIn('id', $alreadyAssigned)->pluck('name')->toArray();
            return redirect()->back()->with('error', 'The following products are already assigned to a campaign: ' . implode(', ', $productNames));
        }

        foreach ($request->product_ids as $productId) {
            DB::table('campaign_products')->insert([
                'campaign_id' => $campaign->id,
                'product_id' => $productId,
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Trigger notification to Admin
        \App\Helpers\NotificationHelper::notifyAdmins([
            'title' => 'New Campaign Product Request',
            'message' => 'Vendor ' . Auth::user()->name . ' has submitted products for campaign: ' . $campaign->name,
            'type' => 'promotions',
            'url' => route('campaign.product.requests', $campaign->id),
            'icon' => 'solar:box-minimalistic-linear',
            'priority' => 'medium'
        ]);

        return redirect()->back()->with('success', 'Products submitted for approval.');
    }
}
