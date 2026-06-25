<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;    
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Category;

use App\Models\ProductSize;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Hash;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Helpers\PriceHelper;
use App\Models\VendorPayout;
use App\Models\VendorsDocument;
use App\Models\KYC_Document;
use App\Helpers\NotificationHelper;
use App\Helpers\EmailHelper;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductVariantItem;
use App\Models\ProductReview;
use Illuminate\Support\Facades\DB;
use App\Models\WalletTransaction;



class MyVendorsController extends Controller
{
    public function add_vendor(Request $request)
    {
        $countries = Country::where('is_active',1)->get()   ;
        $states = State::where('is_active',1)->get()   ;
        $cities = City::where('is_active',1)->get()   ;
        $categories = Category::where('is_active', 1)->get();
     
        return view('backend/admin/vendor/add-vendor', compact('countries','states','cities', 'categories'));
    }


    public function store_vendor(Request $request)
    {
        $rules = [
            'owner_name'      => 'required|min:3',
            'store_name'      => 'required',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'required|digits:10',
            'password'        => 'required|min:6|confirmed',
            'address'         => 'required',
            // 'city_id'            => 'required',
            'state_id'           => 'required',
            'country_id'         => 'required',
            'zip'             => 'required',
            'business_name'   => 'required',
            'pan_no'          => 'nullable|string|max:100',
            'vendor_tax'      => 'nullable',
            'bank_name'       => 'nullable|string|max:255',
            'account_holder_name'       => 'nullable|string|max:255',
            'account_number'  => 'nullable|string|max:100',
            'branch_location' => 'required|string|max:255',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_ids'    => 'required|array|min:1',
        ];

        $request->validate($rules);

        /* 🔹 Generate UQID */
        $storeSlug = Str::slug($request->store_name, '');
        $storeCode = Str::upper(Str::substr($storeSlug, 0, 5));
        $uqid = $storeCode . '-' . rand(1000, 9999);

        /* 🔹 Upload Image */
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = ImageHelper::compressImage($request->image, 'uploads/vendors');
        }

        /* 🔹 Insert Vendor */
        $data = [
            'name'            => $request->owner_name,
            'store_name'      => $request->store_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'password'        => Hash::make($request->password), // 🔐 hashed
            'address'         => $request->address,
            'city_id'            => $request->city_id??'',
            'state_id'           => $request->state_id,
            'country_id'         => $request->country_id,
            'zip'             => $request->zip,
            'business_name'   => $request->business_name,
            'pan_no'          => $request->pan_no,
            'vendor_tax'      => $request->vendor_tax ?? 0,
            'bank_name'       => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number'  => $request->account_number,
            'branch_location' => $request->branch_location,
            'role'            => '2',
            'uqid'            => $uqid,
            'image'           => $imageName,
            'category_ids'    => $request->category_ids,
            'allowed_payout_frequencies' => $request->allowed_payout_frequencies ?? ['weekly', 'monthly', 'bi-weekly', 'daily'],
        ];

        $user = User::create($data);

        if ($user) {
            $id = $user->id;
            
            // Send Email Notification
            $admin_email = \App\Models\EmailSetting::where('status', 1)->value('mail_from_address') ?? 'admin@ecom.com';
            
            // Notify Admin using common template
            EmailHelper::send(
                $admin_email, 
                'New Seller Registration: ' . $user->store_name, 
                'A new seller has been added by the administrator.<br><br><b>Shop Name:</b> ' . $user->store_name . '<br><b>Email:</b> ' . $user->email,
                'emails.common',
                [
                    'action_url' => url('/admin/vendors-list'),
                    'action_text' => 'View Vendors'
                ]
            );

            // Notify Seller using registration template
            $appUrl = config('app.url');
            EmailHelper::send(
                $user->email,
                'Welcome to ' . config('app.name') . '! You have joined as a vendor',
                '', // Message body handled by template
                'emails.registration',
                [
                    'owner_name' => $user->name,
                    'store_name' => $user->store_name,
                    'login_url'  => $appUrl . '/login',
                    'email'      => $user->email,
                    'password'   => $request->password
                ]
            );

            if ($request->ajax()) {
                return response()->json(['status' => true, 'message' => 'Vendor added successfully']);
            }
         
            return redirect('vendors-list')
                ->with('success', 'Vendor added successfully');
        }

        if ($request->ajax()) {
            return response()->json(['status' => false, 'message' => 'Vendor added failed']);
        }

        return redirect()->back()
            ->with('error', 'Vendor added failed');
    }


    public function vendors_list(Request $request)
    {
        $total_vendors = User::where('role', '2')->count();
        $active_vendors = User::where('role', '2')->where('status', 1)->count();
        $pending_vendors = User::where('role', '2')->whereIn('status', [0, 4])->count();
        $rejected_vendors = User::where('role', '2')->where('status', 2)->count();
        $blocked_vendors = User::where('role', '2')->where('status', 3)->count();

        // Calculate vendor growth
        $last_month_vendors = User::where('role', '2')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $vendor_growth = 0;
        if ($last_month_vendors > 0) {
            $vendor_growth = number_format((($total_vendors - $last_month_vendors) / $last_month_vendors) * 100, 1);
        } else if ($total_vendors > 0) {
            $vendor_growth = 100;
        }

        $countries = Country::where('is_active', 1)->get();
        $vendors = User::where('role', '2')->get();

        // Base query for all vendors
        $base_query = User::select('users.*', 'users.image as logo')->where('role', '=', '2')
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            ->withCount('products')
            ->orderBy('users.id', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $base_query->where(function($q) use ($searchTerm) {
                $q->where('users.name', 'like', '%'.$searchTerm.'%')
                  ->orWhere('users.email', 'like', '%'.$searchTerm.'%')
                  ->orWhere('users.store_name', 'like', '%'.$searchTerm.'%')
                  ->orWhere('users.phone', 'like', '%'.$searchTerm.'%');
            });
        }

        if ($request->filled('country_id')) {
            $base_query->where('users.country_id', $request->country_id);
        }

        if ($request->filled('vendor_id')) {
            $base_query->where('users.id', $request->vendor_id);
        }

        if ($request->filled('store_name')) {
            $base_query->where('users.store_name', 'like', '%' . $request->store_name . '%');
        }


        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $base_query->whereDate('users.created_at', '>=', $dates[0])
                           ->whereDate('users.created_at', '<=', $dates[1]);
            } else {
                $base_query->whereDate('users.created_at', $dates[0]);
            }
        }

        // Fetch All Vendors (paginated)
        $all_vendors = clone $base_query;
        $all_vendors_data = $all_vendors->paginate(15, ['*'], 'all_page')
            ->withQueryString();
        foreach ($all_vendors_data as $v) $v->logo = ImageHelper::getVendorsImage($v->logo);

        // Fetch Pending Vendors (status 0 or 4) - paginated
        $pending_vendors_query = clone $base_query;
        $pending_vendors_data = $pending_vendors_query->whereIn('users.status', [0, 4])
            ->paginate(15, ['*'], 'pending_page')
            ->withQueryString();
        foreach ($pending_vendors_data as $v) $v->logo = ImageHelper::getVendorsImage($v->logo);

        // Fetch Active Vendors (status 1) - paginated
        $active_vendors_query = clone $base_query;
        $active_vendors_data = $active_vendors_query->where('users.status', 1)
            ->paginate(15, ['*'], 'active_page')
            ->withQueryString();
        foreach ($active_vendors_data as $v) $v->logo = ImageHelper::getVendorsImage($v->logo);

        // Fetch Rejected Vendors (status 2) - paginated
        $rejected_vendors_query = clone $base_query;
        $rejected_vendors_data = $rejected_vendors_query->where('users.status', 2)
            ->paginate(15, ['*'], 'rejected_page')
            ->withQueryString();
        foreach ($rejected_vendors_data as $v) $v->logo = ImageHelper::getVendorsImage($v->logo);

        // Fetch Blocked Vendors (status 3) - paginated
        $blocked_vendors_query = clone $base_query;
        $blocked_vendors_data = $blocked_vendors_query->where('users.status', 3)
            ->paginate(15, ['*'], 'blocked_page')
            ->withQueryString();
        foreach ($blocked_vendors_data as $v) $v->logo = ImageHelper::getVendorsImage($v->logo);

        $countries = Country::where('is_active', 1)->get();

        // Calculate total sale for selected vendor
          $selected_vendor_sale = 0;
          $selected_vendor_currency = '';
          if ($request->filled('vendor_id')) {
              $selected_vendor_sale = OrderItem::where('vendor_id', $request->vendor_id)
                  ->where('payment_status', '1') // Assuming 1 means paid
                  ->sum('total_actual_price');
              
              $vendor = User::find($request->vendor_id);
              if ($vendor && $vendor->country_id) {
                  $country = Country::find($vendor->country_id);
                  // Based on previous code, we might need to check how currency is stored
                  // If it's in countries table:
                  $selected_vendor_currency = DB::table('countries')->where('id', $vendor->country_id)->value('currency') ?? 'USD';
              }
          }

          // Calculate total sale for selected country
          $selected_country_sale = 0;
          $selected_country_currency = '';
          $selected_country_name = '';
          if ($request->filled('country_id')) {
              $selected_country_sale = OrderItem::join('users as vendors', 'order_items.vendor_id', '=', 'vendors.id')
                  ->where('vendors.country_id', $request->country_id)
                  ->where('order_items.payment_status', '1')
                  ->sum('order_items.total_actual_price');
              
              $country = Country::find($request->country_id);
              if ($country) {
                  $selected_country_currency = $country->currency_code ?? 'USD';
                  $selected_country_name = $country->name;
              }
          }

          // Calculate country-wise sales
         $country_sales = OrderItem::join('users as vendors', 'order_items.vendor_id', '=', 'vendors.id')
             ->join('countries', 'vendors.country_id', '=', 'countries.id')
             ->where('order_items.payment_status', '1')
             ->select(
                 'countries.name as country_name',
                 'countries.currency as currency',
                 DB::raw('SUM(order_items.total_actual_price) as total_sale')
             )
             ->groupBy('countries.id', 'countries.name', 'countries.currency')
             ->get();
 
         $common_commission = GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0;
 
         if ($request->ajax()) {
             return view('backend/admin/vendor/partials/vendor-tabs', compact(
                 'all_vendors_data',
                 'pending_vendors_data',
                 'active_vendors_data',
                 'rejected_vendors_data',
                 'blocked_vendors_data',
                 'common_commission'
             ))->render();
         }
 
         return view('backend/admin/vendor/vendors-list', compact(
             'all_vendors_data',
             'pending_vendors_data',
             'active_vendors_data',
             'rejected_vendors_data',
             'blocked_vendors_data',
             'total_vendors',
             'active_vendors',
             'pending_vendors',
             'rejected_vendors',
             'blocked_vendors',
             'countries',
             'vendors',
              'selected_vendor_sale',
              'selected_vendor_currency',
              'selected_country_sale',
              'selected_country_currency',
              'selected_country_name',
              'country_sales',
              'vendor_growth',
              'common_commission'
          ));
    }

    public function delete_multiple(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) {
            return response()->json(['status' => false, 'message' => 'No vendors selected.']);
        }

        try {
            DB::transaction(function () use ($ids) {
                // Delete related records to avoid integrity constraint violations
                
                // 1. Delete from carts (where user_id is the vendor or items in cart are from vendor)
                DB::table('carts')->whereIn('user_id', $ids)->delete();
                
                // 2. Delete vendor documents
                DB::table('vendors_document')->whereIn('vendor_id', $ids)->delete();
                
                // 3. Delete products and related (variants, images, etc.)
                $productIds = DB::table('products')->whereIn('vendor_id', $ids)->pluck('id');
                if ($productIds->count() > 0) {
                    DB::table('product_variants')->whereIn('product_id', $productIds)->delete();
                    DB::table('product_images')->whereIn('product_id', $productIds)->delete();
                    DB::table('product_reviews')->whereIn('product_id', $productIds)->delete();
                    DB::table('products')->whereIn('vendor_id', $ids)->delete();
                }

                // 4. Delete wallet transactions
                DB::table('wallet_transactions')->whereIn('user_id', $ids)->delete();

                // 5. Delete tickets
                DB::table('tickets')->whereIn('user_id', $ids)->delete();

                // 6. Delete shipping addresses
                DB::table('shipping_addresses')->whereIn('user_id', $ids)->delete();

                // 7. Finally delete the users
                User::whereIn('id', $ids)->delete();
            });

            return response()->json(['status' => true, 'message' => 'Selected vendors and their related data deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting vendors: ' . $e->getMessage()]);
        }
    }

    public function export_multiple(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No vendors selected.');
        }

        $vendors = User::whereIn('id', $ids)->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=vendors_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('SNO', 'Store Name', 'Vendor Name', 'Email', 'Phone', 'Status', 'Registered At');

        $callback = function() use ($vendors, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($vendors as $key => $vendor) {
                $status = 'Pending';
                if($vendor->status == 1) $status = 'Approved';
                elseif($vendor->status == 2) $status = 'Rejected';
                elseif($vendor->status == 3) $status = 'Blocked';

                fputcsv($file, array(
                    $key + 1,
                    $vendor->store_name,
                    $vendor->name,
                    $vendor->email,
                    $vendor->phone,
                    $status,
                    $vendor->created_at->format('Y-m-d H:i:s')
                ));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function vendor_edit(Request $request, $uqid)
    {
        $vendor = User::where('users.uqid', $uqid)
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('users.*','users.image as logo', 'countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            ->first();
            
     $vendor->logo = ImageHelper::getVendorsImage($vendor->logo);

        //  echo '<pre>';print_r($vendor);die;
        $countries = Country::where('is_active', 1)->get();
        $states = State::where('is_active', 1)->get();
        $cities = City::where('is_active', 1)->get();
        $categories = Category::where('is_active', 1)->get();

        return view('backend/admin/vendor/edit-vendor', compact('vendor', 'countries', 'states', 'cities', 'categories'));
    }


    public function vendor_update(Request $request)
    {
        $vendor = User::findOrFail($request->vender_uqid);
        $rules = [
            'owner_name'     => 'required|min:3',
            'store_name'     => 'required',
            'email'          => 'required|email|unique:users,email,' . $vendor->id,
            'phone'          => 'required|digits:10',
            'password'       => 'nullable|min:6|confirmed',
            'address'        => 'required',
            'country_id'        => 'required',
            'city_id'           => 'required',
            'state_id'          => 'required',
            'zip'            => 'required',
            'business_name'  => 'required',
            'pan_no'         => 'required|string|max:100',
            'vendor_tax'     => 'nullable',
            'bank_name'      => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'branch_location' => 'required|string|max:255',
            'payout_frequency' => 'nullable|in:weekly,monthly,daily,bi-weekly',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_ids'   => 'required|array|min:1',
        ];

     

        $request->validate($rules);

        /* 🔹 IMAGE HANDLING */
        $imageName = $vendor->image; // default → old image

        if ($request->hasFile('image')) {
            $imageName = ImageHelper::compressImage($request->image, 'uploads/vendors');
        }

        /* 🔹 Update Vendor */
        $updateData = [
            'name'            => $request->owner_name,
            'store_name'      => $request->store_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'address'         => $request->address,
            'city_id'         => $request->city_id,
            'state_id'        => $request->state_id,
            'country_id'      => $request->country_id,
            'zip'             => $request->zip,
            'business_name'   => $request->business_name,
            'pan_no'          => $request->pan_no,
            'vendor_tax'      => $request->vendor_tax ?? 0,
            'bank_name'       => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number'  => $request->account_number,
            'image'           => $imageName,
            'category_ids'    => $request->category_ids,
            'branch_location' => $request->branch_location,
            'payout_frequency' => $request->payout_frequency,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $vendor->update($updateData);

        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Vendor updated successfully']);
        }

        return redirect()->route('vendors.list')->with('success', 'Vendor updated successfully');
    }


    public function delete_vendor(Request $request)
    {
        $vendor = User::where('id', $request->id)
            ->where('role', '2') // vendor role
            ->first();

        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found'
            ]);
        }

        try {
            DB::transaction(function () use ($vendor) {
                $id = $vendor->id;

                // 1. Delete from carts
                DB::table('carts')->where('user_id', $id)->delete();

                // 2. Delete vendor documents
                DB::table('vendors_document')->where('vendor_id', $id)->delete();

                // 3. Delete products and related data
                $productIds = DB::table('products')->where('vendor_id', $id)->pluck('id');
                if ($productIds->isNotEmpty()) {
                    DB::table('product_variants')->whereIn('product_id', $productIds)->delete();
                    DB::table('product_images')->whereIn('product_id', $productIds)->delete();
                    DB::table('product_reviews')->whereIn('product_id', $productIds)->delete();
                    DB::table('products')->where('vendor_id', $id)->delete();
                }

                // 4. Delete wallet transactions
                DB::table('wallet_transactions')->where('user_id', $id)->delete();

                // 5. Delete tickets
                DB::table('tickets')->where('user_id', $id)->delete();

                // 6. Delete shipping addresses
                DB::table('shipping_addresses')->where('user_id', $id)->delete();

                // 7. Finally delete the user
                $vendor->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Vendor and all related records deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting vendor: ' . $e->getMessage()
            ]);
        }
    }

    public function change_vendor_status(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:users,id',
            'status' => 'required|in:0,1,2,3,4',
            'rejection_reason' => 'required_if:status,2|required_if:status,3'
        ]);

        $vendor = User::where('id', $request->id)
            ->where('role', '2')
            ->first();

        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found'
            ]);
        }

        // Check if approving and documents are missing (unless forced)
        if ($request->status == 1 && !$request->force) {
            $docs = VendorsDocument::where('vendor_id', $request->id)->get();
            $approved_docs = $docs->where('is_verify', 1)->count();

            if ($docs->count() == 0) {
                return response()->json([
                    'status' => 'confirm',
                    'message' => 'No documents uploaded. Are you sure you want to approve this vendor?'
                ]);
            } elseif ($approved_docs < $docs->count()) {
                return response()->json([
                    'status' => 'confirm',
                    'message' => 'Not all documents are approved. Are you sure you want to approve this vendor?'
                ]);
            }
        }

        $vendor->status = $request->status;
        if ($request->status == 2 || $request->status == 3) {
            $vendor->rejection_reason = $request->rejection_reason;
        } else {
            $vendor->rejection_reason = null;
        }
        $vendor->save();

        // Notify Vendor
        $statusText = 'Pending';
        if ($request->status == 1) $statusText = 'Approved';
        elseif ($request->status == 2) $statusText = 'Rejected';
        elseif ($request->status == 3) $statusText = 'Blocked';
        elseif ($request->status == 4) $statusText = 'Pending';

        $priority = 'low';
        if ($request->status == 1) $priority = 'medium';
        elseif ($request->status == 2 || $request->status == 3) $priority = 'critical';

        $message = "Your vendor account has been " . strtolower($statusText) . ".";
        if ($request->status == 2 || $request->status == 3) {
            $message .= " Reason: " . $request->rejection_reason;
        }

        NotificationHelper::notifyVendor($vendor->id, [
            'title' => 'Account Status Update',
            'message' => $message,
            'type' => 'system',
            'url' => route('vendor.profile'),
            'icon' => ($request->status == 1 || $request->status == 4) ? 'solar:check-circle-bold-duotone' : 'solar:close-circle-bold-duotone',
            'priority' => $priority
        ]);

        // Send Email Notification
        EmailHelper::send(
            $vendor->email,
            'Account Status Update - ' . $statusText,
            'Your vendor account status has been updated to: <b>' . $statusText . '</b>.' . ($request->status == 2 || $request->status == 3 ? '<br>Reason: ' . $request->rejection_reason : '')
        );

        return response()->json([
            'status' => true,
            'message' => 'Vendor status updated successfully'
        ]);
    }

    public function toggle_verified(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'is_verified' => 'required|in:0,1'
        ]);

        $vendor = User::where('id', $request->id)
            ->where('role', '2')
            ->first();

        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found'
            ]);
        }

        $vendor->is_verified = $request->is_verified;
        $vendor->save();

        return response()->json([
            'status' => true,
            'message' => $request->is_verified ? 'Vendor verified successfully' : 'Vendor verification removed'
        ]);
    }

    public function change_document_status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:vendors_document,id',
            'status' => 'required|in:0,1,2',
            'rejection_reason' => 'required_if:status,2'
        ]);

        $doc = VendorsDocument::find($request->id);
        $doc->is_verify = $request->status;
        if ($request->status == 2) {
            $doc->rejection_reason = $request->rejection_reason;
        } else {
            $doc->rejection_reason = null;
        }
        $doc->save();

        // Notify Vendor
        $vendor = User::find($doc->vendor_id);
        if ($vendor) {
            $statusText = $request->status == 1 ? 'Approved' : ($request->status == 2 ? 'Rejected' : 'Pending');
            $docType = $doc->documentType->name ?? 'Document';
            
            $message = "Your document (<b>" . $docType . "</b>) has been " . strtolower($statusText) . ".";
            if ($request->status == 2) {
                $message .= " Reason: " . $request->rejection_reason;
            }

            // In-app Notification
            NotificationHelper::notifyVendor($vendor->id, [
                'title' => 'Document Verification Update',
                'message' => $message,
                'type' => 'system',
                'url' => route('vendor.profile'),
                'icon' => $request->status == 1 ? 'solar:check-circle-bold-duotone' : 'solar:close-circle-bold-duotone',
                'priority' => $request->status == 1 ? 'medium' : ($request->status == 2 ? 'critical' : 'low')
            ]);

            // Email Notification
            EmailHelper::send(
                $vendor->email,
                'Document Verification Update - ' . $statusText,
                'Your document (<b>' . $docType . '</b>) verification status has been updated to: <b>' . $statusText . '</b>.' . ($request->status == 2 ? '<br>Reason: ' . $request->rejection_reason : '')
            );

            // AUTO APPROVE VENDOR if all documents are approved
            if ($request->status == 1) {
                $totalDocs = VendorsDocument::where('vendor_id', $vendor->id)->count();
                $approvedDocs = VendorsDocument::where('vendor_id', $vendor->id)->where('is_verify', 1)->count();

                if ($totalDocs > 0 && $totalDocs == $approvedDocs) {
                    $vendor->status = 1; // 1 = Approved
                    $vendor->save();

                    // Notify about vendor approval
                    NotificationHelper::notifyVendor($vendor->id, [
                        'title' => 'Account Approved',
                        'message' => "Your vendor account has been auto-approved as all your documents are verified.",
                        'type' => 'system',
                        'url' => route('vendor.profile'),
                        'icon' => 'solar:check-circle-bold-duotone',
                        'priority' => 'medium'
                    ]);

                    EmailHelper::send(
                        $vendor->email,
                        'Account Approved',
                        'Your vendor account has been auto-approved as all your documents are verified.'
                    );
                }
            }


        }

        return response()->json([
            'status' => true,
            'message' => 'Document status updated successfully',
            'vendor_status' => isset($vendor) ? (int)$vendor->status : null
        ]);
    }

    public function check_email_availability(Request $request)
    {
        $email = $request->email;
        $exclude_id = $request->exclude_id;
        $query = User::where('email', $email);
        if ($exclude_id) {
            $query->where('id', '!=', $exclude_id);
        }
        $exists = $query->exists();
        return response()->json([
            'status' => true,
            'available' => !$exists,
            'message' => $exists ? 'Email already registered.' : 'Email available.'
        ]);
    }

    public function active_vendors()
    {
        $vendor_data = User::select('users.*', 'users.image as logo')->where('role', '=', '2')->where('status', 1)
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            ->orderBy('users.id', 'desc')->get();
        foreach ($vendor_data as $key => $value) {
            $value->logo = ImageHelper::getVendorsImage($value->logo);
        }
        return view('backend/admin/vendor/active-vendors', compact('vendor_data'));
    }

    public function vendor_requests()
    {
        $vendor_data = User::select('users.*', 'users.image as logo')->where('users.role', '=', '2')
        ->where('users.status', '0')
        ->where('users.from_web', '1')
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')

            ->orderBy('users.id', 'desc')->get();
            foreach ($vendor_data as $key => $value) {
                $value->logo = ImageHelper::getVendorsImage($value->logo);
                
            }
            // echo '<pre>';print_r($vendor_data);die;
        return view('backend/admin/vendor/vendor-requests', compact('vendor_data'));
    }

    public function reject_vendor()
    {
         $vendor_data = User::select('users.*', 'users.image as logo')->where('role', '=', '2')
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            
            ->where('status', 2)->orderBy('id', 'desc')->get();
        foreach ($vendor_data as $key => $value) {
            $value->logo = ImageHelper::getVendorsImage($value->logo);
        }
        return view('backend/admin/vendor/rejected-vendor', compact('vendor_data'));
    }

    
     public function pending_vendors()
    {
         $vendor_data = User::select('users.*', 'users.image as logo')->where('role', '=', '2')
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            
            ->where('status', 0)->orderBy('id', 'desc')->get();
        foreach ($vendor_data as $key => $value) {
            $value->logo = ImageHelper::getVendorsImage($value->logo);
        }
        return view('backend/admin/vendor/rejected-vendor', compact('vendor_data'));
    }

     public function approved_vendors()
    {
         $vendor_data = User::select('users.*', 'users.image as logo')->where('role', '=', '2')
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            
            ->where('status', 0)->orderBy('id', 'desc')->get();
        foreach ($vendor_data as $key => $value) {
            $value->logo = ImageHelper::getVendorsImage($value->logo);
        }
        return view('backend/admin/vendor/approved-vendors', compact('vendor_data'));
    }

    public function vendor_product()
    {
        return view('backend/admin/vendor/vendor-product');
    }

    public function markPayoutAsPaid(Request $request, $id)
    {
        $payout = VendorPayout::with('vendor')->findOrFail($id);

        if ($payout->status === 'unpaid') {
            DB::beginTransaction();
            try {
                // Update payout status
                $payout->status = 'paid';
                $payout->paid_at = now();
                $payout->save();

                // Add payout amount to vendor's wallet balance
                $vendor = $payout->vendor;
                if ($vendor) {
                    $vendor->wallet_balance += $payout->payout_amount;
                    $vendor->save();

                    // Record wallet transaction
                    WalletTransaction::create([
                        'user_id' => $vendor->id,
                        'amount' => $payout->payout_amount,
                        'type' => 'credit',
                        'description' => 'Payout #' . $payout->id . ' marked as paid',
                        'reference_id' => $payout->id,
                        'reference_type' => 'vendor_payout',
                    ]);
                }

                DB::commit();

                // Recalculate stats for the dashboard
                $frequency = $request->get('frequency', 'daily');
                $total_payout_amount = VendorPayout::where('payout_frequency', $frequency)->where('status', 'paid')->sum('payout_amount');
                $pending_payouts = VendorPayout::where('payout_frequency', $frequency)->where('status', 'unpaid')->count();
                $total_unpaid_amount = VendorPayout::where('payout_frequency', $frequency)->where('status', 'unpaid')->sum('payout_amount');
                $total_commission = VendorPayout::where('payout_frequency', $frequency)->sum('commission_amount');
                $total_wallet_balance = User::where('role', '2')->sum('wallet_balance');

                return response()->json([
                    'status' => true,
                    'message' => 'Payout marked as paid and vendor wallet updated successfully.',
                    'stats' => [
                        'total_payout_amount' => number_format($total_payout_amount, 2),
                        'pending_payouts' => $pending_payouts,
                        'total_unpaid_amount' => number_format($total_unpaid_amount, 2),
                        'total_commission' => number_format($total_commission, 2),
                        'total_wallet_balance' => number_format($total_wallet_balance, 2)
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Failed to process payment: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => false, 'message' => 'Payout is not in unpaid status.'], 400);
    }

    public function vendor_payout(Request $request)
    {        $frequency = $request->get('frequency', 'daily'); // weekly, monthly, daily, bi-weekly
        if (!in_array($frequency, ['weekly', 'monthly', 'daily', 'bi-weekly'])) {
            $frequency = 'daily';
        }

        $query = VendorPayout::with(['vendor.country', 'order'])
            ->where(function ($q) use ($frequency) {
                if ($frequency === 'monthly') {
                    $q->where('vendor_payouts.payout_frequency', 'monthly')
                      ->orWhereNull('vendor_payouts.payout_frequency'); // default to monthly if not set
                } else {
                    $q->where('vendor_payouts.payout_frequency', $frequency);
                }
            })
            ->select('vendor_payouts.*')
            ->addSelect(DB::raw('(SELECT SUM(quantity) FROM order_items WHERE order_items.order_id = vendor_payouts.order_id AND order_items.vendor_id = vendor_payouts.vendor_id) as items_qty'))
            ->orderBy('vendor_payouts.id', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->join('users', 'vendor_payouts.vendor_id', '=', 'users.id')
                  ->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.store_name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT('VP-', LPAD(vendor_payouts.id, 4, '0')) LIKE ?", ["%{$search}%"]);
            })
            ->select('vendor_payouts.*'); // Re-select to prioritize payout columns over user columns
        }

        // Date range filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) >= 1) {
                $query->whereDate('vendor_payouts.created_at', '>=', trim($dates[0]));
                if (count($dates) >= 2) {
                    $query->whereDate('vendor_payouts.created_at', '<=', trim($dates[1]));
                }
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $statusValue = $request->status;
            // Map legacy numeric status if necessary, but handle 'paid'/'unpaid' directly
            $statusMap = ['0' => 'unpaid', '1' => 'paid', '2' => 'failed', '3' => 'approved'];
            $status = $statusMap[$statusValue] ?? $statusValue;
            $query->where('vendor_payouts.status', $status);
        }

        $payouts = $query->paginate(15)->withQueryString();

        // Stats calculation
        $statsQuery = VendorPayout::where(function ($q) use ($frequency) {
            if ($frequency === 'monthly') {
                $q->where('payout_frequency', 'monthly')->orWhereNull('payout_frequency');
            } else {
                $q->where('payout_frequency', $frequency);
            }
        });

        $total_payout_amount = (clone $statsQuery)->where('status', 'paid')->sum('payout_amount');
        $pending_payouts = (clone $statsQuery)->where('status', 'unpaid')->count();
        $total_unpaid_amount = (clone $statsQuery)->where('status', 'unpaid')->sum('payout_amount');
        $total_commission = (clone $statsQuery)->sum('commission_amount');
        $total_wallet_balance = User::where('role', '2')->sum('wallet_balance');

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'table' => view('backend.admin.vendor.partials.vendor-payout-table', compact('payouts', 'frequency'))->render(),
                'pagination' => (string) $payouts->links('pagination::bootstrap-5'),
                'info' => 'Showing ' . ($payouts->firstItem() ?? 0) . ' to ' . ($payouts->lastItem() ?? 0) . ' of ' . $payouts->total() . ' entries',
                'stats' => [
                    'total_payout_amount' => number_format($total_payout_amount, 2),
                    'pending_payouts' => $pending_payouts,
                    'total_unpaid_amount' => number_format($total_unpaid_amount, 2),
                    'total_commission' => number_format($total_commission, 2),
                    'total_wallet_balance' => number_format($total_wallet_balance, 2)
                ]
            ]);
        }

        // Export CSV
        if ($request->get('export') == '1') {
            $exportPayouts = $exportQuery->get();
            $filename = "vendor_payouts_{$frequency}_" . date('Ymd_His') . ".csv";
            $headers = ['Payout ID', 'Vendor Name', 'Store Name', 'Total Orders', 'Order Amount', 'Commission', 'Items Qty', 'Payout Amount', 'Payment Method', 'Status', 'Date'];
            $file = fopen('php://temp', 'w');
            fputcsv($file, $headers);
            foreach ($exportPayouts as $p) {
                $v = $p->vendor;
                fputcsv($file, [
                    'VP-' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
                    $v->name ?? 'N/A',
                    $v->store_name ?? 'N/A',
                    $p->total_orders ?? 0,
                    $p->order_amount ?? 0,
                    $p->commission_amount ?? 0,
                    $p->items_qty ?? 0,
                    $p->payout_amount ?? 0,
                    $p->payment_method ?? 'Bank Transfer',
                    $p->status ?? 'pending',
                    $p->paid_at ? $p->paid_at->format('Y-m-d') : ($p->created_at ? $p->created_at->format('Y-m-d') : ''),
                ]);
            }
            rewind($file);
            return response()->streamDownload(function () use ($file) { fpassthru($file); }, $filename, ['Content-Type' => 'text/csv']);
        }
       
        return view('backend/admin/vendor/vendor-payout', compact(
            'payouts',
            'total_payout_amount',
            'pending_payouts',
            'total_unpaid_amount',
            'total_commission',
            'total_wallet_balance',
            'frequency'
        ));
    }

    public function export_selected_payouts(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No payouts selected for export.');
        }
        $payouts = VendorPayout::with('vendor')
            ->whereIn('id', $ids)
            ->orderBy('id', 'desc')
            ->get();
        $filename = "vendor_payouts_selected_" . date('Ymd_His') . ".csv";
        $headers = ['Payout ID', 'Vendor Name', 'Store Name', 'Items Qty', 'Order Amount', 'Commission', 'Payout Amount', 'Payment Method', 'Status', 'Date'];
        $file = fopen('php://temp', 'w');
        fputcsv($file, $headers);
        foreach ($payouts as $p) {
            $v = $p->vendor;
            fputcsv($file, [
                'VP-' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
                $v->name ?? 'N/A',
                $v->store_name ?? 'N/A',
                $p->items_qty ?? 0,
                $p->order_amount ?? 0,
                $p->commission_amount ?? 0,
                $p->payout_amount ?? 0,
                $p->payment_method ?? 'Wallet',
                $p->status ?? 'pending',
                $p->paid_at ? $p->paid_at->format('Y-m-d') : ($p->created_at ? $p->created_at->format('Y-m-d') : ''),
            ]);
        }
        rewind($file);
        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function vendor_payout_create()
    {
        $vendors = User::where('role', '2')->where('status', 1)->orderBy('store_name')->get(['id','name','store_name']);
        return view('backend/admin/vendor/vendor-payout-create', compact('vendors'));
    }

    public function vendor_payout_store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'order_reference_id' => 'nullable|string',
            'order_amount' => 'nullable|numeric',
            'commission_amount' => 'nullable|numeric',
            'payout_amount' => 'nullable|numeric',
            'payment_method' => 'nullable|string|max:50',
            'note' => 'nullable|string'
        ]);
        $vendorId = (int) $request->vendor_id;
        $orderId = $request->order_id ? (int) $request->order_id : null;
        $orderAmount = (float) ($request->order_amount ?? 0);
        $commissionAmount = (float) ($request->commission_amount ?? 0);
        $payoutAmount = (float) ($request->payout_amount ?? 0);
        
        if ($orderId) {
            // Check if payout already exists for this order and vendor
            $exists = VendorPayout::where('order_id', $orderId)
                ->where('vendor_id', $vendorId)
                ->whereIn('status', ['unpaid', 'paid'])
                ->exists();
            if ($exists) {
                return redirect()->back()->withErrors(['order_id' => 'Payout already exists or requested for this order.'])->withInput();
            }

            $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
            $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);
            $items = OrderItem::where('order_id', $orderId)->where('vendor_id', $vendorId)->where('status', 3)->get();
            $grossOrder = 0;
            $commissionTotal = 0;
            $pgFeeTotal = 0;
            $campaignDiscountShare = 0;
            foreach ($items as $item) {
                $amt = $item->total_actual_price ?? ($item->price * $item->quantity);
                $grossOrder += $amt;
                $commissionTotal += ($amt * $commissionRate) / 100;
                $pgFeeTotal += ($amt * $pgFeePercent) / 100;
                $campaignDiscountShare += ($item->campaign_discount ?? 0) * ($item->quantity ?? 1);
            }
            $orderAmount = $grossOrder;
            $commissionAmount = $commissionTotal;
            $order = Order::find($orderId);
            $totalDeliveredGross = OrderItem::where('order_id', $orderId)->where('status', 3)->get()->sum(function ($it) {
                return $it->total_actual_price ?? ($it->price * $it->quantity);
            });
            $payoutAmount = max(0, $grossOrder - $commissionTotal - $pgFeeTotal - $campaignDiscountShare);
        }
        if (!$orderId && ($orderAmount <= 0 || $payoutAmount <= 0)) {
            return redirect()->back()->withErrors(['order_amount' => 'Enter valid amounts or select an order.'])->withInput();
        }
        $p = new VendorPayout();
        $p->vendor_id = $vendorId;
        $p->order_id = $orderId;
        $p->order_amount = $orderAmount;
        $p->commission_amount = $commissionAmount;
        $p->payout_amount = $payoutAmount;
        $p->payment_method = $request->payment_method ?: 'Bank Transfer';
        $p->status = 'pending';
        $p->note = $request->note;
        $p->save();
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Payout created', 'id' => $p->id]);
        }
        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Payout created successfully']);
        }

        return redirect()->route('vendor.payout')->with('success', 'Payout created successfully');
    }

    public function vendor_payout_show($id, Request $request)
    {
        $payout = VendorPayout::with(['vendor.country', 'order.user'])->findOrFail($id);
        $txQuery = WalletTransaction::where('user_id', $payout->vendor_id)
            ->orderBy('created_at', 'desc');
        $refs = [];
        $refs[] = 'PAYOUT-' . $payout->id;
        if (!empty($payout->order_id)) {
            $refs[] = 'VENDOR-SETTLEMENT-' . $payout->order_id . '-' . $payout->vendor_id;
        }
        $transactions = $txQuery->whereIn('reference_id', $refs)->get();
        $frequency = $request->get('frequency');
        // Fetch order items for this payout's vendor-order combo
        $items = collect();
        if (!empty($payout->order_id) && !empty($payout->vendor_id)) {
            $items = \App\Models\OrderItem::with('product')
                ->where('order_id', $payout->order_id)
                ->where('vendor_id', $payout->vendor_id)
                ->get();
        }
        return view('backend/admin/vendor/vendor-payout-show', compact('payout', 'transactions', 'frequency', 'items'));
    }

    public function update_payout_status(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed,approved',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'note' => 'nullable|string'
        ]);

        $payout = VendorPayout::with('vendor')->findOrFail($id);
        $oldStatus = (string) ($payout->status ?? 'unpaid');
        $newStatus = $request->input('status');

        DB::beginTransaction();
        // try {
            $payout->status = $newStatus;
            
            // Only update payment method if provided
            if ($request->filled('payment_method')) {
                $payout->payment_method = $request->payment_method;
            }
            
            // Default to 'Wallet' for paid status if no method is set
            if ($newStatus === 'paid' && !$payout->payment_method) {
                $payout->payment_method = 'Wallet';
            }

            if ($request->filled('transaction_id')) {
                $payout->transaction_id = $request->transaction_id;
            }
            if ($request->filled('note')) {
                $payout->note = $request->note;
            }

            if ($newStatus === 'paid') {
                $payout->paid_at = now();
            }
            $payout->save();

            // On confirmation to paid: settlement credit for payout with order_id, withdrawal debit for vendor-initiated payout
            
            if ($newStatus === 'paid') {
                $vendor = $payout->vendor;
                if ($vendor && $payout->payout_amount > 0) {
                    // Determine if this is a withdrawal (debit) or a payout/settlement (credit)
                    // A withdrawal is indicated by no order_id AND payment_method is 'Wallet Withdrawal'
                    $isWithdrawal = (empty($payout->order_id) && $payout->payment_method === 'Wallet Withdrawal');

                    if (!$isWithdrawal) {
                        // SETTLEMENT / PAYOUT (Credit)
                        $creditRef = 'PAYOUT-' . $payout->id;
                        
                        // Check if already credited by this payout ID or old settlement ID
                        $alreadyCreditedByPayout = WalletTransaction::where('user_id', $vendor->id)
                            ->where('reference_id', $creditRef)
                            ->exists();
                            
                        $alreadyCreditedBySettlement = false;
                        if (!empty($payout->order_id)) {
                            $settlementRef = 'VENDOR-SETTLEMENT-' . $payout->order_id . '-' . $payout->vendor_id;
                            $alreadyCreditedBySettlement = WalletTransaction::where('user_id', $vendor->id)
                                ->where('reference_id', $settlementRef)
                                ->exists();
                        }

                        if (!$alreadyCreditedByPayout && !$alreadyCreditedBySettlement) {
                            $oldBalance = (float) ($vendor->wallet_balance ?? 0);
                            $payoutAmount = (float) $payout->payout_amount;
                            
                            $vendor->wallet_balance = $oldBalance + $payoutAmount;
                            if (isset($vendor->status) && (string) $vendor->status !== '1') {
                                $vendor->status = 1;
                            }

                            $vendor->save();
                            
                            WalletTransaction::create([
                                'user_id' => $vendor->id,
                                'amount' => $payoutAmount,
                                'type' => 'credit',
                                'description' => $payout->order_id ? ('Vendor settlement for Order #' . $payout->order_id) : ('Vendor payout #' . str_pad($payout->id, 4, '0', STR_PAD_LEFT)),
                                'reference_id' => $creditRef,
                                'status' => 'completed',
                            ]);
                            \App\Helpers\NotificationHelper::notifyVendor($vendor->id, [
                                'title' => 'Payout Credited',
                                'message' => 'Your payout has been credited. Amount: ' . number_format($payout->payout_amount, 2),
                                'type' => 'finance',
                                'url' => route('vendor.wallet', ['type' => 'payout']),
                                'icon' => 'solar:wallet-2-linear',
                                'priority' => 'medium'
                            ]);
                        }
                    } else {
                        // WITHDRAWAL (Debit)
                        $withdrawRef = 'WITHDRAWAL-' . $payout->id;
                        $alreadyDebited = WalletTransaction::where('reference_id', $withdrawRef)->exists();
                        if (!$alreadyDebited) {
                            $deduct = min((float)$payout->payout_amount, (float)($vendor->wallet_balance ?? 0));
                            if ($deduct > 0) {
                                $vendor->wallet_balance = max(0, ($vendor->wallet_balance ?? 0) - $deduct);
                                $vendor->save();
                                WalletTransaction::create([
                                    'user_id' => $vendor->id,
                                    'amount' => $deduct,
                                    'type' => 'debit',
                                    'description' => 'Withdrawal #' . str_pad($payout->id, 4, '0', STR_PAD_LEFT),
                                    'reference_id' => $withdrawRef,
                                    'status' => 'completed',
                                ]);
                                \App\Helpers\NotificationHelper::notifyVendor($vendor->id, [
                                    'title' => 'Withdrawal Processed',
                                    'message' => 'Your withdrawal request has been processed. Amount: ' . number_format($deduct, 2),
                                    'type' => 'finance',
                                    'url' => route('vendor.wallet', ['type' => 'withdrawal']),
                                    'icon' => 'solar:card-transfer-linear',
                                    'priority' => 'medium'
                                ]);
                            }
                        }
                    }
                }
            }
            
            DB::commit();
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Payout status updated successfully.',
                    'data' => [
                        'status' => $payout->status,
                        'payment_method' => $payout->payment_method,
                        'paid_at' => $payout->paid_at ? $payout->paid_at->format('Y-m-d H:i:s') : null,
                        'summary' => [
                            'total_payout_amount' => VendorPayout::where('status', 'paid')->sum('payout_amount'),
                            'pending_payouts' => VendorPayout::whereIn('status', ['pending', 'approved'])->count(),
                            'total_commission' => VendorPayout::sum('commission_amount')
                        ]
                    ]
                ]);
            }
            return redirect()->back()->with('success', 'Payout status updated successfully.');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     if ($request->expectsJson()) {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Failed to update payout status: ' . $e->getMessage()
        //         ], 500);
        //     }
        //     return redirect()->back()->with('error', 'Failed to update payout status: ' . $e->getMessage());
        // }
    }

    public function vendor_detail(Request $request, $uqid)
    {
        $vendor = User::where('users.uqid', $uqid)
            ->leftjoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftjoin('states', 'users.state_id', '=', 'states.id')
            ->leftjoin('cities', 'users.city_id', '=', 'cities.id')
            ->addSelect('users.*', 'users.image as logo', 'countries.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            ->withCount('products')
            ->firstOrFail();

        $vendor->logo = ImageHelper::getVendorsImage($vendor->logo);
        $vendor->total_sale = PriceHelper::getVendorTotalSale($vendor->id);
        $orders_count = OrderItem::where('vendor_id', $vendor->id)->distinct('order_id')->count();
        $total_discount = OrderItem::where('vendor_id', $vendor->id)->sum('campaign_discount');
        $total_commission = VendorPayout::where('vendor_id', $vendor->id)->sum('commission_amount');
        $total_products_count = Product::where('vendor_id', $vendor->id)->count();
        $vendor->total_products = Product::where('vendor_id', $vendor->id)->count();
        $vendor->total_reviews = ProductReview::whereIn('product_id', Product::where('vendor_id', $vendor->id)->pluck('id'))->count();
        $vendor->avg_rating = ProductReview::whereIn('product_id', Product::where('vendor_id', $vendor->id)->pluck('id'))->avg('rating');
        $vendor->documents = VendorsDocument::where('vendor_id', $vendor->id)->get();
        $vendor->kyc_documents = KYC_Document::where('is_active', 1)->get();

        $cancelledChequeDocId = KYC_Document::where('name', 'cancelled_cheque')->value('id');
        $cancelled_cheque_doc = VendorsDocument::where('vendor_id', $vendor->id)->where('document_id', $cancelledChequeDocId)->first();
        if ($cancelled_cheque_doc) {
            $vendor->cancelled_cheque = ImageHelper::getVendorDocImage($cancelled_cheque_doc->document);
        } else {
            $vendor->cancelled_cheque = ImageHelper::getVendorsCancelledChequeImage($vendor->cancelled_cheque);
        }

        $profit_by_category = OrderItem::where('order_items.vendor_id', $vendor->id)
    ->join('products', 'order_items.product_id', '=', 'products.id')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select('categories.name as category_name', DB::raw('SUM(order_items.total_actual_price) as total_profit'))
    ->groupBy('categories.name')
    ->get();



        $product_ids = Product::where('vendor_id', $vendor->id)->pluck('id');
        $item_stock = ProductVariant::whereIn('product_id', $product_ids)->sum('stock');

        // Auto-approve vendor when loading detail page if KYC details and all required documents are approved
        if ($vendor->hasMinimumKyc() && $vendor->areRequiredDocumentsVerified() && (int)$vendor->status !== 1) {
            User::where('id', $vendor->id)->update(['status' => 1]);
            $vendor->status = 1;
        }

        $total_sells = OrderItem::where('vendor_id', $vendor->id)->sum('quantity');
        $total_orders = OrderItem::where('vendor_id', $vendor->id)->distinct('order_id')->count();
        $completed_orders = OrderItem::where('vendor_id', $vendor->id)->where('status', 3)->distinct('order_id')->count();
        $order_complete_percent = $total_orders > 0 ? round(($completed_orders / $total_orders) * 100) : 0;

        $vendor_users_count = 0;

        $products = Product::where('vendor_id', $vendor->id)->with('variants')->paginate(10)->withQueryString();
        foreach ($products as $product) {
            $firstVariant = $product->variants->first();
            $imageName = '';
            if ($firstVariant && $firstVariant->image) {
                $images = json_decode($firstVariant->image, true);
                if (is_array($images) && count($images) > 0) {
                    $imageName = $images[0];
                }
            }
            $product->image = ImageHelper::getProductImage($imageName);
        }   

        $vendor->review_count = ProductReview::whereIn('product_id', Product::where('vendor_id', $vendor->id)->pluck('id'))->count();

        $total_revenue = OrderItem::where('vendor_id', $vendor->id)->where('status', 3)->sum('total_actual_price');
        $monthly_revenue = OrderItem::where('vendor_id', $vendor->id)->where('status', 3)->whereMonth('created_at', now()->month)->sum('total_actual_price');

        $revenue_chart_data = [];
        $expense_chart_data = [];
        $revenue_chart_labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue_chart_labels[] = $month->format('M Y');

            $revenue = OrderItem::where('vendor_id', $vendor->id)
                ->where('status', 3)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_actual_price');
            $revenue_chart_data[] = $revenue;

            $expense = VendorPayout::where('vendor_id', $vendor->id)
                ->where('status', 'paid')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('payout_amount');
            $expense_chart_data[] = $expense;
        }

        $vendor_docs = VendorsDocument::where('vendor_id', $vendor->id)->get();
        foreach ($vendor_docs as $doc) {
            $doc->document = ImageHelper::getVendorDocImage($doc->document);
        }

        $reviews = ProductReview::whereIn('product_id', $product_ids)
            ->with(['product', 'user'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10, ['*'], 'review_page')
            ->withQueryString();

        $order_ids = OrderItem::where('vendor_id', $vendor->id)->pluck('order_id')->unique();
        $vendor_users_count = Order::whereIn('id', $order_ids)->distinct('user_id')->count();
        $user_percent = 0;

        return view('backend/admin/vendor/vendor-detail', compact('vendor', 'total_products_count', 'orders_count', 'total_discount', 'total_commission', 'profit_by_category', 'item_stock', 'total_sells', 'total_orders', 'completed_orders', 'order_complete_percent', 'vendor_users_count', 'products', 'total_revenue', 'monthly_revenue', 'revenue_chart_data', 'expense_chart_data', 'revenue_chart_labels', 'vendor_docs', 'user_percent', 'reviews'));
    }


}
