<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Product;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        try {
            $now = Carbon::now();

            // Find campaigns that have ended but are still marked as active
            $expiredIds = Campaign::where('status', 1)
                ->whereNotNull('end_date')
                ->where('end_date', '<', $now)
                ->pluck('id')
                ->toArray();

            if (!empty($expiredIds)) {
                // Update campaign status to 3 (Closed/Expired)
                Campaign::whereIn('id', $expiredIds)->update([
                    'status' => 3, 
                    'is_active' => 0, 
                    'updated_at' => $now
                ]);
                
                // Update vendor participation status
                DB::table('campaign_vendors')
                    ->whereIn('campaign_id', $expiredIds)
                    ->update([
                        'active' => 0, 
                        'status' => 'closed', 
                        'updated_at' => $now
                    ]);
            }

            $campaigns = Campaign::withCount(['vendors', 'products'])
                ->with('offer')
                ->orderBy('is_active', 'desc')
                ->orderBy('updated_at', 'desc')
                ->paginate(15)->withQueryString();

            $campaigns->getCollection()->transform(function ($c) use ($now) {
                // Priority: 1. Manual Closed, 2. Expired (End Time Passed), 3. Upcoming (Start Time Not Reached), 4. Active (Within Time Window)
                $isClosed   = (int)$c->status === 0;
                $isExpired  = $c->end_date && $now->greaterThan($c->end_date);
                $isUpcoming = $c->start_date && $now->lessThan($c->start_date);
                
                // Active if not closed/expired/upcoming (this means now is between start and end including both boundaries)
                if ($isClosed) {
                    $c->status_label = 'Closed';
                    $c->status_badge = 'bg-secondary';
                } elseif ($isExpired) {
                    $c->status_label = 'Expired';
                    $c->status_badge = 'bg-danger';
                } elseif ($isUpcoming) {
                    $c->status_label = 'Upcoming';
                    $c->status_badge = 'bg-warning';
                } else {
                    $c->status_label = 'Active';
                    $c->status_badge = 'bg-success';
                }

                return $c;
            });
            $offers = \App\Models\Offer::where('status', 1)->orderBy('code')->get();

            return view('backend.admin.campaigns.index', compact('campaigns', 'offers'));
        } catch (\Throwable $e) {
            // Log the error for the developer
            \Illuminate\Support\Facades\Log::error("Campaign List Error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Show error directly to the user as requested
            return response("Campaign List Error: " . $e->getMessage() . " at line " . $e->getLine() . " in " . $e->getFile(), 200)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function create()
    {
        // Get vendors not assigned to any active campaign
        $vendors = User::where('role', '2')
            ->whereNotIn('id', function($query) {
                $query->select('campaign_vendors.vendor_id')
                    ->from('campaign_vendors')
                    ->join('campaigns', 'campaigns.id', '=', 'campaign_vendors.campaign_id')
                    ->where('campaigns.is_active', 1)
                    ->where('campaigns.status', 1)
                    ->where('campaigns.end_date', '>', now())
                    ->where('campaign_vendors.status', 'approved');
            })
            ->orderBy('name')
            ->get(['id','name','store_name']);

        // Get products not assigned to any active campaign and are approved (status = 1)
        $products = Product::where('status', 1)
            ->whereNotIn('id', function($query) {
                $query->select('campaign_products.product_id')
                    ->from('campaign_products')
                    ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
                    ->where('campaigns.is_active', 1)
                    ->where('campaigns.status', 1)
                    ->where('campaigns.end_date', '>', now());
            })
            ->orderBy('name')
            ->get(['id','name']);

        $offers = \App\Models\Offer::where('status', 1)->orderBy('code')->get();
        return view('backend.admin.campaigns.create', compact('vendors','products', 'offers'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('campaigns', 'name')],
                'discount_percent' => 'required|numeric|min:0.01',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => ['required', Rule::in([0, 1, true, false])],
                'offer_id' => 'nullable|exists:offers,id',
                'budget_per_vendor' => 'nullable|numeric|min:0.01',
                'max_vendors' => 'nullable|integer|min:1',
                'vendor_ids' => 'array',
                'vendor_ids.*' => 'integer|exists:users,id',
                'product_ids' => 'array',
                'product_ids.*' => 'integer|exists:products,id',
            ]);

            return DB::transaction(function () use ($request, $validated) {
                $campaign = Campaign::create([
                    'name' => $validated['name'],
                    'discount_percent' => $validated['discount_percent'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'status' => (bool) $validated['status'],
                    'is_active' => (bool) $validated['status'],
                    'offer_id' => $request->input('offer_id'),
                    'budget_per_vendor' => $request->input('budget_per_vendor'),
                    'max_vendors' => $request->input('max_vendors'),
                ]);

                $vendorIds = $request->input('vendor_ids', []);
                $productIds = $request->input('product_ids', []);

                // Validate vendors are not in any other active campaign
                if (!empty($vendorIds)) {
                    $alreadyAssignedVendors = DB::table('campaign_vendors')
                        ->whereIn('campaign_vendors.vendor_id', $vendorIds)
                        ->join('campaigns', 'campaigns.id', '=', 'campaign_vendors.campaign_id')
                        ->where('campaigns.is_active', 1)
                        ->where('campaigns.status', 1)
                        ->where('campaigns.end_date', '>', now())
                        ->where('campaign_vendors.status', 'approved')
                        ->join('users', 'users.id', '=', 'campaign_vendors.vendor_id')
                        ->pluck('users.name')
                        ->toArray();
                    
                    if (!empty($alreadyAssignedVendors)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'vendor_ids' => 'The following vendors are already assigned to an active campaign: ' . implode(', ', $alreadyAssignedVendors),
                        ]);
                    }
                }

                // Validate products are not in any other active campaign
                if (!empty($productIds)) {
                    $alreadyAssignedProducts = DB::table('campaign_products')
                        ->whereIn('campaign_products.product_id', $productIds)
                        ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
                        ->where('campaigns.is_active', 1)
                        ->where('campaigns.status', 1)
                        ->where('campaigns.end_date', '>', now())
                        ->join('products', 'products.id', '=', 'campaign_products.product_id')
                        ->pluck('products.name')
                        ->toArray();
                    
                    if (!empty($alreadyAssignedProducts)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'product_ids' => 'The following products are already assigned to an active campaign: ' . implode(', ', $alreadyAssignedProducts),
                        ]);
                    }
                }

                if (!empty($vendorIds) && !empty($campaign->max_vendors) && count($vendorIds) > (int) $campaign->max_vendors) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vendor_ids' => 'You can select at most ' . (int) $campaign->max_vendors . ' vendors.',
                    ]);
                }

                if (!empty($vendorIds)) {
                    $budgetPerVendor = (float) ($campaign->budget_per_vendor ?? 0);
                    $vendors = User::whereIn('id', $vendorIds)->where('role', '2')->get();

                    // Validate sufficient wallet balance before deducting (admin-assigned vendors)
                    $insufficientVendors = [];
                    if ($budgetPerVendor > 0) {
                        foreach ($vendors as $v) {
                            if ((float)($v->wallet_balance ?? 0) < $budgetPerVendor) {
                                $insufficientVendors[] = $v;
                            }
                        }
                    }

                    // Filter out vendors with insufficient balance and proceed with the rest
                    if (!empty($insufficientVendors)) {
                        $insufficientIds = collect($insufficientVendors)->pluck('id')->all();
                        $vendors = $vendors->reject(fn($v) => in_array($v->id, $insufficientIds));
                        // Flash a warning so UI can inform and keep previous valid selections
                        session()->flash('vendor_skip_warning', 'Skipped vendors due to low wallet: ' . collect($insufficientVendors)->map(fn($v) => $v->store_name ?? $v->name)->implode(', ') . '.');
                    }

                    $sync = [];
                    foreach ($vendors as $v) {
                        $vid = $v->id;
                        $sync[$vid] = [
                            'budget_total' => $budgetPerVendor > 0 ? $budgetPerVendor : 0,
                            'budget_spent' => 0,
                            'active' => false,
                            'status' => $budgetPerVendor > 0 ? 'pending' : null,
                        ];
                    }
                    if (!empty($sync)) {
                        $campaign->vendors()->sync($sync);
                    }
                    $effectiveVendorIds = array_keys($sync);

                    // Deduct from vendor wallet when admin assigns (joined, needs approval)
                    if ($budgetPerVendor > 0 && $vendors->count()) {
                        foreach ($vendors as $vendor) {
                            $vendor->wallet_balance = ($vendor->wallet_balance ?? 0) - $budgetPerVendor;
                            $vendor->save();
                            WalletTransaction::create([
                                'user_id' => $vendor->id,
                                'amount' => $budgetPerVendor,
                                'type' => 'debit',
                                'description' => 'campaign_hold',
                                'reference_id' => 'CAMP-PENDING-' . $campaign->id . '-' . $vendor->id,
                                'status' => 'completed',
                            ]);
                        }
                    }
                }

                if (!empty($productIds)) {
                    // Phase-1: ensure products not already assigned elsewhere in an active campaign
                    $existing = DB::table('campaign_products')
                        ->whereIn('product_id', $productIds)
                        ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
                        ->where('campaigns.is_active', 1)
                        ->where('campaigns.status', 1)
                        ->where('campaigns.end_date', '>', now())
                        ->pluck('product_id')
                        ->toArray();
                    
                    if (!empty($existing)) {
                        if ($request->expectsJson() || $request->ajax()) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Some products are already assigned to another active campaign',
                                'conflicts' => $existing
                            ], 422);
                        }
                        return redirect()->back()->withErrors(['product_ids' => 'Some products are already assigned to another active campaign'])->withInput();
                    }
                    $campaign->products()->sync($productIds);
                }

                // Notify Assigned Vendors
                if (!empty($vendorIds ?? []) && !empty($effectiveVendorIds ?? [])) {
                    $vendors = User::whereIn('id', $effectiveVendorIds)->get();
                    foreach ($vendors as $vendor) {
                        $vendorBudgetText = $campaign->budget_per_vendor > 0
                            ? ' with a budget of ' . number_format((float) $campaign->budget_per_vendor, 2)
                            : '';

                        NotificationHelper::notifyVendor($vendor->id, [
                            'title' => 'New Campaign Assigned',
                            'message' => "You have been invited to join the campaign: {$campaign->name}{$vendorBudgetText}.",
                            'type' => 'promotions',
                            'url' => route('vendor.campaigns'),
                            'icon' => 'solar:ticket-sale-linear',
                            'priority' => 'medium'
                        ]);
                    }
                }

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['status' => true, 'message' => 'Campaign created', 'data' => $campaign]);
                }
                return redirect()->route('campaign.list')->with('success', 'Campaign created successfully');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Campaign Error: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
            return "Campaign Store Error: " . $e->getMessage() . " at line " . $e->getLine() . " in " . $e->getFile();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('campaigns', 'name')->ignore($campaign->id)],
                'discount_percent' => 'sometimes|required|numeric|min:0.01',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'status' => ['sometimes', 'required', Rule::in([0, 1, true, false])],
                'offer_id' => 'nullable|exists:offers,id',
                'budget_per_vendor' => 'nullable|numeric|min:0.01',
                'max_vendors' => 'nullable|integer|min:1',
                'vendor_ids' => 'array',
                'vendor_ids.*' => 'integer|exists:users,id',
                'product_ids' => 'array',
                'product_ids.*' => 'integer|exists:products,id',
            ]);

            return DB::transaction(function () use ($request, $campaign, $validated) {
                $campaign->fill($validated);
                if ($request->has('offer_id')) {
                    $campaign->offer_id = $request->input('offer_id');
                }
                if ($request->has('status')) {
                    $campaign->is_active = (bool) $request->input('status');
                }
                if ($request->has('budget_per_vendor')) {
                    $campaign->budget_per_vendor = $request->input('budget_per_vendor');
                }
                if ($request->has('max_vendors')) {
                    $campaign->max_vendors = $request->input('max_vendors');
                }
                $campaign->save();

                $vendorIdsLimit = null;
                if (!empty($campaign->max_vendors)) {
                    $vendorIdsLimit = (int) $campaign->max_vendors;
                }

                if ($request->has('vendor_ids')) {
                    $inputVendorIds = $request->input('vendor_ids', []);

                    // Validate vendors are not in other active campaigns
                    if (!empty($inputVendorIds)) {
                        $alreadyAssignedVendors = DB::table('campaign_vendors')
                            ->where('campaign_vendors.campaign_id', '!=', $campaign->id)
                            ->whereIn('campaign_vendors.vendor_id', $inputVendorIds)
                            ->join('campaigns', 'campaigns.id', '=', 'campaign_vendors.campaign_id')
                            ->where('campaigns.is_active', 1)
                            ->where('campaigns.status', 1)
                            ->where('campaigns.end_date', '>', now())
                            ->where('campaign_vendors.status', 'approved')
                            ->join('users', 'users.id', '=', 'campaign_vendors.vendor_id')
                            ->pluck('users.name')
                            ->toArray();
                        
                        if (!empty($alreadyAssignedVendors)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'vendor_ids' => 'The following vendors are already assigned to another active campaign: ' . implode(', ', $alreadyAssignedVendors),
                            ]);
                        }
                    }

                    if ($vendorIdsLimit !== null && count($inputVendorIds) > $vendorIdsLimit) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'vendor_ids' => 'You can select at most ' . $vendorIdsLimit . ' vendors.',
                        ]);
                    }

                    $vendors = User::whereIn('id', $request->input('vendor_ids', []))
                        ->where('role', '2')->pluck('id')->toArray();
                    $existingPivot = $campaign->vendors()->pluck('campaign_vendors.vendor_id')->toArray();
                    $sync = [];
                    foreach ($vendors as $vid) {
                        $current = DB::table('campaign_vendors')
                            ->where('campaign_id', $campaign->id)
                            ->where('vendor_id', $vid)
                            ->first();
                        $sync[$vid] = [
                            'budget_total' => $current->budget_total ?? 0,
                            'budget_spent' => $current->budget_spent ?? 0,
                            'active' => $current->active ?? false
                        ];
                    }
                    $campaign->vendors()->sync($sync);
                }

                if ($request->has('product_ids')) {
                    $productIds = $request->input('product_ids', []);

                    // Validate products are not in other active campaigns
                    if (!empty($productIds)) {
                        $alreadyAssignedProducts = DB::table('campaign_products')
                            ->where('campaign_products.campaign_id', '!=', $campaign->id)
                            ->whereIn('campaign_products.product_id', $productIds)
                            ->join('campaigns', 'campaigns.id', '=', 'campaign_products.campaign_id')
                            ->where('campaigns.is_active', 1)
                            ->where('campaigns.status', 1)
                            ->where('campaigns.end_date', '>', now())
                            ->join('products', 'products.id', '=', 'campaign_products.product_id')
                            ->pluck('products.name')
                            ->toArray();
                        
                        if (!empty($alreadyAssignedProducts)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'product_ids' => 'The following products are already assigned to another active campaign: ' . implode(', ', $alreadyAssignedProducts),
                            ]);
                        }
                    }

                    $campaign->products()->sync($productIds);
                }

                return response()->json(['status' => true, 'message' => 'Campaign updated', 'data' => $campaign]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Campaign Update Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();
        return response()->json(['status' => true, 'message' => 'Campaign deleted']);
    }

    public function change_status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:campaigns,id',
            'status' => ['required', Rule::in([0,1,true,false])]
        ]);
        $campaign = Campaign::find($request->id);
        $text = $request->status === 1 ? 'Active' : 'Inactive';
        // Update is_active and status for toggle behavior
        $campaign->is_active = (bool) $request->status;
        $campaign->status = (bool) $request->status;
        $campaign->save();
        return response()->json(['status' => true, 'message' => 'Campaign ' . $text . ' successfully']);
    }

    public function close_all(Request $request)
    {
        DB::table('campaigns')->update(['status' => 0, 'is_active' => 0, 'updated_at' => now()]);
        return redirect()->route('campaign.list')->with('success', 'All campaigns have been closed.');
    }

    public function vendor_requests(Request $request, $campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $pending = DB::table('campaign_vendors')
            ->join('users', 'users.id', '=', 'campaign_vendors.vendor_id')
            ->select('users.id as vendor_id', 'users.name', 'users.store_name', 'campaign_vendors.budget_total', 'campaign_vendors.active', 'campaign_vendors.budget_spent', 'campaign_vendors.status')
            ->where('campaign_vendors.campaign_id', $campaign->id)
            ->where('campaign_vendors.status', 'pending')
            ->get();
        return response()->json([
            'status' => true,
            'campaign' => [
                'id' => $campaign->id,
                'name' => $campaign->name
            ],
            'data' => $pending
        ]);
    }

    public function vendor_requests_page(Request $request, $campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $vendors = DB::table('campaign_vendors')
            ->join('users', 'users.id', '=', 'campaign_vendors.vendor_id')
            ->select('users.id as vendor_id', 'users.name', 'users.store_name', 'campaign_vendors.budget_total', 'campaign_vendors.active', 'campaign_vendors.budget_spent', 'campaign_vendors.status')
            ->where('campaign_vendors.campaign_id', $campaign->id)
            ->orderByRaw("CASE WHEN campaign_vendors.status = 'pending' THEN 0 WHEN campaign_vendors.status = 'approved' THEN 1 ELSE 2 END")
            ->orderBy('campaign_vendors.updated_at', 'desc')
            ->paginate(15)->withQueryString();
        return view('backend.admin.campaigns.vendor_requests', compact('campaign', 'vendors'));
    }

    public function vendor_bulk_action(Request $request, $campaignId)
    {
        $request->validate([
            'vendor_ids' => 'required|array',
            'action' => 'required|in:approve,reject'
        ]);

        $campaign = Campaign::findOrFail($campaignId);
        $vendorIds = $request->vendor_ids;
        $action = $request->action;
        $successCount = 0;
        $errors = [];

        foreach ($vendorIds as $vendorId) {
            try {
                if ($action === 'approve') {
                    // Reuse existing approval logic but with slight modifications for bulk context
                    $pivot = DB::table('campaign_vendors')
                        ->where('campaign_id', $campaign->id)
                        ->where('vendor_id', $vendorId)
                        ->whereIn('status', ['pending', 'rejected'])
                        ->first();
                    
                    if (!$pivot) continue;

                    $vendor = User::find($vendorId);
                    $budget = (float) ($pivot->budget_total ?? 0);

                    // Check max vendors limit
                    if ($campaign->max_vendors) {
                        $approvedCount = DB::table('campaign_vendors')
                            ->where('campaign_id', $campaign->id)
                            ->where('status', 'approved')
                            ->count();
                        if ($approvedCount >= $campaign->max_vendors) {
                            $errors[] = "Limit reached: Cannot approve more vendors.";
                            break; 
                        }
                    }

                    $alreadyDeducted = WalletTransaction::where('user_id', $vendorId)
                        ->where('reference_id', 'CAMP-PENDING-' . $campaign->id . '-' . $vendorId)
                        ->where('type', 'debit')
                        ->exists();

                    if (!$alreadyDeducted && ($vendor->wallet_balance ?? 0) < $budget) {
                        $errors[] = "Insufficient balance for vendor: " . ($vendor->store_name ?? $vendor->name);
                        continue;
                    }

                    DB::transaction(function () use ($campaign, $vendor, $budget, $alreadyDeducted) {
                        DB::table('campaign_vendors')
                            ->where('campaign_id', $campaign->id)
                            ->where('vendor_id', $vendor->id)
                            ->update([
                                'active' => true,
                                'status' => 'approved',
                                'budget_spent' => 0,
                                'updated_at' => now()
                            ]);
                        if (!$alreadyDeducted) {
                            $vendor->wallet_balance = ($vendor->wallet_balance ?? 0) - $budget;
                            $vendor->save();
                            WalletTransaction::create([
                                'user_id' => $vendor->id,
                                'amount' => $budget,
                                'type' => 'debit',
                                'description' => 'promotion_deduction',
                                'reference_id' => 'PROMO-APPROVE-' . $campaign->id . '-' . $vendor->id,
                                'status' => 'completed'
                            ]);
                        }
                    });

                    NotificationHelper::notifyVendor($vendor->id, [
                        'title' => 'Campaign Request Approved',
                        'message' => "Your request to join '{$campaign->name}' has been approved.",
                        'type' => 'promotions',
                        'url' => route('vendor.campaigns'),
                        'icon' => 'solar:check-circle-bold-duotone',
                        'priority' => 'high'
                    ]);
                    $successCount++;

                    // Check if limit reached during bulk approval
                    if ($campaign->max_vendors) {
                        $approvedCount = DB::table('campaign_vendors')
                            ->where('campaign_id', $campaign->id)
                            ->where('status', 'approved')
                            ->count();
                        if ($approvedCount >= $campaign->max_vendors) {
                            $this->auto_reject_pending_vendors($campaign);
                            break; // Stop approving more since limit reached and others are now rejected
                        }
                    }

                } else {
                    // Rejection logic
                    $pivot = DB::table('campaign_vendors')
                        ->where('campaign_id', $campaign->id)
                        ->where('vendor_id', $vendorId)
                        ->whereIn('status', ['pending', 'approved'])
                        ->first();
                    
                    if (!$pivot) continue;

                    $budget = (float) ($pivot->budget_total ?? 0);
                    $alreadyDeducted = WalletTransaction::where('user_id', $vendorId)
                        ->where('reference_id', 'CAMP-PENDING-' . $campaign->id . '-' . $vendorId)
                        ->where('type', 'debit')
                        ->exists();

                    DB::transaction(function () use ($campaign, $vendorId, $budget, $alreadyDeducted) {
                        DB::table('campaign_vendors')
                            ->where('campaign_id', $campaign->id)
                            ->where('vendor_id', $vendorId)
                            ->update([
                                'active' => false,
                                'status' => 'rejected',
                                'updated_at' => now()
                            ]);

                        // Remove vendor's products from the campaign
                 DB::table('campaign_products')
                     ->where('campaign_products.campaign_id', $campaign->id)
                     ->whereIn('campaign_products.product_id', function ($query) use ($vendorId) {
                         $query->select('campaign_products.product_id')->from('products')->where('vendor_id', $vendorId);
                     })
                     ->delete();

                        if ($alreadyDeducted && $budget > 0) {
                            $vendor = User::find($vendorId);
                            if ($vendor) {
                                $vendor->wallet_balance = ($vendor->wallet_balance ?? 0) + $budget;
                                $vendor->save();
                                WalletTransaction::create([
                                    'user_id' => $vendorId,
                                    'amount' => $budget,
                                    'type' => 'credit',
                                    'description' => 'campaign_rejection_refund',
                                    'reference_id' => 'CAMP-REJECT-' . $campaign->id . '-' . $vendorId,
                                    'status' => 'completed',
                                ]);
                            }
                        }
                    });

                    NotificationHelper::notifyVendor($vendorId, [
                        'title' => 'Campaign Request Rejected',
                        'message' => "Your request to join '{$campaign->name}' has been rejected.",
                        'type' => 'promotions',
                        'url' => route('vendor.campaigns'),
                        'icon' => 'solar:close-circle-bold-duotone',
                        'priority' => 'high'
                    ]);
                    $successCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing vendor ID $vendorId: " . $e->getMessage();
            }
        }

        $msg = "$successCount vendors " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully.";
        if (!empty($errors)) {
            return redirect()->back()->with('success', $msg)->withErrors($errors);
        }
        return redirect()->back()->with('success', $msg);
    }

    public function approve_vendor(Request $request, $campaignId, $vendorId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $pivot = DB::table('campaign_vendors')
            ->where('campaign_id', $campaign->id)
            ->where('vendor_id', $vendorId)
            ->first();
        if (!$pivot) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Request not found'], 404);
            }
            return redirect()->back()->withErrors(['error' => 'Request not found']);
        }
        $vendor = User::findOrFail($vendorId);
        $budget = (float) ($pivot->budget_total ?? 0);
        if ($budget <= 0) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Invalid budget amount'], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Invalid budget amount']);
        }

        // Check if the campaign has reached its actual approved vendor limit
        if ($campaign->max_vendors) {
            $approvedCount = DB::table('campaign_vendors')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'approved')
                ->count();
            if ($approvedCount >= $campaign->max_vendors) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['status' => false, 'message' => "Campaign has reached its maximum approved vendor limit ({$campaign->max_vendors})."], 400);
                }
                return redirect()->back()->withErrors(['error' => "Campaign has reached its maximum approved vendor limit ({$campaign->max_vendors})."]);
            }
        }

        // Admin-assigned vendors: wallet already deducted at campaign creation. Vendor self-joins: deduct on approve.
        $alreadyDeducted = WalletTransaction::where('user_id', $vendorId)
            ->where('reference_id', 'CAMP-PENDING-' . $campaign->id . '-' . $vendorId)
            ->where('type', 'debit')
            ->exists();

        if (!$alreadyDeducted && ($vendor->wallet_balance ?? 0) < $budget) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Insufficient vendor wallet balance'], 400);
            }
            return redirect()->back()->withErrors(['error' => 'Insufficient vendor wallet balance']);
        }

        DB::transaction(function () use ($campaign, $vendor, $budget, $alreadyDeducted) {
            DB::table('campaign_vendors')
                ->where('campaign_id', $campaign->id)
                ->where('vendor_id', $vendor->id)
                ->update([
                    'active' => true,
                    'status' => 'approved',
                    'budget_spent' => 0,
                    'updated_at' => now()
                ]);
            // Only deduct if vendor self-joined (admin-assigned was already deducted at creation)
            if (!$alreadyDeducted) {
                $vendor->wallet_balance = ($vendor->wallet_balance ?? 0) - $budget;
                $vendor->save();
                WalletTransaction::create([
                    'user_id' => $vendor->id,
                    'amount' => $budget,
                    'type' => 'debit',
                    'description' => 'promotion_deduction',
                    'reference_id' => 'PROMO-APPROVE-' . $campaign->id . '-' . $vendor->id,
                    'status' => 'completed'
                ]);
            }
        });

        // Notify Vendor
        NotificationHelper::notifyVendor($vendor->id, [
            'title' => 'Campaign Request Approved',
            'message' => "Your request to join '{$campaign->name}' has been approved.",
            'type' => 'promotions',
            'url' => route('vendor.campaigns'),
            'icon' => 'solar:check-circle-bold-duotone',
            'priority' => 'high'
        ]);

        // Auto-reject other pending vendors if limit reached
        if ($campaign->max_vendors) {
            $approvedCount = DB::table('campaign_vendors')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'approved')
                ->count();
            
            if ($approvedCount >= $campaign->max_vendors) {
                $this->auto_reject_pending_vendors($campaign);
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Vendor approved for campaign']);
        }
        return redirect()->back()->with('success', 'Vendor approved for campaign');
    }

    private function auto_reject_pending_vendors($campaign)
    {
        $pendingVendors = DB::table('campaign_vendors')
            ->where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingVendors as $pv) {
            $vendorId = $pv->vendor_id;
            $budget = (float) ($pv->budget_total ?? 0);
            
            $alreadyDeducted = WalletTransaction::where('user_id', $vendorId)
                ->where('reference_id', 'CAMP-PENDING-' . $campaign->id . '-' . $vendorId)
                ->where('type', 'debit')
                ->exists();

            DB::transaction(function () use ($campaign, $vendorId, $budget, $alreadyDeducted) {
                DB::table('campaign_vendors')
                    ->where('campaign_id', $campaign->id)
                    ->where('vendor_id', $vendorId)
                    ->update([
                        'active' => false,
                        'status' => 'rejected',
                        'updated_at' => now()
                    ]);

                // Remove vendor's products from the campaign
                DB::table('campaign_products')
                    ->where('campaign_id', $campaign->id)
                    ->whereIn('product_id', function ($query) use ($vendorId) {
                        $query->select('id')->from('products')->where('vendor_id', $vendorId);
                    })
                    ->delete();

                if ($alreadyDeducted && $budget > 0) {
                    $vendor = User::find($vendorId);
                    if ($vendor) {
                        $vendor->wallet_balance = ($vendor->wallet_balance ?? 0) + $budget;
                        $vendor->save();
                        WalletTransaction::create([
                            'user_id' => $vendorId,
                            'amount' => $budget,
                            'type' => 'credit',
                            'description' => 'campaign_rejection_refund',
                            'reference_id' => 'CAMP-REJECT-' . $campaign->id . '-' . $vendorId,
                            'status' => 'completed',
                        ]);
                    }
                }
            });

            // Notify Vendor
            NotificationHelper::notifyVendor($vendorId, [
                'title' => 'Campaign Slots Full',
                'message' => "Your request to join '{$campaign->name}' was rejected because all slots are now full.",
                'type' => 'promotions',
                'url' => route('vendor.campaigns'),
                'icon' => 'solar:info-circle-bold-duotone',
                'priority' => 'medium'
            ]);
        }
    }

    public function reject_vendor(Request $request, $campaignId, $vendorId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $pivot = DB::table('campaign_vendors')
            ->where('campaign_id', $campaign->id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$pivot) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Request not found or already processed'], 404);
            }
            return redirect()->back()->withErrors(['error' => 'Request not found or already processed']);
        }

        $budget = (float) ($pivot->budget_total ?? 0);
        $alreadyDeducted = WalletTransaction::where('user_id', $vendorId)
            ->where('reference_id', 'CAMP-PENDING-' . $campaign->id . '-' . $vendorId)
            ->where('type', 'debit')
            ->exists();

        DB::transaction(function () use ($campaign, $vendorId, $budget, $alreadyDeducted) {
            DB::table('campaign_vendors')
                ->where('campaign_id', $campaign->id)
                ->where('vendor_id', $vendorId)
                ->update([
                    'active' => false,
                    'status' => 'rejected',
                    'updated_at' => now()
                ]);

            // Remove vendor's products from the campaign
            DB::table('campaign_products')
                ->where('campaign_id', $campaign->id)
                ->whereIn('product_id', function ($query) use ($vendorId) {
                    $query->select('id')->from('products')->where('vendor_id', $vendorId);
                })
                ->delete();

            // Refund to vendor wallet if amount was deducted (admin-assigned at campaign creation)
            if ($alreadyDeducted && $budget > 0) {
                $vendor = User::find($vendorId);
                if ($vendor) {
                    $vendor->wallet_balance = ($vendor->wallet_balance ?? 0) + $budget;
                    $vendor->save();
                    WalletTransaction::create([
                        'user_id' => $vendorId,
                        'amount' => $budget,
                        'type' => 'credit',
                        'description' => 'campaign_rejection_refund',
                        'reference_id' => 'CAMP-REJECT-' . $campaign->id . '-' . $vendorId,
                        'status' => 'completed',
                    ]);
                }
            }
        });

        // Notify Vendor
        NotificationHelper::notifyVendor($vendorId, [
            'title' => 'Campaign Request Rejected',
            'message' => "Your request to join '{$campaign->name}' has been rejected.",
            'type' => 'promotions',
            'url' => route('vendor.campaigns'),
            'icon' => 'solar:close-circle-bold-duotone',
            'priority' => 'high'
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Vendor request rejected']);
        }
        return redirect()->back()->with('success', 'Vendor request rejected');
    }

    public function product_requests_page(Request $request, $campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $products = DB::table('campaign_products')
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->join('users', 'users.id', '=', 'products.vendor_id')
            ->select('products.id', 'products.name', 'users.name as vendor_name', 'campaign_products.status')
            ->where('campaign_products.campaign_id', $campaign->id)
            ->orderByRaw("CASE WHEN campaign_products.status = 0 THEN 0 WHEN campaign_products.status = 1 THEN 1 ELSE 2 END")
            ->orderBy('campaign_products.updated_at', 'desc')
            ->paginate(15)->withQueryString();
        return view('backend.admin.campaigns.product_requests', compact('campaign', 'products'));
    }

    public function approve_product(Request $request, $campaignId, $productId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $product = \App\Models\Product::findOrFail($productId);

        DB::table('campaign_products')
            ->where('campaign_id', $campaignId)
            ->where('product_id', $productId)
            ->update(['status' => 1, 'updated_at' => now()]);

        // Notify Vendor
        \App\Helpers\NotificationHelper::notifyVendor($product->vendor_id, [
            'title' => 'Campaign Product Approved',
            'message' => "Your product '{$product->name}' has been approved for the campaign: {$campaign->name}.",
            'type' => 'promotions',
            'url' => route('vendor.campaign.manage.products', $campaign->id),
            'icon' => 'solar:check-circle-linear',
            'priority' => 'medium'
        ]);

        return redirect()->back()->with('success', 'Product approved.');
    }

    public function reject_product(Request $request, $campaignId, $productId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $product = \App\Models\Product::findOrFail($productId);

        DB::table('campaign_products')
            ->where('campaign_id', $campaignId)
            ->where('product_id', $productId)
            ->delete();

        // Notify Vendor
        \App\Helpers\NotificationHelper::notifyVendor($product->vendor_id, [
            'title' => 'Campaign Product Rejected',
            'message' => "Your product '{$product->name}' has been rejected and removed from the campaign: {$campaign->name}.",
            'type' => 'promotions',
            'url' => route('vendor.campaign.manage.products', $campaign->id),
            'icon' => 'solar:close-circle-linear',
            'priority' => 'medium'
        ]);

        return redirect()->back()->with('success', 'Product rejected and removed from campaign.');
    }

    public function delete_product_request(Request $request, $campaignId, $productId)
    {
        $campaign = Campaign::findOrFail($campaignId);

        $deleted = DB::table('campaign_products')
            ->where('campaign_id', $campaignId)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return redirect()->back()->with('error', 'Product request not found.');
        }

        return redirect()->back()->with('success', "Product removed from '{$campaign->name}'.");
    }

    public function bulk_action_products(Request $request, $campaignId)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:approve,reject'
        ]);

        $campaign = Campaign::findOrFail($campaignId);
        $status = $request->action === 'approve' ? 1 : 2;
        $statusText = $status == 1 ? 'approved' : 'rejected';

        if ($status == 1) {
            DB::table('campaign_products')
                ->where('campaign_id', $campaignId)
                ->whereIn('product_id', $request->product_ids)
                ->update(['status' => 1, 'updated_at' => now()]);
        } else {
            DB::table('campaign_products')
                ->where('campaign_id', $campaignId)
                ->whereIn('product_id', $request->product_ids)
                ->delete();
        }

        // Group products by vendor to send one notification per vendor
        $products = \App\Models\Product::whereIn('id', $request->product_ids)->get()->groupBy('vendor_id');

        foreach ($products as $vendorId => $vendorProducts) {
            $count = $vendorProducts->count();
            $productNames = $vendorProducts->take(2)->pluck('name')->implode(', ');
            if ($count > 2) $productNames .= " and " . ($count - 2) . " others";

            \App\Helpers\NotificationHelper::notifyVendor($vendorId, [
                'title' => "Campaign Products " . ucfirst($statusText),
                'message' => "{$count} of your products ({$productNames}) have been {$statusText} for campaign: {$campaign->name}.",
                'type' => 'promotions',
                'url' => route('vendor.campaign.manage.products', $campaign->id),
                'icon' => $status == 1 ? 'solar:check-circle-linear' : 'solar:close-circle-linear',
                'priority' => 'medium'
            ]);
        }

        return redirect()->back()->with('success', 'Selected products have been ' . $statusText . '.');
    }
}
