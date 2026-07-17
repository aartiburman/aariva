<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Helpers\ImageHelper;
use App\Models\VendorsDocument;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Can;
use App\Models\KYC_Document;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\ProductSize;
use App\Helpers\NotificationHelper;
use App\Helpers\EmailHelper;
use App\Models\Category;
use App\Models\GeneralSetting;



class VendorProfileController extends Controller
{
     public function vendor_profile(Request $request)
    {
        $id = Auth::user()->id;
        $vendor_data =   User::with('country')->where('id', $id)->first();
        $vendor_data->image = ImageHelper::getVendorsImage($vendor_data->image);
        
        $currencySymbol = $vendor_data->country ? $vendor_data->country->currency : 'AED';

        $products = Product::where('vendor_id', $id)->get();
        foreach($products as $product) {
            $product->image = ImageHelper::getProductImage($product->image);
        }

          $query = Product::with('variants')
            ->select(
                'products.*',
                'vendor.name as vendor_name',
                'categories.name as category_name',
                'subcategories.name as subcategory_name',
                'child_categories.name as child_category_name',
                'brands.name as brand_name'
            )
            ->leftJoin('users as vendor', 'vendor.id', 'products.vendor_id')
            ->leftJoin('categories', 'categories.id', 'products.category_id')
            ->leftJoin('subcategories', 'subcategories.id', 'products.subcategory_id')
            ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->where('products.vendor_id', $id)
            ->distinct('products.id');

        /* ===============================
        STATUS FILTER (TAB / DROPDOWN)
        =============================== */
        if ($request->filled('status')) {
            $query->where('products.status', $request->status);
        } 



        $products = $query->get();

           foreach ($products as $product) {
            // Set display image to first variant image if available, else product thumbnail
            $firstVariant = $product->variants->first();
            $firstImage = null;
            
            if ($firstVariant && $firstVariant->image) {
                $images = json_decode($firstVariant->image, true);
                if (is_array($images) && count($images) > 0) {
                    $firstImage = $images[0];
                }
            }

            if ($firstImage) {
                $product->display_image = asset('uploads/products/' . $firstImage);
            } else {
                $product->display_image = asset('uploads/products/' . $product->thumbnail);
            }

            // Calculate display price from variants
            $minPrice = $product->variants->where('final_price', '>', 0)->min('final_price');
            if (!$minPrice) {
                $minPrice = $product->variants->where('price', '>', 0)->min('price');
            }
            $product->display_price = $minPrice ?: $product->price;

            foreach ($product->variants as $variant) {
                $sizeIds = json_decode($variant->size, true) ?? [];
                $variant->sizes_list = ProductSize::whereIn('id', $sizeIds)
                    ->where('status', 1)
                    ->get();
            }
        }

        $vendor_docs = VendorsDocument::with('documentType')->where('vendor_id', $id)->get();
        foreach($vendor_docs as $doc) {
            $doc->document = ImageHelper::getVendorDocImage($doc->document);
        }

        $vendor_data->cancelled_cheque = ImageHelper::getVendorsCancelledChequeImage($vendor_data->cancelled_cheque);

        return view('backend/vendor/vendor-profile', compact('vendor_data', 'products', 'vendor_docs', 'currencySymbol'));
    }

  
    public function edit_vendor_profile(Request $request)
    {
        $id = Auth::user()->id;
        $vendor_data =   User::where('id', $id)->first();
        $vendor_data->image = ImageHelper::getVendorsImage($vendor_data->image);
        $vendor_data->cancelled_cheque = ImageHelper::getVendorsCancelledChequeImage($vendor_data->cancelled_cheque);
        $vendor_docs = VendorsDocument::where('vendor_id', $id)->get();
        foreach($vendor_docs as $doc) {
            $doc->document = ImageHelper::getVendorDocImage($doc->document);
        }     
       


         $vendor_doc_type = KYC_Document::get();
         $country = Country::where('is_active', 1)->get();
         $state = State::where('is_active', 1)->get();
         $cities = City::where('is_active', 1)->get();
         $categories = Category::where('is_active', 1)->get();

        return view('backend/vendor/edit-vendor-profile', compact('vendor_data', 'vendor_docs', 'vendor_doc_type','country','state','cities', 'categories'));
    }

    public function update_vendor_logo(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $id = Auth::user()->id;
        $vendor = User::findOrFail($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . rand(100, 999) . '.' . $image->getClientOriginalExtension();

            // delete old image if it is local
            if ($vendor->image && !preg_match('/^https?:\/\//', $vendor->image)) {
                $oldPath = public_path('uploads/vendors/' . $vendor->image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $image->move(public_path('uploads/vendors'), $imageName);
            $vendor->image = $imageName;
            $vendor->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile logo updated successfully.',
                'image_url' => asset('uploads/vendors/' . $imageName)
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No image file provided.'
        ], 400);
    }

    public function update_vendor_profile(Request $request)
    {
        $id = Auth::user()->id;
        $vendor = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $vendor->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'city_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'country_id' => 'nullable|integer',
            'zip' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            // Bank details
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'account_holder_name' => 'nullable|string|max:255',
            'branch_location' => 'required|string|max:255',
            'cancelled_cheque' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'payout_frequency' => 'nullable|in:weekly,monthly,daily,bi-weekly',
            'category_ids'    => 'nullable|array|min:1',
            'vendor_description' => 'nullable|string|max:1000',
        ];

        $request->validate($rules);

        // handle bank proof upload
        if ($request->hasFile('cancelled_cheque')) {
            $file = $request->file('cancelled_cheque');
            if ($file->isValid()) {
                $name = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
                
                // delete old file if it is local
                if ($vendor->cancelled_cheque && !preg_match('/^https?:\/\//', $vendor->cancelled_cheque)) {
                    $oldPath = public_path('uploads/vendors/cancelled_cheque/' . $vendor->cancelled_cheque);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                $file->move(public_path('uploads/vendors/cancelled_cheque'), $name);
                $vendor->cancelled_cheque = $name;
            }
        }

        // update basic fields
        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->address = $request->address;
        $vendor->city_id = $request->city_id;
        $vendor->state_id = $request->state_id;
        $vendor->country_id = $request->country_id;
        $vendor->zip = $request->zip;
        $vendor->business_name = $request->business_name;
        $vendor->vendor_description = $request->vendor_description;
        
        // update bank fields
        if ($request->has('bank_name')) $vendor->bank_name = $request->bank_name;
        if ($request->has('account_number')) $vendor->account_number = $request->account_number;
        if ($request->has('ifsc_code')) $vendor->ifsc_code = $request->ifsc_code;
        if ($request->has('account_holder_name')) $vendor->account_holder_name = $request->account_holder_name;
        if ($request->has('branch_location')) $vendor->branch_location = $request->branch_location;

        if ($request->has('category_ids')) {
            $vendor->category_ids = $request->category_ids;
        }

        if ($request->filled('payout_frequency') && in_array($request->payout_frequency, ['weekly', 'monthly', 'daily', 'bi-weekly'])) {
            $vendor->payout_frequency = $request->payout_frequency;
        }

        if ($request->filled('status')) {
            $vendor->status = $request->status;
        }

        $vendor->save();

        return redirect()->route('vendor.profile')->with('success', 'Profile updated successfully');
    }


       public function update_bank_proof(Request $request)
    {
        $id = Auth::user()->id;
        $vendor = User::findOrFail($id);

        $rules = [
              'cancelled_cheque' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ];

        $request->validate($rules);
        // handle bank proof upload
        if ($request->hasFile('cancelled_cheque')) {
            $file = $request->file('cancelled_cheque');
            if ($file->isValid()) {
                $name = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
                
                // delete old file if it is local
                if ($vendor->cancelled_cheque && !preg_match('/^https?:\/\//', $vendor->cancelled_cheque)) {
                    $oldPath = public_path('uploads/vendors/cancelled_cheque/' . $vendor->cancelled_cheque);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                
                $file->move(public_path('uploads/vendors/cancelled_cheque'), $name);
                $vendor->cancelled_cheque = $name;
            }
        }

      

        $vendor->save();

        return redirect()->back()->with('success', 'Bank proof updated successfully');
    }

    /**
     * Update or add vendor documents
     */
    public function update_vendor_documents(Request $request)
    {
        $id = Auth::user()->id;
        $vendor = VendorsDocument::where('vendor_id', $id)->first();

        $isAjax = $request->ajax() || $request->wantsJson();

        // remove checked documents by id or filename
        $toDelete = $request->input('delete_documents', []);
        if (!empty($toDelete)) {
            foreach ($toDelete as $del) {
                // if numeric, treat as ID
                if (is_numeric($del)) {
                    $doc = VendorsDocument::where('id', $del)->where('vendor_id', $id)->first();
                    if ($doc) {
                        if (!preg_match('/^https?:\/\//', (string)$doc->document)) {
                            $path = public_path('uploads/vendors/documents/' . ltrim(basename($doc->document), '/'));
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                        $doc->delete();
                    }
                } else {
                    // match by filename
                    $doc = VendorsDocument::where('vendor_id', $id)
                        ->where(function($q) use ($del) {
                            $q->where('document', $del)
                              ->orWhereRaw('BINARY document = ?', [$del])
                              ;
                        })->first();
                    if ($doc) {
                        if (!preg_match('/^https?:\/\//', (string)$doc->document)) {
                            $path = public_path('uploads/vendors/documents/' . ltrim(basename($doc->document), '/'));
                            if (File::exists($path)) {
                                File::delete($path);
                            }
                        }
                        $doc->delete();
                    }
                }
            }
        }

        // validate single document upload (one at a time)
        $request->validate([
            'document' => 'nullable|file|mimes:pdf,jpeg,png,jpg,doc,docx|max:5120',
            'document_id' => 'nullable|exists:kyc_documents,id',
            'document_number' => 'nullable|string|max:255'
        ]);

        // handle new single upload under 'document'
        $newDoc = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            if ($file->isValid()) {
                $document_id	 = $request->input('document_id', null);
                $document_number = $request->input('document_number', null);
                $name = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('uploads/vendors/documents'), $name);
                $newDoc = VendorsDocument::create([
                    'vendor_id' => $id,
                    'document_id' => $document_id,
                    'document_number' => $document_number,
                    'document' => $name,
                    'is_verify' => 0,
                ]);

                // Notify Admin
                NotificationHelper::notifyAdmins([
                    'title' => 'New Document Uploaded',
                    'message' => 'Vendor ' . Auth::user()->name . ' has uploaded a new document.',
                    'type' => 'system',
                    'url' => route('vendor.detail', Auth::user()->uqid), // Fixed to use uqid
                    'icon' => 'solar:document-bold-duotone',
                    'priority' => 'medium'
                ]);

                // Send Email Notification to Admin
                $admin_email = \App\Models\EmailSetting::where('status', 1)->value('mail_from_address') ?? 'admin@ecom.com';
                EmailHelper::send(
                    $admin_email,
                    'Document Upload Notification: ' . Auth::user()->store_name,
                    'Vendor <b>' . Auth::user()->store_name . '</b> has uploaded a new document for verification.'
                );

                // Send Email Notification to Vendor
                EmailHelper::send(
                    Auth::user()->email,
                    'Document Uploaded Successfully',
                    'Your document has been uploaded successfully and is pending verification. We will notify you once it is reviewed.'
                );

                // Check if all required documents are uploaded
                $required_count = KYC_Document::count();
                $uploaded_count = VendorsDocument::where('vendor_id', $id)
                    ->whereNotNull('document_id')
                    ->distinct('document_id')
                    ->count();

                if ($uploaded_count >= $required_count) {
                    // Notify Admin that all documents are uploaded
                    EmailHelper::send(
                        $admin_email,
                        'All Documents Uploaded: ' . Auth::user()->store_name,
                        'Vendor <b>' . Auth::user()->store_name . '</b> has uploaded all required documents and is waiting for account activation.'
                    );

                    // Notify Vendor
                    EmailHelper::send(
                        Auth::user()->email,
                        'All Documents Uploaded - Pending Activation',
                        'Thank you for uploading all required documents. Our team will review them and activate your account soon.'
                    );
                }
            }
        }

        if ($isAjax) {
            // Fresh vendor documents and uploaded type IDs
            $docs = VendorsDocument::with('documentType')
                ->where('vendor_id', $id)
                ->get();
            $uploadedTypeIds = $docs->pluck('document_id')->filter()->unique()->values();

            return response()->json([
                'status' => true,
                'message' => 'Document uploaded successfully',
                'data' => $newDoc ? [
                    'id' => $newDoc->id,
                    'document_id' => $newDoc->document_id,
                    'document_url' => \App\Helpers\ImageHelper::getVendorDocImage($newDoc->document),
                    'document_name' => optional($newDoc->documentType)->name,
                    'is_verify' => (int) $newDoc->is_verify,
                ] : null,
                'uploaded_type_ids' => $uploadedTypeIds,
            ]);
        }

        return redirect()->back()->with('success', 'Documents updated successfully');
    }
    

    public function vendor_change_password (Request $request)
    {
        return view('backend/vendor/vendor-change-password');
    }

    
      public function add_vendor_bank_detail (Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = User::findOrFail($vendor_id);

        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:20',
            'account_holder_name' => 'required|string|max:255',
            'branch_location' => 'required|string|max:255',
            'cancelled_cheque' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        // update bank details
        $cancelled_cheque = $vendor->cancelled_cheque;
        if ($request->hasFile('cancelled_cheque')) {
            $file = $request->file('cancelled_cheque');
            if ($file->isValid()) {
                $name = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
                $file->move(public_path('uploads/vendors/cancelled_cheque'), $name);
                $cancelled_cheque = $name;
            }
        }

        
        $data = [
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'account_holder_name' => $request->account_holder_name,
            'branch_location' => $request->branch_location,
            'cancelled_cheque'  => $cancelled_cheque,
        ];
    
            User::where('id', $vendor_id)->update($data);

        return redirect()->back()->with('success', 'Bank details added successfully');
        
    }

    public function update_vendor_password(Request $request)
    {
        $id = Auth::user()->id;
        $vendor = User::findOrFail($id);

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Check current password
        if (!password_verify($request->current_password, $vendor->password)) {
             return redirect()->back()->with('error', 'Current password does not match');
        }

        $vendor->password = bcrypt($request->new_password);
        $vendor->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function delivery_settings()
    {
        $vendor_data = Auth::user();
        return view('backend.vendor.delivery-settings', compact('vendor_data'));
    }

    public function update_delivery_settings(Request $request)
    {
        $request->validate([
            'delivery_days' => 'required|string|max:50',
        ]);

        $vendor = User::findOrFail(Auth::user()->id);
        $vendor->delivery_days = $request->delivery_days;
        $vendor->save();

        return redirect()->back()->with('success', 'Delivery settings updated successfully');
    }

    public function kyc_documents()
    {
        $vendor_id = Auth::user()->id;
        $vendor = User::findOrFail($vendor_id);
        $vendor_docs = VendorsDocument::with('documentType')
            ->where('vendor_id', $vendor_id)
            ->get();
        
        foreach ($vendor_docs as $doc) {
            $doc->document = \App\Helpers\ImageHelper::getVendorDocImage($doc->document);
        }

        
        $vendor->cancelled_cheque = ImageHelper::getVendorsCancelledChequeImage($vendor->cancelled_cheque);
       
        $kyc_types = KYC_Document::where('is_active', 1)->get();

        return view('backend/vendor/kyc_documents', compact('vendor', 'vendor_docs', 'kyc_types'));
    }
}
