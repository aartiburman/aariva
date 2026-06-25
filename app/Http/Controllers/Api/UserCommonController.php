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
        $lang = $request->get('lang', app()->getLocale());
        $categories = Category::where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($categories as $category) {
            $category->image = ImageHelper::getCategoryImage($category->image);
            $category->name = $category->{"name_$lang"} ?? $category->name;
            $category->slug = $category->{"slug_$lang"} ?? $category->slug;
        }

        return response()->json([
            'status' => true,
            'data'   => $categories
        ], 200);
    }


    public function get_subcategories(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());

        $subcategories = SubCategory::where('sub_categories.is_active', 1)
            ->leftJoin('categories', 'sub_categories.category_id', '=', 'categories.id')
            ->select(
                'sub_categories.*',
                'categories.name as category_name',
                'categories.name_ar as category_name_ar',
                'categories.name_ne as category_name_ne'
            )
            ->orderBy('sub_categories.id', 'DESC')
            ->get();

        foreach ($subcategories as $subcategory) {

            /* -------------------------
           IMAGE
        ------------------------- */
            $subcategory->image = ImageHelper::getSubCategoryImage($subcategory->image);

            /* -------------------------
           DYNAMIC LANGUAGE FIELDS
        ------------------------- */
            $subcategory->name = $subcategory->{"name_$lang"} ?? $subcategory->name;
            $subcategory->slug = $subcategory->{"slug_$lang"} ?? $subcategory->slug;
            $subcategory->description = $subcategory->{"description_$lang"} ?? $subcategory->description;
            
            // Category name translation
            $subcategory->category_name = $subcategory->{"category_name_$lang"} ?? $subcategory->category_name;
            
            // Remove helper fields
            unset($subcategory->category_name_ar, $subcategory->category_name_ne);
        }

        return response()->json([
            'status' => true,
            'lang'   => $lang,
            'data'   => $subcategories
        ], 200);
    }

    public function get_childcategories(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $childcategories = ChildCategory::where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($childcategories as $child) {
            $child->name = $child->{"name_$lang"} ?? $child->name;
            $child->slug = $child->{"slug_$lang"} ?? $child->slug;
        }

        return response()->json([
            'status' => true,
            'data'   => $childcategories
        ], 200);
    }

    
    public function get_brands(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $brands = Brand::where('is_active', 1)
            ->select('*')->orderBy('id','DESC')->get();

        foreach ($brands as $key => $value) {
            $value->logo = ImageHelper::getBrandImage($value->logo);
            $value->name = $value->{"name_$lang"} ?? $value->name;
            $value->description = $value->{"description_$lang"} ?? $value->description;
        }

        return response()->json([
            'status' => true,
            'data'   => $brands
        ], 200);
    }

     public function get_terms_and_condition(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $content = TermsAndCondition::first();

        if ($content) {
            $content = [
                'title' => $content->{"title_$lang"} ?? $content->title,
                'content' => $content->{"content_$lang"} ?? $content->content,
            ];
        }

        return response()->json([
            'status' => true,
            'content'   =>$content,
        ], 200);
    }

      public function get_privacy_policy(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $content = PrivacyPolicy::where('status', 1)->first();

        if ($content) {
            $content = [
                'title' => $content->{"title_$lang"} ?? $content->title,
                'content' => $content->{"content_$lang"} ?? $content->content,
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $content
        ], 200);
    }

    public function get_about_us(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $content = AboutUs::where('status', 1)->first();

        if ($content) {
            $content = [
                'title' => $content->{"title_$lang"} ?? $content->title,
                'content' => $content->{"content_$lang"} ?? $content->content,
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $content
        ], 200);
    }

    public function get_faqs(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $faqs = Faq::where('status', 1)->get();

        foreach ($faqs as $faq) {
            $faq->question = $faq->{"question_$lang"} ?? $faq->question;
            $faq->answer = $faq->{"answer_$lang"} ?? $faq->answer;
            
            // Remove multi-language fields to keep response clean
            unset($faq->question_ar, $faq->question_ne, $faq->answer_ar, $faq->answer_ne);
        }

        return response()->json([
            'status' => true,
            'data'   => $faqs
        ], 200);
    }

    public function get_countries(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $countries = Country::where('is_active', 1)->get();
        
        foreach ($countries as $country) {
            $country->name = $country->{"name_$lang"} ?? $country->name;
        }

        return response()->json([
            'status' => true,
            'data' => $countries
        ], 200);
    }

    public function get_states(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $states = State::where('is_active', 1);
        if ($request->has('country_id')) {
            $states->where('country_id', $request->country_id);
        }
        $states = $states->get();

        foreach ($states as $state) {
            $state->name = $state->{"name_$lang"} ?? $state->name;
        }

        return response()->json([
            'status' => true,
            'data' => $states
        ], 200);
    }

    public function get_cities(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $cities = City::where('is_active', 1);
        if ($request->has('state_id')) {
            $cities->where('state_id', $request->state_id);
        }
        if ($request->has('country_id')) {
            $cities->where('country_id', $request->country_id);
        }
        $cities = $cities->get();

        foreach ($cities as $city) {
            $city->name = $city->{"name_$lang"} ?? $city->name;
        }

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
        $lang = $request->get('lang', app()->getLocale());
        $policies = VendorPolicy::where('status', 1)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($policies as $policy) {
            $policy->title = $policy->{"title_$lang"} ?? $policy->title;
            $policy->content = $policy->{"content_$lang"} ?? $policy->content;
            
            // Remove helper fields
            unset($policy->title_ar, $policy->title_ne, $policy->content_ar, $policy->content_ne);
        }

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
                'company_name' => $company_name->value ?? 'Nepoora Store Pvt. Ltd.',
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
