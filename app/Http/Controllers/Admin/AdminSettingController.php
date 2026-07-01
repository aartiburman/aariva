<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Models\TermsAndCondition;
use App\Models\VendorPolicy;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\PaymentGateway;
use App\Models\EmailSetting;
use App\Models\SmsSetting;
use App\Models\NotificationSetting;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;
use App\Helpers\ImageHelper;
use App\Models\User;
use App\Helpers\EmailHelper;
use App\Models\KYC_Document;

class AdminSettingController extends Controller
{
    public function privacy_policy(Request $request)
    {
        $policies = PrivacyPolicy::all();
        return view('backend/admin/setting/privacy-policy', compact('policies'));
    }

    public function add_privacy_policy(Request $request)
    {
        return view('backend/admin/setting/add-privacy-policy');
    }

    public function edit_privacy_policy(Request $request, $id)
    {
        $policy = PrivacyPolicy::findOrFail($id);
        return view('backend/admin/setting/add-privacy-policy', compact('policy'));
    }

    public function privacy_policy_store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        if ($request->id) {
            $policy = PrivacyPolicy::findOrFail($request->id);
            $totalPolicies = PrivacyPolicy::count();
            $status = $request->status ?? 1;

            if ($totalPolicies == 1 && $status == 0) {
                return redirect()->back()->with('error', 'The only Privacy Policy cannot be deactivated.');
            }

            if ($status == 1) {
                PrivacyPolicy::where('id', '!=', $request->id)->update(['status' => 0]);
            } else {
                $otherActive = PrivacyPolicy::where('id', '!=', $request->id)->where('status', 1)->exists();
                if (!$otherActive) {
                    return redirect()->back()->with('error', 'At least one Privacy Policy must remain active.');
                }
            }

            $policy->update([
                'title' => $request->title,
                'title_ar' => $trAr->translate($request->title),
                'title_ne' => $trNe->translate($request->title),
                'content' => $request->content,
                'content_ar' => $trAr->translate($request->content),
                'content_ne' => $trNe->translate($request->content),
                'status' => $status,
            ]);
            return redirect()->route('privacy.policy.list')->with('success', 'Privacy Policy updated successfully');
        }

        $status = $request->status ?? 1;
        if ($status == 1) {
            PrivacyPolicy::where('status', 1)->update(['status' => 0]);
        } else {
            $anyActive = PrivacyPolicy::where('status', 1)->exists();
            if (!$anyActive) {
                $status = 1; // Force active if none exists
            }
        }

        PrivacyPolicy::create([
            'title' => $request->title,
            'title_ar' => $trAr->translate($request->title),
            'title_ne' => $trNe->translate($request->title),
            'content' => $request->content,
            'content_ar' => $trAr->translate($request->content),
            'content_ne' => $trNe->translate($request->content),
            'status' => $status,
            'version' => 'v1',
        ]);

        return redirect()->route('privacy.policy.list')->with('success', 'Privacy Policy created successfully');
    }

    public function delete_privacy_policy(Request $request)
    {
        $policy = PrivacyPolicy::findOrFail($request->id);
        $policy->delete();
        return response()->json(['status' => true, 'message' => 'Privacy Policy deleted successfully']);
    }

    public function terms_and_condition(Request $request)
    {
        $terms = TermsAndCondition::all();
        return view('backend/admin/setting/terms-and-condition', compact('terms'));
    }

    public function add_terms_and_condition(Request $request)
    {
        return view('backend/admin/setting/add-terms-and-condition');
    }

    public function edit_terms_and_condition(Request $request, $id)
    {
        $term = TermsAndCondition::findOrFail($id);
        return view('backend/admin/setting/add-terms-and-condition', compact('term'));
    }

    public function terms_and_condition_store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        if ($request->id) {
            $term = TermsAndCondition::findOrFail($request->id);
            $totalTerms = TermsAndCondition::count();
            $status = $request->status ?? 1;

            if ($totalTerms == 1 && $status == 0) {
                return redirect()->back()->with('error', 'The only Terms and Condition cannot be deactivated.');
            }

            if ($status == 1) {
                TermsAndCondition::where('id', '!=', $request->id)->update(['status' => 0]);
            } else {
                $otherActive = TermsAndCondition::where('id', '!=', $request->id)->where('status', 1)->exists();
                if (!$otherActive) {
                    return redirect()->back()->with('error', 'At least one Terms and Condition must remain active.');
                }
            }

            $term->update([
                'title' => $request->title,
                'title_ar' => $trAr->translate($request->title),
                'title_ne' => $trNe->translate($request->title),
                'content' => $request->content,
                'content_ar' => $trAr->translate($request->content),
                'content_ne' => $trNe->translate($request->content),
                'status' => $status,
            ]);
            return redirect()->route('term.and.conditions.list')->with('success', 'Terms and Condition updated successfully');
        }

        $status = $request->status ?? 1;
        if ($status == 1) {
            TermsAndCondition::where('status', 1)->update(['status' => 0]);
        } else {
            $anyActive = TermsAndCondition::where('status', 1)->exists();
            if (!$anyActive) {
                $status = 1; // Force active if none exists
            }
        }

        TermsAndCondition::create([
            'title' => $request->title,
            'title_ar' => $trAr->translate($request->title),
            'title_ne' => $trNe->translate($request->title),
            'content' => $request->content,
            'content_ar' => $trAr->translate($request->content),
            'content_ne' => $trNe->translate($request->content),
            'status' => $status,
        ]);

        return redirect()->route('term.and.conditions.list')->with('success', 'Terms and Condition created successfully');
    }

    public function delete_terms_and_condition(Request $request)
    {
        $term = TermsAndCondition::findOrFail($request->id);
        $term->delete();
        return response()->json(['status' => true, 'message' => 'Terms and Condition deleted successfully']);
    }

    public function vendor_policy(Request $request)
    {
        $policies = VendorPolicy::all();
        return view('backend/admin/setting/vendor-policy', compact('policies'));
    }

    public function add_vendor_policy(Request $request)
    {
        return view('backend/admin/setting/add-vendor-policy');
    }

    public function edit_vendor_policy(Request $request, $id)
    {
        $policy = VendorPolicy::findOrFail($id);
        return view('backend/admin/setting/add-vendor-policy', compact('policy'));
    }

    public function vendor_policy_store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        if ($request->id) {
            $policy = VendorPolicy::findOrFail($request->id);
            $totalPolicies = VendorPolicy::count();
            $status = $request->status ?? 1;

            if ($totalPolicies == 1 && $status == 0) {
                return redirect()->back()->with('error', 'The only Vendor Policy cannot be deactivated.');
            }

            if ($status == 1) {
                // Deactivate all existing policies before making this one active
                VendorPolicy::where('id', '!=', $request->id)->update(['status' => 0]);
            } else {
                $otherActive = VendorPolicy::where('id', '!=', $request->id)->where('status', 1)->exists();
                if (!$otherActive) {
                    return redirect()->back()->with('error', 'At least one Vendor Policy must remain active.');
                }
            }
            
            // Logic for versioning on edit: Increment minor version (e.g., v1.0 -> v1.1)
            $currentVersion = $policy->version ?? 'v1.0';
            $versionParts = explode('.', str_replace('v', '', $currentVersion));
            $major = isset($versionParts[0]) ? (int)$versionParts[0] : 1;
            $minor = isset($versionParts[1]) ? (int)$versionParts[1] : 0;
            $newVersion = 'v' . $major . '.' . ($minor + 1);
            $policy->update([
                'title' => $request->title,
                'title_ar' => $trAr->translate($request->title),
                'title_ne' => $trNe->translate($request->title),
                'content' => $request->content,
                'content_ar' => $trAr->translate($request->content),
                'content_ne' => $trNe->translate($request->content),
                'status' => $status,
                'version' => $newVersion,
            ]);

            // Notify all vendors about the policy update
            if ($status == 1) {
                $this->notifyVendorsOfPolicyUpdate($policy);
            }

            return redirect()->route('vendor.policy.list')->with('success', 'Vendor Policy updated to version ' . $newVersion);
        }

        // Logic for versioning on create: Start with v2.0 or increment major version
        $latestPolicy = VendorPolicy::orderBy('id', 'desc')->first();
        if ($latestPolicy) {
            $latestVersion = $latestPolicy->version ?? 'v1.0';
            $versionParts = explode('.', str_replace('v', '', $latestVersion));
            $major = isset($versionParts[0]) ? (int)$versionParts[0] : 1;
            $newVersion = 'v' . ($major + 1) . '.0';
        } else {
            $newVersion = 'v2.0';
        }

        $status = $request->status ?? 1;
        if ($status == 1) {
            VendorPolicy::where('status', 1)->update(['status' => 0]);
        } else {
            $anyActive = VendorPolicy::where('status', 1)->exists();
            if (!$anyActive) {
                $status = 1; // Force active if none exists
            }
        }

        $policy = VendorPolicy::create([
            'title' => $request->title,
            'title_ar' => $trAr->translate($request->title),
            'title_ne' => $trNe->translate($request->title),
            'content' => $request->content,
            'content_ar' => $trAr->translate($request->content),
            'content_ne' => $trNe->translate($request->content),
            'status' => $status,
            'version' => $newVersion,
        ]);

        // Notify all vendors about the new policy
        if ($status == 1) {
            $this->notifyVendorsOfPolicyUpdate($policy);
        }

        return redirect()->route('vendor.policy.list')->with('success', 'Vendor Policy created with version ' . $newVersion);
    }

    private function notifyVendorsOfPolicyUpdate($policy)
    {
        // Reset agreement status for all vendors so they must re-accept the new policy
        User::where('role', '2')->update([
            'agreement' => 0,
            'agreement_id' => $policy->id
        ]);

        $vendors = User::where('role', '2')->whereNotNull('email')->get();
        $policyUrl = config('app.url') . '/vendor/policy'; // Assuming this is the dashboard policy URL

        foreach ($vendors as $vendor) {
            EmailHelper::send(
                $vendor->email,
                'Important: Vendor Policy Update - ' . $policy->version,
                '',
                'emails.policy-update',
                [
                    'vendor_name' => $vendor->name,
                    'policy_title' => $policy->title,
                    'version' => $policy->version,
                    'policy_url' => $policyUrl
                ]
            );
        }
    }

    public function delete_vendor_policy(Request $request)
    {
        $policy = VendorPolicy::findOrFail($request->id);
        $policy->delete();
        return response()->json(['status' => true, 'message' => 'Vendor Policy deleted successfully']);
    }

    public function kyc_documents(Request $request)
    {
        $documents = KYC_Document::all();
        return view('backend/admin/setting/kyc-documents', compact('documents'));
    }

    public function kyc_document_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($request->id) {
            $doc = KYC_Document::findOrFail($request->id);
            $doc->update([
                'name' => $request->name,
                'is_active' => $request->is_active ?? 1
            ]);
            return redirect()->back()->with('success', 'KYC Document updated successfully');
        }

        KYC_Document::create([
            'name' => $request->name,
            'is_active' => $request->is_active ?? 1
        ]);

        return redirect()->back()->with('success', 'KYC Document added successfully');
    }

    public function delete_kyc_document(Request $request)
    {
        $doc = KYC_Document::findOrFail($request->id);
        $doc->delete();
        return response()->json(['status' => true, 'message' => 'KYC Document deleted successfully']);
    }

    public function toggle_kyc_document_status(Request $request)
    {
        $doc = KYC_Document::findOrFail($request->id);
        $doc->is_active = $request->status;
        $doc->save();
        return response()->json(['status' => true, 'message' => 'KYC Document status updated successfully']);
    }

    public function payment_gateway_list()
    {
        $gateways = PaymentGateway::all();
        foreach ($gateways as $gateway) {
            $gateway->image = ImageHelper::getPaymentGatewayImage($gateway->image);
            $gateway->logo = ImageHelper::getPaymentGatewayLogo($gateway->logo);
        }
        return view('backend.admin.setting.payment-gateway-list', compact('gateways'));
    }

    public function payment_gateway_edit($id)
    {
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->image = ImageHelper::getPaymentGatewayImage($gateway->image);
        $gateway->logo = ImageHelper::getPaymentGatewayLogo($gateway->logo);
        return view('backend.admin.setting.add-payment-getway-setting', compact('gateway'));
    }

    public function payment_gateway_update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'name' => 'required',
            'status' => 'required',
            'mode' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $gateway = PaymentGateway::findOrFail($request->id);
        
        $fields = [
            'name', 'status', 'mode',
            'public_key', 'secret_key',
            'live_public_key', 'live_secret_key',
            'test_public_key', 'test_secret_key',
            'merchant_id', 'app_id',
            'sandbox_base_url', 'live_base_url',
            'success_url', 'failure_url',
        ];

        $updateData = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        if ($request->hasFile('image')) {
            $imageName = ImageHelper::compressImage($request->file('image'), 'uploads/payment_gateways');
            $updateData['image'] = $imageName;
            
            if ($gateway->image && file_exists(public_path('uploads/payment_gateways/' . $gateway->image))) {
                unlink(public_path('uploads/payment_gateways/' . $gateway->image));
            }
        }

        if ($request->hasFile('logo')) {
            $logoName = ImageHelper::compressImage($request->file('logo'), 'uploads/payment_gateways');
            $updateData['logo'] = $logoName;
            
            if ($gateway->logo && file_exists(public_path('uploads/payment_gateways/' . $gateway->logo))) {
                unlink(public_path('uploads/payment_gateways/' . $gateway->logo));
            }
        }

        $gateway->update($updateData);

        Artisan::call('view:clear');
        Artisan::call('config:clear');

        return redirect()->route('payment.getway.setting')->with('success', 'Payment gateway updated successfully.');
    }

    public function global_fees(Request $request)
    {
        $vatPercent = GeneralSetting::where('key', 'vat_percent')->first();
        $commission = GeneralSetting::where('key', 'vendor_commission')->first();
        $pgFeePercent = GeneralSetting::where('key', 'pg_fee_percent')->first();

        // Dynamic Shipping Rates (India)
        $shippingLocal = GeneralSetting::where('key', 'shipping_local')->first();
        $shippingWithinState = GeneralSetting::where('key', 'shipping_within_state')->first();
        $shippingInterstate = GeneralSetting::where('key', 'shipping_interstate')->first();

        // Free Delivery Minimum Amounts
        $freeDeliveryMin = GeneralSetting::where('key', 'free_delivery_min')->first();
        $freeDeliveryMetro = GeneralSetting::where('key', 'free_delivery_min_metro')->first();

        return view('backend/admin/setting/global-fees', compact(
            'vatPercent', 'commission', 'pgFeePercent',
            'shippingLocal', 'shippingWithinState', 'shippingInterstate',
            'freeDeliveryMin', 'freeDeliveryMetro'
        ));
    }

    public function update_global_fees(Request $request)
    {
        $fields = [
            'vat_percent', 'vendor_commission', 'pg_fee_percent',
            'shipping_local', 'shipping_within_state', 'shipping_interstate',
            'free_delivery_min', 'free_delivery_min_metro'
        ];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                GeneralSetting::updateOrCreate(
                    ['key' => $field],
                    ['value' => $request->$field ?? 0]
                );
            }
        }

        Artisan::call('optimize:clear');

        return redirect()->back()->with('success', 'Global fees updated successfully.');
    }

    public function general_setting(Request $request)
    {
        $commission = GeneralSetting::where('key', 'vendor_commission')->first();
        $pgFeePercent = GeneralSetting::where('key', 'pg_fee_percent')->first();
        
        $maintenanceMode = GeneralSetting::where('key', 'maintenance_mode')->first();
        $isMaintenance = ($maintenanceMode && $maintenanceMode->value == '1') || file_exists(base_path('maintenance.flag'));
        
        $maintenanceCustomUrl = GeneralSetting::where('key', 'maintenance_custom_url')->first();
        $currentCustomUrl = $maintenanceCustomUrl ? $maintenanceCustomUrl->value : '';
        
        $maintenanceRoles = GeneralSetting::where('key', 'maintenance_roles')->first();
        $selectedRoles = $maintenanceRoles ? json_decode($maintenanceRoles->value, true) : ['2', '3'];

        // Website Information
        $websiteName = GeneralSetting::where('key', 'website_name')->first();
        $contactEmail = GeneralSetting::where('key', 'contact_email')->first();
        $contactPhone = GeneralSetting::where('key', 'contact_phone')->first();
        $defaultCurrency = GeneralSetting::where('key', 'default_currency')->first();
        $timezone = GeneralSetting::where('key', 'timezone')->first();
        $address = GeneralSetting::where('key', 'address')->first();
        $websiteLogoDark = GeneralSetting::where('key', 'website_logo_dark')->first();
        $websiteLogoLight = GeneralSetting::where('key', 'website_logo_light')->first();
        $favicon = GeneralSetting::where('key', 'favicon')->first();

        // Referral Reward Settings
        $referralReferrerReward = GeneralSetting::where('key', 'referral_referrer_reward')->first();
        $referralReferredReward = GeneralSetting::where('key', 'referral_referred_reward')->first();
        $referralMinCart = GeneralSetting::where('key', 'referral_min_cart_value')->first();
        $referralEnabled = GeneralSetting::where('key', 'referral_enabled')->first();

        // Payout Controls
        $payoutFrequencies = GeneralSetting::where('key', 'payout_frequencies')->first();
        $selectedFrequencies = $payoutFrequencies ? json_decode($payoutFrequencies->value, true) : ['weekly', 'monthly', 'bi-weekly', 'daily'];
        return view('backend/admin/setting/add-general-setting', compact(
            'commission', 'pgFeePercent', 'isMaintenance', 'currentCustomUrl', 'selectedRoles',
            'websiteName', 'contactEmail', 'contactPhone', 'defaultCurrency',
            'timezone', 'address', 'websiteLogoDark', 'websiteLogoLight', 'favicon',
            'referralReferrerReward', 'referralReferredReward', 'referralMinCart', 'referralEnabled',
            'selectedFrequencies'
        ));
    }

    public function general_setting_update(Request $request)
    {
        // Website Information
        $fields = ['website_name', 'contact_email', 'contact_phone', 'default_currency', 'timezone', 'address'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                GeneralSetting::updateOrCreate(
                    ['key' => $field],
                    ['value' => $request->$field]
                );
            }
        }

        // Logo Upload
        if ($request->hasFile('website_logo_dark')) {
            $logoName = ImageHelper::compressImage($request->file('website_logo_dark'), 'uploads/settings');
            GeneralSetting::updateOrCreate(
                ['key' => 'website_logo_dark'],
                ['value' => $logoName]
            );
        }

        if ($request->hasFile('website_logo_light')) {
            $logoName = ImageHelper::compressImage($request->file('website_logo_light'), 'uploads/settings');
            GeneralSetting::updateOrCreate(
                ['key' => 'website_logo_light'],
                ['value' => $logoName]
            );
        }

        // Favicon Upload
        if ($request->hasFile('favicon')) {
            $faviconName = ImageHelper::compressImage($request->file('favicon'), 'uploads/settings');
            GeneralSetting::updateOrCreate(
                ['key' => 'favicon'],
                ['value' => $faviconName]
            );
        }

        // Handle Maintenance Mode
        $isMaintenance = $request->maintenance_mode == '1';
        GeneralSetting::updateOrCreate(
            ['key' => 'maintenance_mode'],
            ['value' => $isMaintenance ? '1' : '0']
        );

        if ($isMaintenance) {
            file_put_contents(base_path('maintenance.flag'), 'active');
        } else {
            if (file_exists(base_path('maintenance.flag'))) {
                unlink(base_path('maintenance.flag'));
            }
            // Also ensure Laravel's built-in maintenance mode is off
            \Illuminate\Support\Facades\Artisan::call('up');
        }

        if ($request->has('maintenance_custom_url')) {
            GeneralSetting::updateOrCreate(
                ['key' => 'maintenance_custom_url'],
                ['value' => $request->maintenance_custom_url]
            );
        }

        if ($request->has('maintenance_roles')) {
            GeneralSetting::updateOrCreate(
                ['key' => 'maintenance_roles'],
                ['value' => json_encode($request->maintenance_roles)]
            );
        } else {
            // If none selected, default to both if maintenance is on, or empty if we want to be explicit
            GeneralSetting::updateOrCreate(
                ['key' => 'maintenance_roles'],
                ['value' => json_encode([])]
            );
        }

        // Handle Vendor Commission
        if ($request->has('vendor_commission')) {
             GeneralSetting::updateOrCreate(
                ['key' => 'vendor_commission'],
                ['value' => $request->vendor_commission]
            );
        }

        // Handle PG Fee Percent
        if ($request->has('pg_fee_percent')) {
            GeneralSetting::updateOrCreate(
                ['key' => 'pg_fee_percent'],
                ['value' => $request->pg_fee_percent ?? 0]
            );
        }

        // Referral Reward Settings
        if ($request->has('referral_referrer_reward')) {
            GeneralSetting::updateOrCreate(
                ['key' => 'referral_referrer_reward'],
                ['value' => $request->referral_referrer_reward ?? 200]
            );
        }
        if ($request->has('referral_referred_reward')) {
            GeneralSetting::updateOrCreate(
                ['key' => 'referral_referred_reward'],
                ['value' => $request->referral_referred_reward ?? 100]
            );
        }
        if ($request->has('referral_min_cart_value')) {
            GeneralSetting::updateOrCreate(
                ['key' => 'referral_min_cart_value'],
                ['value' => $request->referral_min_cart_value ?? 1000]
            );
        }
        GeneralSetting::updateOrCreate(
            ['key' => 'referral_enabled'],
            ['value' => $request->referral_enabled == '1' ? '1' : '0']
        );

        // Handle Payout Frequencies
        if ($request->has('payout_frequencies')) {
            GeneralSetting::updateOrCreate(
                ['key' => 'payout_frequencies'],
                ['value' => json_encode($request->payout_frequencies)]
            );
        } else {
            GeneralSetting::updateOrCreate(
                ['key' => 'payout_frequencies'],
                ['value' => json_encode([])]
            );
        }

        // Clear cache to reflect changes
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Settings updated successfully.']);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function enable_maintenance(Request $request)
    {
        GeneralSetting::updateOrCreate(
            ['key' => 'maintenance_mode'],
            ['value' => '1']
        );

        // Create hardcoded flag file
        file_put_contents(base_path('maintenance.flag'), 'active');

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');

        return redirect()->back()->with('success', 'Maintenance mode enabled successfully.');
    }

    public function disable_maintenance()
    {
        GeneralSetting::updateOrCreate(
            ['key' => 'maintenance_mode'],
            ['value' => '0']
        );
        
        // Remove hardcoded flag file
        if (file_exists(base_path('maintenance.flag'))) {
            unlink(base_path('maintenance.flag'));
        }

        // Also ensure Laravel's built-in maintenance mode is off
        \Illuminate\Support\Facades\Artisan::call('up');

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        
        return redirect()->back()->with('success', 'Maintenance mode disabled successfully.');
    }

    public function payment_getway_setting(Request $request)
    {
        return view('backend/admin/setting/payment-getway-setting');
    }

    public function email_setting(Request $request)
    {
        $email = EmailSetting::first();
        if (!$email) {
            $email = new EmailSetting();
        }
        return view('backend/admin/setting/add-email-setting', compact('email'));
    }

    public function email_setting_update(Request $request)
    {
        $request->validate([
            'mail_driver' => 'required',
            'mail_host' => 'required',
            'mail_port' => 'required',
            'mail_username' => 'required',
            'mail_password' => 'required',
            'mail_encryption' => 'required',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required',
        ]);

        $email = EmailSetting::first();
        $data = $request->all();
        $data['status'] = 1;
        $data['use_alternate_smtp'] = $request->use_alternate_smtp ?? false;
        if ($email) {
            $email->update($data);
        } else {
            EmailSetting::create($data);
        }

        Artisan::call('optimize:clear');

        return redirect()->back()->with('success', 'Email Setting updated successfully');
    }

    public function test_email(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        $success = \App\Helpers\EmailHelper::send(
            $request->test_email,
            'SMTP Test Email',
            '<h1>SMTP Configuration Successful!</h1><p>If you are receiving this email, your SMTP settings are working correctly.</p>'
        );

        if ($success) {
            return redirect()->back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } else {
            return redirect()->back()->with('error', 'Failed to send test email. Please check your SMTP settings and Laravel logs.');
        }
    }

    public function sms_setting(Request $request)
    {
        $sms = SmsSetting::where('status', 1)->first();
        return view('backend/admin/setting/add-sms-setting', compact('sms'));
    }

    public function sms_setting_update(Request $request)
    {
        $request->validate([
            'sms_gateway' => 'required',
            'api_key' => 'required',
            'api_secret' => 'required',
            'from_number' => 'required',
        ]);

        $sms = SmsSetting::where('status', 1)->first();
        if ($sms) {
            $sms->update($request->all());
        } else {
            SmsSetting::create($request->all());
        }

        return redirect()->back()->with('success', 'SMS Setting updated successfully');
    }

    public function notification_setting(Request $request)
    {
        $notification = NotificationSetting::first();
        return view('backend/admin/setting/add-notification-setting', compact('notification'));
    }

    public function notification_setting_update(Request $request)
    {
        $fields = [
            'fcm_server_key', 'fcm_sender_id',
            'firebase_api_key', 'firebase_auth_domain', 'firebase_project_id',
            'firebase_storage_bucket', 'firebase_messaging_sender_id', 'firebase_app_id',
            'status', 'fcm_vapid_key', 'firebase_service_account', 'measurementId'
        ];

        $updateData = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        if (!empty($updateData)) {
            NotificationSetting::updateOrCreate(['id' => 1], $updateData);
        }

        Artisan::call('optimize:clear');

        return redirect()->back()->with('success', 'Notification settings updated successfully.');
    }

    public function company_info(Request $request)
    {
        $company_name = GeneralSetting::where('key', 'company_name')->first();
        $registration_number = GeneralSetting::where('key', 'registration_number')->first();
        $registered_at = GeneralSetting::where('key', 'registered_at')->first();
        $registered_office = GeneralSetting::where('key', 'registered_office')->first();
        $branch_office = GeneralSetting::where('key', 'branch_office')->first();
        $pan_vat_number = GeneralSetting::where('key', 'pan_vat_number')->first();
        $customer_support_email = GeneralSetting::where('key', 'customer_support_email')->first();
        $docscp_listing_number = GeneralSetting::where('key', 'docscp_listing_number')->first();

        return view('backend/admin/setting/add-company-info', compact(
            'company_name', 'registration_number', 'registered_at',
            'registered_office', 'branch_office', 'pan_vat_number',
            'customer_support_email', 'docscp_listing_number'
        ));
    }

    public function company_info_update(Request $request)
    {
        $companyFields = [
            'company_name',
            'registration_number',
            'registered_at',
            'registered_office',
            'branch_office',
            'pan_vat_number',
            'customer_support_email',
            'docscp_listing_number'
        ];

        foreach ($companyFields as $field) {
            if ($request->has($field)) {
                GeneralSetting::updateOrCreate(
                    ['key' => $field],
                    ['value' => $request->$field ?? '']
                );
            }
        }

        return redirect()->route('company.info')->with('success', 'Company information updated successfully.');
    }
}
