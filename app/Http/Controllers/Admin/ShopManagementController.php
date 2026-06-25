<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\ShopRenewal;
use Carbon\Carbon;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RenewalStatusUpdatedNotification;

class ShopManagementController extends Controller
{
    /**
     * Display a listing of shops.
     */
    public function index(Request $request)
    {
        $query = Shop::with('creator');

        if ($request->has('status') && $request->status != 'all') {
            $query->where('license_status', $request->status);
        }

        $shops = $query->paginate(10);
        $shops->each(function ($shop) {
            $shop->license_expiry_date = $shop->license_expiry_date ? $shop->license_expiry_date->toDateString() : null;
        });

        foreach ($shops as $shop) {

           $shop->image =  ImageHelper::getShopImage($shop->image);
          
        }

       

        return view('backend.admin.shops.index', compact('shops'));
    }

    /**
     * Display a listing of renewal requests.
     */
    public function renewals(Request $request)
    {
        $query = ShopRenewal::with(['shop', 'agent']);

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $renewals = $query->paginate(10);
        foreach ($renewals as $renewal) {
            $renewal->selfie =  ImageHelper::getRenewalImage($renewal->selfie);
        }

        return view('backend.admin.renewals.index', compact('renewals'));
    }

    /**
     * Approve a renewal request.
     */
    public function approveRenewal(Request $request, $id)
    {
        $renewal = ShopRenewal::findOrFail($id);
        
        if ($renewal->status !== 'pending') {
            return redirect()->back()->with('error', 'Renewal request already processed.');
        }

        $renewal->status = 'approved';
        $renewal->save();

        $shop = $renewal->shop;
       
        // Update shop license
        $new_start_date = $shop->license_expiry_date && $shop->license_expiry_date->isFuture() 
            ? $shop->license_expiry_date 
            : Carbon::today();
            
        $shop->license_start_date = $new_start_date;
        $shop->license_duration_days = (int) $renewal->requested_duration_days;
        $shop->license_expiry_date = $new_start_date->copy()->addDays((int) $renewal->requested_duration_days);
        $shop->license_status = 'active';
        $shop->save();

        // Notify Agent
        if ($renewal->agent) {
            $renewal->agent->notify(new RenewalStatusUpdatedNotification($renewal));
        }

        return redirect()->back()->with('success', 'Renewal request approved and shop license updated.');
    }

    /**
     * Reject a renewal request.
     */
    public function rejectRenewal(Request $request, $id)
    {
        $renewal = ShopRenewal::findOrFail($id);
        
        if ($renewal->status !== 'pending') {
            return redirect()->back()->with('error', 'Renewal request already processed.');
        }

        $renewal->status = 'rejected';
        $renewal->save();

        // Notify Agent
        if ($renewal->agent) {
            $renewal->agent->notify(new RenewalStatusUpdatedNotification($renewal));
        }

        return redirect()->back()->with('success', 'Renewal request rejected.');
    }
}
