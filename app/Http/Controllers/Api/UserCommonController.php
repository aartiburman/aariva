<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Models\Banner;
use Carbon\Carbon;
use App\Models\PrivacyPolicy;
use App\Models\TermsAndCondition;
use App\Models\AboutUs;
use App\Models\Faq;
use App\Models\Coupon;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\PaymentGateway;
use App\Models\VendorPolicy;
use Stichoza\GoogleTranslate\GoogleTranslate;



class UserCommonController extends Controller
{
    public function get_categories(Request $request)
    {
        $categories = Category::where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($categories as $category) {
            $category->image = ImageHelper::getCategoryImage($category->image);
        }

        return response()->json([
            'status' => true,
            'data'   => $categories
        ], 200);
    }


    public function get_subcategories(Request $request)
    {
        $subcategories = SubCategory::where('subcategories.is_active', 1)
            ->leftJoin('categories', 'subcategories.category_id', '=', 'categories.id')
            ->select(
                'subcategories.*',
                'categories.name as category_name'
            )
            ->orderBy('subcategories.id', 'DESC')
            ->get();

        foreach ($subcategories as $subcategory) {
            $subcategory->image = ImageHelper::getSubCategoryImage($subcategory->image);
        }

        return response()->json([
            'status' => true,
            'data'   => $subcategories
        ], 200);
    }

    public function get_childcategories(Request $request)
    {
        $childcategories = ChildCategory::where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $childcategories
        ], 200);
    }

    
    public function get_brands(Request $request)
    {
        $brands = Brand::where('is_active', 1)
            ->select('*')->orderBy('id','DESC')->get();

        foreach ($brands as $key => $value) {
            $value->logo = ImageHelper::getBrandImage($value->logo);
        }

        return response()->json([
            'status' => true,
            'data'   => $brands
        ], 200);
    }

     public function get_terms_and_condition(Request $request)
    {
        $content = TermsAndCondition::first();

        return response()->json([
            'status' => true,
            'content'   =>$content,
        ], 200);
    }

      public function get_privacy_policy(Request $request)
    {
        $content = PrivacyPolicy::where('status', 1)->first();

        return response()->json([
            'status' => true,
            'data'   => $content
        ], 200);
    }

    public function get_about_us(Request $request)
    {
        $content = AboutUs::where('status', 1)->first();

        return response()->json([
            'status' => true,
            'data'   => $content
        ], 200);
    }

    public function get_faqs(Request $request)
    {
        $faqs = Faq::where('status', 1)->get();

        return response()->json([
            'status' => true,
            'data'   => $faqs
        ], 200);
    }

    public function get_countries(Request $request)
    {
        $countries = Country::where('is_active', 1)->get();

        return response()->json([
            'status' => true,
            'data' => $countries
        ], 200);
    }

    public function get_states(Request $request)
    {
        $states = State::where('is_active', 1);
        if ($request->has('country_id')) {
            $states->where('country_id', $request->country_id);
        }
        $states = $states->get();

        return response()->json([
            'status' => true,
            'data' => $states
        ], 200);
    }

    public function get_cities(Request $request)
    {
        $cities = City::where('is_active', 1);
        if ($request->has('state_id')) {
            $cities->where('state_id', $request->state_id);
        }
        if ($request->has('country_id')) {
            $cities->where('country_id', $request->country_id);
        }
        $cities = $cities->get();

        return response()->json([
            'status' => true,
            'data' => $cities
        ], 200);
    }

    public function get_payment_methods(Request $request)
    {
        $gateways = PaymentGateway::select('id', 'name','slug', 'image', 'logo')->where('is_active', 1)->where('status', 1)->get();

        foreach ($gateways as $gateway) {
            $gateway->image = ImageHelper::getPaymentGatewayImage($gateway->image);
            $gateway->logo = ImageHelper::getPaymentGatewayLogo($gateway->logo);
        }

        return response()->json([
            'status' => true,
            'data' => $gateways
        ], 200);
    }

    public function get_payment_method_detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:payment_gateways,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.validation_error'),
                'errors' => $validator->errors()
            ], 422);
        }

        $gateway = PaymentGateway::find($request->id);
        $gateway->image = ImageHelper::getPaymentGatewayImage($gateway->image);
        $gateway->logo = ImageHelper::getPaymentGatewayLogo($gateway->logo);

        return response()->json([
            'status' => true,
            'data' => $gateway
        ], 200);
    }

    public function get_vendor_policies(Request $request)
    {
        $policies = VendorPolicy::where('status', 1)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $policies
        ], 200);
    }

    public function get_company_info(Request $request)
    {
        $company_name = \App\Models\GeneralSetting::where('key', 'company_name')->first();
        $registration_number = \App\Models\GeneralSetting::where('key', 'registration_number')->first();
        $registered_at = \App\Models\GeneralSetting::where('key', 'registered_at')->first();
        $registered_office = \App\Models\GeneralSetting::where('key', 'registered_office')->first();
        $branch_office = \App\Models\GeneralSetting::where('key', 'branch_office')->first();
        $pan_vat_number = \App\Models\GeneralSetting::where('key', 'pan_vat_number')->first();
        $customer_support_email = \App\Models\GeneralSetting::where('key', 'customer_support_email')->first();
        $docscp_listing_number = \App\Models\GeneralSetting::where('key', 'docscp_listing_number')->first();

        return response()->json([
            'status' => true,
            'data' => [
                'company_name' => $company_name->value ?? 'Aariva Store Pvt. Ltd.',
                'registration_number' => $registration_number->value ?? '',
                'registered_at' => $registered_at->value ?? '',
                'registered_office' => $registered_office->value ?? '',
                'branch_office' => $branch_office->value ?? '',
                'pan_vat_number' => $pan_vat_number->value ?? '',
                'customer_support_email' => $customer_support_email->value ?? '',
                'docscp_listing_number' => $docscp_listing_number->value ?? '',
            ]
        ], 200);
    }
}
