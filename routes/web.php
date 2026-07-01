<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Admin\HomeController;
use  App\Http\Controllers\Admin\ProductController;
use  App\Http\Controllers\Admin\CategoryController;
use  App\Http\Controllers\Admin\SubcategoryController;
use  App\Http\Controllers\Admin\ChildCategoryController;
use  App\Http\Controllers\Admin\BrandController;
use  App\Http\Controllers\Admin\AdminAuthController;
use  App\Http\Controllers\Admin\EmailController;
use  App\Http\Controllers\Admin\MyVendorsController;
use  App\Http\Controllers\Admin\ReportController;
use  App\Http\Controllers\Admin\BannerController;
use  App\Http\Controllers\Auth\LoginController;
use  App\Http\Controllers\Frontend\Template1\WebController;
use  App\Http\Controllers\Frontend\Template1\UserController;
use  App\Http\Controllers\Admin\LanguageController;
use  App\Http\Controllers\Admin\BlogController;
use  App\Http\Controllers\Admin\ContactDetailController;
use  App\Http\Controllers\Admin\OfferController;
use  App\Http\Controllers\Admin\CouponController;
use  App\Http\Controllers\Admin\CampaignController;
use  App\Http\Controllers\Vendor\CampaignPageController;
use  App\Http\Controllers\Admin\LocationController;
use  App\Http\Controllers\Admin\AdminSettingController;
use  App\Http\Controllers\Admin\OrderController;
use  App\Http\Controllers\Admin\RefundController as AdminRefundController;
use  App\Http\Controllers\Vendor\RefundController as VendorRefundController;
use  App\Http\Controllers\Vendor\VendorProfileController;
use  App\Http\Controllers\Vendor\VendorPolicyController;
use  App\Http\Controllers\NewHomeController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\PayPalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AboutUsController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Auth\SessionController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\WebPagesController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\OrderNoteController;

Route::get('/pay/{order_reference_id}', [POSController::class, 'showPaymentPage'])->name('pos.payment');

// Payment Result Pages
Route::get('/payment-success', [WebPagesController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment-failure', [WebPagesController::class, 'paymentFailure'])->name('payment.failure');


//    Route::get('/', function () {
//        return response('OK', 200);
//    });

Route::get('change-language/{lang}', [LanguageController::class, 'changeLanguage'])->name('change.language');
Route::get('change-country/{country}', [LanguageController::class, 'changeCountry'])->name('change.country');

// Frontend static template pages
require __DIR__ . '/frontend.php';

// Auth::routes();
Route::get('error-403', [HomeController::class, 'error_403'])->name('error.403');
Route::get('forgot-password', [AdminAuthController::class, 'forgot_password'])->name('forgot.password');
Route::post('send-otp', [AdminAuthController::class, 'send_otp'])->name('send.otp');
Route::get('verify-otp-form', [AdminAuthController::class, 'verify_otp_form'])->name('verify.otp.form');
Route::post('otp-match', [AdminAuthController::class, 'otp_match'])->name('otp.match');
Route::get('reset-password-form', [AdminAuthController::class, 'reset_password_form'])->name('reset.password.form');
Route::post('reset-password', [AdminAuthController::class, 'reset_password'])->name('reset.password');



    Route::get('/admin', [LoginController::class, 'showLoginForm'])->name('admin.login');


Route::middleware(['auth', 'role:1'])->group(function () {
    Route::get('/admin-dashboard', [HomeController::class, 'admin_dashboard'])->name('admin.dashboard');
    Route::get('shipping-zone', [HomeController::class, 'shipping_zone'])->name('shipping.zone');
    Route::post('shipping-zone-store', [HomeController::class, 'shipping_zone_store'])->name('shipping.zone.store');

    // brand
    Route::match(['get', 'post'], 'brand-list', [BrandController::class, 'brand_list'])->name('brand.list');
    Route::get('add-brand', [BrandController::class, 'add_brand'])->name('add.brand');
    Route::post('store-brand', [BrandController::class, 'store_brand'])->name('store.brand');
    Route::get('edit-brand/{slug}', [BrandController::class, 'edit_brand'])->name('edit.brand');
    Route::post('change-brand-status', [BrandController::class, 'change_brand_status'])->name('change.brand.status');
    Route::put('update-brand', [BrandController::class, 'update_brand'])->name('update.brand');
    Route::post('delete-brand', [BrandController::class, 'delete_brand'])->name('delete.brand');
    Route::post('bulk-delete-brand', [BrandController::class, 'bulk_delete_brand'])->name('bulk.delete.brand');
    Route::get('export-brands', [BrandController::class, 'export_brands'])->name('export.brands');

    // banner
    Route::get('banner-list', [BannerController::class, 'banner_list'])->name('banner.list');
    Route::get('add-banner', [BannerController::class, 'add_banner'])->name('add.banner');
    Route::post('store-banner', [BannerController::class, 'store_banner'])->name('store.banner');
    Route::get('edit-banner/{id}', [BannerController::class, 'edit_banner'])->name('edit.banner');
    Route::post('update-banner', [BannerController::class, 'update_banner'])->name('update.banner');
    Route::post('delete-banner', [BannerController::class, 'delete_banner'])->name('delete.banner');
    Route::post('delete-banner-image', [BannerController::class, 'delete_banner_image'])->name('delete.banner.image');
    Route::post('change-banner-status', [BannerController::class, 'change_banner_status'])->name('change.banner.status');
    Route::post('bulk-delete-banner', [BannerController::class, 'bulk_delete_banner'])->name('bulk.delete.banner');
    Route::post('bulk-banner-status', [BannerController::class, 'bulk_banner_status'])->name('bulk.banner.status');
    Route::match(['get', 'post'], 'export-banners', [BannerController::class, 'export_banners'])->name('export.banners');

    
    Route::get('promotions/expire', function () {
        Artisan::call('promotions:expire');
        $out = trim(Artisan::output());
        if (request()->expectsJson()) {
            return response()->json(['status' => true, 'message' => $out ?: 'Promotions expired']);
        }
        return redirect()->back()->with('success', $out ?: 'Promotions expired');
    })->name('promotions.expire');




    // offers
    Route::match(['get', 'post'], 'offer-list', [OfferController::class, 'index'])->name('offer.list');
    Route::get('add-offer', [OfferController::class, 'create'])->name('add.offer');
    Route::post('store-offer', [OfferController::class, 'store'])->name('store.offer');
    Route::get('edit-offer/{id}', [OfferController::class, 'edit'])->name('edit.offer');
    Route::post('update-offer', [OfferController::class, 'update'])->name('update.offer');
    Route::post('delete-offer', [OfferController::class, 'destroy'])->name('offer.delete');
    Route::post('change-offer-status', [OfferController::class, 'change_status'])->name('change.offer.status');
    Route::post('bulk-delete-offer', [OfferController::class, 'bulk_delete'])->name('bulk.delete.offer');
    Route::get('export-offers', [OfferController::class, 'export_offers'])->name('export.offers');

    // coupons
    Route::match(['get', 'post'], 'coupons-list', [CouponController::class, 'index'])->name('coupons.list');
    Route::get('coupons-create', [CouponController::class, 'create'])->name('coupons.create');
    Route::post('coupons-store', [CouponController::class, 'store'])->name('coupons.store');
    Route::get('coupons-edit/{id}', [CouponController::class, 'edit'])->name('coupons.edit');
    Route::post('coupons-update', [CouponController::class, 'update'])->name('coupons.update');

    Route::post('delete-coupon', [CouponController::class, 'delete'])->name('delete.coupon');
    Route::post('coupons-delete-multiple', [CouponController::class, 'delete_multiple'])->name('coupons.delete.multiple');
    Route::post('export-coupons', [CouponController::class, 'export_multiple'])->name('coupons.export');
    Route::post('change-coupons-status', [CouponController::class, 'status'])->name('change.coupons.status');
    Route::post('export-coupons', [CouponController::class, 'export'])->name('coupons.export');
    Route::post('export-coupons-multiple', [CouponController::class, 'export_multiple'])->name('coupons.export.multiple');
    // campaigns (JSON endpoints; UI can be added later)
    Route::get('campaign-list', [CampaignController::class, 'index'])->name('campaign.list');
    Route::get('add-campaign', [CampaignController::class, 'create'])->name('campaign.add');
    Route::post('store-campaign', [CampaignController::class, 'store'])->name('campaign.store');
    Route::post('update-campaign/{id}', [CampaignController::class, 'update'])->name('campaign.update');
    Route::post('delete-campaign/{id}', [CampaignController::class, 'destroy'])->name('campaign.delete');
    Route::post('change-campaign-status', [CampaignController::class, 'change_status'])->name('campaign.change.status');
    Route::post('close-all-campaigns', [CampaignController::class, 'close_all'])->name('campaign.close.all');
    // campaign vendor approvals
    Route::get('campaign/{id}/vendor-requests', [CampaignController::class, 'vendor_requests'])->name('campaign.vendor.requests');
    Route::get('campaign/{id}/request-vendors', [CampaignController::class, 'vendor_requests_page'])->name('campaign.vendor.requests.page');
    Route::post('campaign/{id}/vendor-bulk-action', [CampaignController::class, 'vendor_bulk_action'])->name('campaign.vendor.bulk-action');
    Route::post('campaign/{campaignId}/vendor/{vendorId}/approve', [CampaignController::class, 'approve_vendor'])->name('campaign.vendor.approve');
    Route::post('campaign/{campaignId}/vendor/{vendorId}/reject', [CampaignController::class, 'reject_vendor'])->name('campaign.vendor.reject');
    // campaign product approvals
    Route::get('campaign/{id}/product-requests', [CampaignController::class, 'product_requests_page'])->name('campaign.product.requests');
    Route::post('campaign/{campaignId}/product/{productId}/approve', [CampaignController::class, 'approve_product'])->name('campaign.product.approve');
    Route::post('campaign/{campaignId}/product/{productId}/reject', [CampaignController::class, 'reject_product'])->name('campaign.product.reject');
    Route::post('campaign/{campaignId}/product/{productId}/delete', [CampaignController::class, 'delete_product_request'])->name('campaign.product.delete');
    Route::post('campaign/{id}/products/bulk-action', [CampaignController::class, 'bulk_action_products'])->name('campaign.product.bulk-action');
    // About Us
    Route::get('about-us-list', [AboutUsController::class, 'index'])->name('about.us.list');
    Route::get('about-us-add', [AboutUsController::class, 'add'])->name('about.us.add');
    Route::get('about-us-edit/{id}', [AboutUsController::class, 'edit'])->name('about.us.edit');
    Route::post('about-us-store', [AboutUsController::class, 'store'])->name('about.us.store');
    Route::post('about-us-delete', [AboutUsController::class, 'delete'])->name('about.us.delete');

    // FAQ
    Route::match(['get', 'post'], 'faq-list', [FaqController::class, 'index'])->name('faq.list');
    Route::get('faq-add', [FaqController::class, 'add'])->name('faq.add');
    Route::get('faq-edit/{id}', [FaqController::class, 'edit'])->name('faq.edit');
    Route::post('faq-store', [FaqController::class, 'store'])->name('faq.store');
    Route::post('faq-delete', [FaqController::class, 'delete'])->name('faq.delete');

    // Blogs
    Route::get('blog-list', [BlogController::class, 'index'])->name('admin.blog.index');
    Route::get('blog-add', [BlogController::class, 'add'])->name('admin.blog.add');
    Route::post('blog-store', [BlogController::class, 'store'])->name('admin.blog.store');
    Route::get('blog-edit/{id}', [BlogController::class, 'edit'])->name('admin.blog.edit');
    Route::post('blog-update/{id}', [BlogController::class, 'update'])->name('admin.blog.update');
    Route::get('blog-delete/{id}', [BlogController::class, 'delete'])->name('admin.blog.delete');
    Route::post('blog-update-status', [BlogController::class, 'update_status'])->name('admin.blog.update.status');

    // contact details
    Route::get('contact-details-list', [ContactDetailController::class, 'index'])->name('contact.detail.list');
    Route::get('add-contact-detail', [ContactDetailController::class, 'create'])->name('add.contact.detail');
    Route::post('store-contact-detail', [ContactDetailController::class, 'store'])->name('store.contact.detail');
    Route::get('edit-contact-detail/{id}', [ContactDetailController::class, 'edit'])->name('edit.contact.detail');
    Route::post('update-contact-detail', [ContactDetailController::class, 'update'])->name('update.contact.detail');
    Route::post('delete-contact-detail', [ContactDetailController::class, 'destroy'])->name('delete.contact.detail');
    Route::post('change-contact-detail-status', [ContactDetailController::class, 'change_status'])->name('change.contact.detail.status');
    Route::get('/get-pincode/{city}', [ContactDetailController::class, 'getPincode']);


    // category
    Route::match(['get', 'post'], 'category-list', [CategoryController::class, 'category_list'])->name('category.list');
    Route::get('add-category', [CategoryController::class, 'add_category'])->name('add.category');
    Route::post('store-category', [CategoryController::class, 'store_category'])->name('store.category');
    Route::get('edit-category/{slug}', [CategoryController::class, 'edit_category'])->name('edit.category');
    Route::post('update-category', [CategoryController::class, 'update_category'])->name('update.category');
    Route::post('delete-category', [CategoryController::class, 'delete_category'])->name('delete.category');
    Route::post('bulk-delete-category', [CategoryController::class, 'bulk_delete_category'])->name('bulk.delete.category');
    Route::post('change-category-status', [CategoryController::class, 'change_category_status'])->name('change.category.status');
    Route::get('export-categories', [CategoryController::class, 'export_categories'])->name('export.categories');



    // subcategory
    Route::match(['get', 'post'], 'subcategory-list', [SubcategoryController::class, 'subcategory_list'])->name('subcategory.list');
    Route::get('add-subcategory', [SubcategoryController::class, 'add_subcategory'])->name('add.subcategory');
    Route::post('store-subcategory', [SubcategoryController::class, 'store_subcategory'])->name('store.subcategory');
    Route::post('change-subcategory-status', [SubcategoryController::class, 'change_subcategory_status'])->name('change.subcategory.status');
    Route::get('edit-subcategory/{slug}', [SubcategoryController::class, 'edit_subcategory'])->name('edit.subcategory');
    Route::post('update-subcategory', [SubcategoryController::class, 'update_subcategory'])->name('update.subcategory');
    Route::post('delete-subcategory', [SubcategoryController::class, 'delete_subcategory'])->name('delete.subcategory');
    Route::post('bulk-delete-subcategory', [SubcategoryController::class, 'bulk_delete_subcategory'])->name('bulk.delete.subcategory');
    Route::get('export-subcategories', [SubcategoryController::class, 'export_subcategories'])->name('export.subcategories');

    // child category
    Route::match(['get', 'post'], 'child-category-list', [ChildCategoryController::class, 'child_category_list'])->name('child.category.list');
    Route::get('add-child-category', [ChildCategoryController::class, 'add_child_category'])->name('add.child.category');
    Route::post('store-child-category', [ChildCategoryController::class, 'store_child_category'])->name('store.child.category');
    Route::get('edit-child-category/{slug}',  [ChildCategoryController::class, 'edit_child_category'])->name('edit.child.category');
    Route::post('update-child-category', [ChildCategoryController::class, 'update_child_category'])->name('update.child.category');
    Route::post('delete-child-category', [ChildCategoryController::class, 'delete_child_category'])->name('delete.child.category');
    Route::post('bulk-delete-child-category', [ChildCategoryController::class, 'bulk_delete_child_category'])->name('bulk.delete.child.category');
    Route::post('change-child-category-status', [ChildCategoryController::class, 'change_child_category_status'])->name('change.child.category.status');
    Route::get('export-child-categories', [ChildCategoryController::class, 'export_child_categories'])->name('export.child.categories');


    //admins vendor route new template start
    Route::match(['get', 'post'], 'vendors-list', [MyVendorsController::class, 'vendors_list'])->name('vendors.list');
    Route::post('vendor-delete-multiple', [MyVendorsController::class, 'delete_multiple'])->name('vendor.delete.multiple');
    Route::post('vendor-export-multiple', [MyVendorsController::class, 'export_multiple'])->name('vendor.export.multiple');
    Route::get('add-vendor', [MyVendorsController::class, 'add_vendor'])->name('add.vendor');
    Route::post('store-vendor', [MyVendorsController::class, 'store_vendor'])->name('store.vendor');
    Route::post('delete-vendor', [MyVendorsController::class, 'delete_vendor'])->name('delete.vendor');
    Route::post('change-vendor-status', [MyVendorsController::class, 'change_vendor_status'])->name('vendor.change.status');
    Route::post('toggle-vendor-verified', [MyVendorsController::class, 'toggle_verified'])->name('vendor.toggle.verified');
    Route::post('change-document-status', [MyVendorsController::class, 'change_document_status'])->name('change.document.status');
    Route::post('check-email-availability', [MyVendorsController::class, 'check_email_availability'])->name('check.email.availability');
    Route::get('vendor-detail/{uqid}', [MyVendorsController::class, 'vendor_detail'])->name('vendor.detail');
    Route::get('active-vendors', [MyVendorsController::class, 'active_vendors'])->name('active.vendors');
    Route::get('reject-vendor', [MyVendorsController::class, 'reject_vendor'])->name('reject.vendor');
    Route::get('pending-vendors', [MyVendorsController::class, 'pending_vendors'])->name('pending.vendors');
    Route::get('vendor-requests', [MyVendorsController::class, 'vendor_requests'])->name('vendor.requests');
    Route::match(['get', 'post'], 'vendor-payout', [MyVendorsController::class, 'vendor_payout'])->name('vendor.payout');
    Route::get('vendor-payout.create', [MyVendorsController::class, 'vendor_payout_create'])->name('vendor.payout.create');
    Route::post('vendor-payout.store', [MyVendorsController::class, 'vendor_payout_store'])->name('vendor.payout.store');
    Route::post('vendor-payout/{id}/status', [MyVendorsController::class, 'update_payout_status'])->name('vendor.payout.status');
    Route::post('vendor-payout/{id}/mark-as-paid', [MyVendorsController::class, 'markPayoutAsPaid'])->name('vendor.payout.mark_as_paid');
    Route::get('vendor-payout/{id}', [MyVendorsController::class, 'vendor_payout_show'])->name('vendor.payout.show');

    Route::post('vendor-payout/export-selected', [MyVendorsController::class, 'export_selected_payouts'])->name('vendor.payout.export.selected');
    Route::get('vendor-edit/{id}', [MyVendorsController::class, 'vendor_edit'])->name('vendor.edit');
    Route::get('vendor-delete', [MyVendorsController::class, 'vendor_delete'])->name('vendor.delete');
    Route::post('vendor-update', [MyVendorsController::class, 'vendor_update'])->name('vendor.update');
    Route::get('export-vendors', [MyVendorsController::class, 'export_vendors'])->name('export.vendors');

    // new templete done

    // admin authenticate 
    Route::get('admin-change-password', [AdminAuthController::class, 'admin_change_password'])->name('admin.change.password');
    Route::get('my-account', [AdminAuthController::class, 'my_account'])->name('my.account');
    Route::get('download-vendors', [HomeController::class, 'download_vendors'])->name('download.vendors');

   
    // profile
    Route::get('admin-profile', [AdminAuthController::class, 'admin_profile'])->name('admin.profile');
    Route::post('admin-profile-update', [AdminAuthController::class, 'admin_profile_update'])->name('admin.profile.update');
    // Route::get('change-password', [AdminAuthController::class, 'change_password'])->name('change.password');
    Route::post('admin-update-password', [AdminAuthController::class, 'admin_update_password'])->name('admin.update.password');

    // shipping Address
    Route::get('shipping-address', [HomeController::class, 'shipping_address'])->name('shipping.address');
    Route::get('shipping-zone', [HomeController::class, 'shipping_zone'])->name('shipping.zone');
    Route::get('add-shipping-zone', [HomeController::class, 'add_shipping_zone'])->name('add.shipping.zone');
    Route::get('store-shipping-zone', [HomeController::class, 'store_shipping_zone'])->name('store.shipping.zone');


    // email
    Route::get('create-email-template', [EmailController::class, 'create_email_template'])->name('create.email.template');
    Route::get('add-email-template', [EmailController::class, 'add_email_template'])->name('add.email.template');
    Route::post('store-email-template', [EmailController::class, 'store_email_template'])->name('store.email.template');
    Route::get('add-tax-rates', [HomeController::class, 'add_tax_rates'])->name('add.tax.rate');
    Route::get('tax-rates', [HomeController::class, 'tax_rates'])->name('tax.rates');
    Route::get('store-tax-rate', [HomeController::class, 'store_tax_rate'])->name('store.tax.rate');

    // report   
    Route::match(['get', 'post'], 'sales-report', [ReportController::class, 'sales_report'])->name('sales.report');
    Route::match(['get', 'post'], 'vendor-report', [ReportController::class, 'vendor_report'])->name('vendor.report');
    Route::match(['get', 'post'], 'kyc-report', [ReportController::class, 'kyc_report'])->name('kyc.report');
    Route::match(['get', 'post'], 'product-report', [ReportController::class, 'product_report'])->name('product.report');

    // Refund Request
    Route::match(['get', 'post'], 'admin/refund-requests', [AdminRefundController::class, 'getAllRefunds'])->name('admin.refund.list');
    Route::get('admin/refund-requests/{id}', [AdminRefundController::class, 'show'])->name('admin.refund.show');
    Route::post('admin/refund-action', [AdminRefundController::class, 'adminAction'])->name('admin.refund.action');

    //  add Size category new template start
    Route::get('add-product-size-category', [ProductController::class, 'add_product_size_category'])->name('add.product.size.category');
    Route::post('store-product-size-category', [ProductController::class, 'store_product_size_category'])->name('store.product.size.category');
    Route::get('edit-product-size-category/{id}', [ProductController::class, 'edit_product_size_category'])->name('edit.product.size.category');
    Route::post('update-product-size-category', [ProductController::class, 'update_product_size_category'])->name('update.product.size.category');
    Route::post('change-product-size-category-status', [ProductController::class, 'change_product_size_category_status'])->name('change.product.size.category.status');
    Route::post('delete-product-size-category', [ProductController::class, 'delete_product_size_category'])->name('delete.product.size.category');

    Route::get('add-product-size/{id}', [ProductController::class, 'add_product_size'])->name('add.product.size');
    Route::post('store-product-size', [ProductController::class, 'store_product_size'])->name('store.product.size');
    Route::get('edit-product-size/{id}', [ProductController::class, 'edit_product_size'])->name('edit.product.size');
    Route::post('update-product-size', [ProductController::class, 'update_product_size'])->name('update.product.size');
    Route::post('change-product-size-status', [ProductController::class, 'change_product_size_status'])->name('change.product.size.status');
    Route::post('delete-product-size', [ProductController::class, 'delete_product_size'])->name('delete.product.size');
    // new template done

    // setting 

    Route::get('privacy-policy', [AdminSettingController::class, 'privacy_policy'])->name('privacy.policy.list');
    Route::get('privacy-policy-add', [AdminSettingController::class, 'add_privacy_policy'])->name('privacy.policy.add');
    Route::post('privacy-policy-store', [AdminSettingController::class, 'privacy_policy_store'])->name('privacy.policy.store');
    Route::get('privacy-policy-edit/{id}', [AdminSettingController::class, 'edit_privacy_policy'])->name('privacy.policy.edit');
    Route::post('privacy-policy-delete', [AdminSettingController::class, 'delete_privacy_policy'])->name('privacy.policy.delete');

    Route::get('terms-and-conditions', [AdminSettingController::class, 'terms_and_condition'])->name('term.and.conditions.list');
    Route::get('terms-and-conditions-add', [AdminSettingController::class, 'add_terms_and_condition'])->name('term.and.conditions.add');
    Route::post('terms-and-conditions-store', [AdminSettingController::class, 'terms_and_condition_store'])->name('term.and.conditions.store');
    Route::get('terms-and-conditions-edit/{id}', [AdminSettingController::class, 'edit_terms_and_condition'])->name('term.and.conditions.edit');
    Route::post('terms-and-conditions-delete', [AdminSettingController::class, 'delete_terms_and_condition'])->name('term.and.conditions.delete');

    Route::get('vendor-policy', [AdminSettingController::class, 'vendor_policy'])->name('vendor.policy.list');
    Route::get('vendor-policy-add', [AdminSettingController::class, 'add_vendor_policy'])->name('vendor.policy.add');
    Route::post('vendor-policy-store', [AdminSettingController::class, 'vendor_policy_store'])->name('vendor.policy.store');
    Route::get('vendor-policy-edit/{id}', [AdminSettingController::class, 'edit_vendor_policy'])->name('vendor.policy.edit');
    Route::post('vendor-policy-delete', [AdminSettingController::class, 'delete_vendor_policy'])->name('vendor.policy.delete');

    // KYC Documents
    Route::get('kyc-documents', [AdminSettingController::class, 'kyc_documents'])->name('kyc.documents.list');
    Route::post('kyc-documents-store', [AdminSettingController::class, 'kyc_document_store'])->name('kyc.documents.store');
    Route::post('kyc-documents-delete', [AdminSettingController::class, 'delete_kyc_document'])->name('kyc.documents.delete');
    Route::post('kyc-documents-status', [AdminSettingController::class, 'toggle_kyc_document_status'])->name('kyc.documents.status');

    Route::get('general-setting', [AdminSettingController::class, 'general_setting'])->name('general.setting');
    Route::post('general-setting-update', [AdminSettingController::class, 'general_setting_update'])->name('general.setting.update');
    Route::get('global-fees', [AdminSettingController::class, 'global_fees'])->name('global.fees');
    Route::post('global-fees-update', [AdminSettingController::class, 'update_global_fees'])->name('global.fees.update');
    Route::get('company-info', [AdminSettingController::class, 'company_info'])->name('company.info');
    Route::post('company-info-update', [AdminSettingController::class, 'company_info_update'])->name('company.info.update');
    Route::get('maintenance/enable', [AdminSettingController::class, 'enable_maintenance'])->name('maintenance.enable');
    Route::get('maintenance/disable', [AdminSettingController::class, 'disable_maintenance'])->name('maintenance.disable');

    Route::get('payment-getway-setting', [AdminSettingController::class, 'payment_gateway_list'])->name('payment.getway.setting');
    Route::get('payment-getway-edit/{id}', [AdminSettingController::class, 'payment_gateway_edit'])->name('payment.getway.edit');
    Route::post('payment-getway-update', [AdminSettingController::class, 'payment_gateway_update'])->name('payment.getway.update');
    Route::get('email-setting', [AdminSettingController::class, 'email_setting'])->name('email.setting');
    Route::post('email-setting-update', [AdminSettingController::class, 'email_setting_update'])->name('email.setting.update');
    Route::post('test-email', [AdminSettingController::class, 'test_email'])->name('test.email');
    Route::get('sms-setting', [AdminSettingController::class, 'sms_setting'])->name('sms.setting');
    Route::post('sms-setting-update', [AdminSettingController::class, 'sms_setting_update'])->name('sms.setting.update');

    Route::get('notification-setting', [AdminSettingController::class, 'notification_setting'])->name('notification.setting');
    Route::post('notification-setting-update', [AdminSettingController::class, 'notification_setting_update'])->name('notification.setting.update');

    // Handover PDF
    Route::get('download-handover-pdf', [WebPagesController::class, 'downloadHandoverPdf'])->name('download.handover.pdf');

    // ================= INVENTORY MANAGEMENT =================
    Route::match(['get', 'post'], 'inventory/dashboard', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
    Route::match(['get', 'post'], 'inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::post('inventory/stock-adjustment', [InventoryController::class, 'stockAdjustment'])->name('inventory.stock.adjustment');
    Route::post('inventory/update-threshold', [InventoryController::class, 'updateThreshold'])->name('inventory.update.threshold');
    Route::get('inventory/warehouses', [InventoryController::class, 'warehouses'])->name('inventory.warehouses');
    Route::post('inventory/warehouses/store', [InventoryController::class, 'storeWarehouse'])->name('inventory.warehouse.store');
    Route::put('inventory/warehouses/{id}', [InventoryController::class, 'updateWarehouse'])->name('inventory.warehouse.update');
    Route::delete('inventory/warehouses/{id}', [InventoryController::class, 'deleteWarehouse'])->name('inventory.warehouse.delete');

    // ================= CUSTOMER CRM =================
    Route::match(['get', 'post'], 'crm/dashboard', [CrmController::class, 'dashboard'])->name('crm.dashboard');
    Route::match(['get', 'post'], 'crm/customers', [CrmController::class, 'customers'])->name('crm.customers');
    Route::get('crm/customers/{id}', [CrmController::class, 'customerDetail'])->name('crm.customer.detail');
    Route::post('crm/notes', [CrmController::class, 'storeNote'])->name('crm.note.store');
    Route::delete('crm/notes/{id}', [CrmController::class, 'deleteNote'])->name('crm.note.delete');
    Route::post('crm/customers/assign-group', [CrmController::class, 'assignGroup'])->name('crm.customer.assign.group');
    Route::get('crm/abandoned-carts', [CrmController::class, 'abandonedCarts'])->name('crm.abandoned.carts');
    Route::get('crm/groups', [CrmController::class, 'groups'])->name('crm.groups');
    Route::post('crm/groups', [CrmController::class, 'storeGroup'])->name('crm.group.store');
    Route::put('crm/groups/{id}', [CrmController::class, 'updateGroup'])->name('crm.group.update');
    Route::delete('crm/groups/{id}', [CrmController::class, 'deleteGroup'])->name('crm.group.delete');

    // ================= SUPPLIER MANAGEMENT =================
    Route::match(['get', 'post'], 'suppliers', [SupplierController::class, 'index'])->name('supplier.index');
    Route::post('suppliers/store', [SupplierController::class, 'store'])->name('supplier.store');
    Route::put('suppliers/{id}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('suppliers/{id}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
    Route::get('suppliers/{id}', [SupplierController::class, 'detail'])->name('supplier.detail');
    Route::post('suppliers/link-product', [SupplierController::class, 'linkProduct'])->name('supplier.product.link');
    Route::delete('suppliers/{supplierId}/products/{productId}', [SupplierController::class, 'unlinkProduct'])->name('supplier.product.unlink');
    Route::match(['get', 'post'], 'purchase-orders', [SupplierController::class, 'purchaseOrders'])->name('supplier.purchase.orders');
    Route::post('purchase-orders/store', [SupplierController::class, 'storePurchaseOrder'])->name('supplier.purchase.order.store');
    Route::get('purchase-orders/{id}', [SupplierController::class, 'purchaseOrderDetail'])->name('supplier.purchase.order.detail');
    Route::put('purchase-orders/{id}/status', [SupplierController::class, 'updatePurchaseOrderStatus'])->name('supplier.purchase.order.status');
    Route::put('purchase-orders/{id}/receive', [SupplierController::class, 'receivePurchaseOrder'])->name('supplier.purchase.order.receive');

    // ================= WHATSAPP AUTOMATION =================
    Route::get('whatsapp/settings', [WhatsAppController::class, 'settings'])->name('whatsapp.settings');
    Route::post('whatsapp/settings', [WhatsAppController::class, 'updateSettings'])->name('whatsapp.settings.update');
    Route::match(['get', 'post'], 'whatsapp/messages', [WhatsAppController::class, 'messages'])->name('whatsapp.messages');
    Route::post('whatsapp/test-send', [WhatsAppController::class, 'sendTest'])->name('whatsapp.test.send');
    Route::post('whatsapp/order-notify/{orderId}', [WhatsAppController::class, 'sendOrderNotification'])->name('whatsapp.order.notify');

    // ================= ORDER NOTES =================
    Route::post('order-notes', [OrderNoteController::class, 'store'])->name('order.note.store');
    Route::delete('order-notes/{id}', [OrderNoteController::class, 'destroy'])->name('order.note.delete');
});

Route::get('firebase-messaging-sw.js', [NotificationController::class, 'firebase_sw']);

Route::middleware(['auth', 'role:1,2'])->group(function () {

    Route::get('get-subcategories/{category_id}',  [ChildCategoryController::class, 'getByCategory'])->name('get.subcategories');
    Route::get('get-child-categories/{subcategory_id}', [ChildCategoryController::class, 'get_child_categories'])->name('get.child.categories');
    Route::get('get-brands-by-category/{catId}', [BrandController::class, 'get_brands_by_category'])->name('get.brands.by.category');
    Route::get('get-brands-by-subcategory/{subcatId}', [BrandController::class, 'get_brands_by_subcategory'])->name('get.brands.by.subcategory');

    Route::post('create-product-variant-ajax', [ProductController::class, 'create_product_variant_ajax'])->name('create.product.variant.ajax');
    Route::post('create-size-category-ajax', [ProductController::class, 'create_size_category_ajax'])->name('create.size.category.ajax');
    Route::post('create-size-ajax', [ProductController::class, 'create_size_ajax'])->name('create.size.ajax');


  Route::get('get-brands-by-childcategory/{childcatId}', [BrandController::class, 'get_brands_by_childcategory'])->name('get.brands.by.childcategory');
    Route::get('get-states/{country_id}', [LocationController::class, 'getStates'])->name('get.states');
    Route::get('get-cities/{state_id}', [LocationController::class, 'getCities'])->name('get.cities');
    Route::get('get-sizes/{id}', [ProductController::class, 'get_sizes'])->name('get.sizes');

    // product new template start
    Route::match(['get', 'post'], 'product-list', [ProductController::class, 'product_list'])->name('product.list');
    Route::get('product-detail/{id}', [ProductController::class, 'product_detail'])->name('product.detail');
    Route::get('add-product', [ProductController::class, 'add_product'])->name('add.product');
    Route::post('store-product', [ProductController::class, 'store_product'])->name('store.product');
    Route::post('change-product-status', [ProductController::class, 'change_product_status'])->name('change.product.status');
    Route::get('edit-product/{id}', [ProductController::class, 'edit_product'])->name('edit.product');
    Route::post('update-product', [ProductController::class, 'update_product'])->name('update.product');
    Route::get('edit-variant/{id}', [ProductController::class, 'edit_variant'])->name('edit.variant');
    Route::post('update-variant', [ProductController::class, 'update_variant'])->name('update.variant');
    Route::post('store-edit-variants', [ProductController::class, 'store_edit_variants'])->name('store.edit.variants');
    Route::post('delete-variant-image', [ProductController::class, 'delete_variant_image'])->name('delete.variant.image');

    // POS routes
    Route::get('pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('pos/search-products', [POSController::class, 'searchProducts'])->name('pos.search.products');
    Route::get('pos/product-details/{id}', [POSController::class, 'getProductDetails'])->name('pos.product.details');
    Route::get('pos/history', [POSController::class, 'orderHistory'])->name('pos.history');
    Route::post('pos/place-order', [POSController::class, 'placeOrder'])->name('pos.place.order');
    Route::get('pos/invoice/{id}', [POSController::class, 'generateInvoice'])->name('pos.invoice');
    // Alias path to match JS that calls /admin/change-product-status
    Route::post('admin/change-product-status', [ProductController::class, 'change_product_status'])->name('admin.change.product.status.alias');
    Route::post('delete-variant', [ProductController::class, 'delete_variant'])->name('delete.variant');
    Route::post('delete-product-image', [ProductController::class, 'delete_product_image'])->name('delete.product.image');
    Route::post('delete-product', [ProductController::class, 'delete_product'])->name('delete.product');
    Route::post('bulk-delete-product', [ProductController::class, 'bulk_delete_product'])->name('bulk.delete.product');
    Route::post('bulk-product-status', [ProductController::class, 'bulk_product_status'])->name('bulk.product.status');
    Route::match(['get', 'post'], 'export-products', [ProductController::class, 'export_products'])->name('export.products');
    Route::get('rejected-product', [ProductController::class, 'rejected_product'])->name('rejected.product');
    Route::get('approve-product', [ProductController::class, 'approve_product'])->name('approve.product');
    Route::get('pending-product', [ProductController::class, 'pending_product'])->name('pending.product');
    Route::get('find-similar-product', [ProductController::class, 'find_similar_product'])->name('find.similar.product');

    Route::match(['get', 'post'], 'bulk-upload-product', [ProductController::class, 'bulk_upload_product'])->name('bulk.upload.product');
    Route::get('export-bulk-products', [ProductController::class, 'export_bulk_products'])->name('export.bulk.products');
    Route::get('bulk-upload-product-template', [ProductController::class, 'download_bulk_upload_product_csv'])->name('bulk.upload.product.template');
    Route::post('bulk-upload-product-store', [ProductController::class, 'store_bulk_upload_product'])->name('bulk.upload.product.store');

    Route::post('ajax-find-similar', [ProductController::class, 'ajax_find_similar'])->name('ajax.find.similar');
    Route::post('ajax-fetch-product', [ProductController::class, 'ajax_fetch_product'])->name('ajax.fetch.product');
    Route::post('create-brand', [BrandController::class, 'createBrand'])->name('create.brand');
    Route::post('create-category-ajax', [CategoryController::class, 'ajax_store_category'])->name('create.category.ajax');
    Route::post('create-subcategory-ajax', [SubcategoryController::class, 'ajax_store_subcategory'])->name('create.subcategory.ajax');
    Route::post('create-child-category-ajax', [ChildCategoryController::class, 'ajax_store_child_category'])->name('create.child.category.ajax');
    Route::post('ajax-render-product', [ProductController::class, 'ajax_render_product'])->name('ajax.render.product');

    Route::post('store-similar-product', [ProductController::class, 'store_similar_product'])->name('store.similar.product');

    // new template start


    // order

    Route::match(['get', 'post'], 'new-orders', [OrderController::class, 'new_orders'])->name('new.orders');
    Route::match(['get', 'post'], 'pending-orders', [OrderController::class, 'pending_orders'])->name('pending.orders');
    Route::match(['get', 'post'], 'confirmed-orders', [OrderController::class, 'confirmed_orders'])->name('confirmed.orders');
    Route::match(['get', 'post'], 'shipped-orders', [OrderController::class, 'shipped_orders'])->name('shipped.orders');
    Route::match(['get', 'post'], 'rejected-orders', [OrderController::class, 'rejected_orders'])->name('rejected.orders');
    Route::match(['get', 'post'], 'cancelled-orders', [OrderController::class, 'cancelled_orders'])->name('cancelled.orders');
    Route::match(['get', 'post'], 'delivered-orders', [OrderController::class, 'delivered_orders'])->name('delivered.orders');
    Route::match(['get', 'post'], 'returned-orders', [OrderController::class, 'returned_orders'])->name('returned.orders');
    Route::match(['get', 'post'], 'dispute-orders', [OrderController::class, 'dispute_orders'])->name('dispute.orders');


    Route::post('update-order-status', [OrderController::class, 'update_order_status'])->name('update.order.status');
    Route::post('bulk-update-order-status', [OrderController::class, 'bulk_update_order_status'])->name('bulk.update.order.status');

    Route::post('update-payment-status', [OrderController::class, 'update_payment_status'])->name('update.payment.status');
    Route::get('orders-details/{reference_id}', [OrderController::class, 'orders_details'])->name('orders.details');
    Route::get('orders-invoice/{reference_id}', [OrderController::class, 'orders_invoice'])->name('orders.invoice');
    Route::get('restore-order/{id}', [OrderController::class, 'restore_order'])->name('restore.order');

    // tickets
    Route::match(['get', 'post'], 'tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('tickets/{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::get('tickets/{id}/messages', [TicketController::class, 'fetchMessages'])->name('tickets.messages');
    Route::post('tickets/{id}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('tickets/{id}/escalate', [TicketController::class, 'escalate'])->name('tickets.escalate');

    // customer
    Route::match(['get', 'post'], 'my-customer-list', [HomeController::class, 'my_customer_list'])->name('my.customer.list');
    Route::get('export-my-customers', [HomeController::class, 'export_my_customers'])->name('export.my.customers');
    Route::match(['get', 'post'], 'all-customers', [HomeController::class, 'all_customers'])->name('all.customers');
    Route::get('export-customers', [HomeController::class, 'export_customers'])->name('export.customers');
    Route::get('customer-detail/{id}', [HomeController::class, 'customer_detail'])->name('customer.detail');
    Route::post('change-customer-status', [HomeController::class, 'change_customer_status'])->name('change.customer.status');

    // CRM - shared (vendors also need customer management)
    Route::get('crm/customers/{id}', [CrmController::class, 'customerDetail'])->name('crm.customer.detail');
    Route::post('crm/notes', [CrmController::class, 'storeNote'])->name('crm.note.store');

});


Route::middleware(['auth', 'role:2'])->group(function () {
    Route::get('/vendor-dashboard', [HomeController::class, 'vendor_dashboard'])->name('vendor.dashboard');
    Route::get('/vendor-performance-data', [HomeController::class, 'getVendorPerformanceData'])->name('vendor.performance.data');
    Route::get('vendor/campaigns', [CampaignPageController::class, 'index'])->name('vendor.campaigns');
    Route::post('vendor/campaigns/join', [CampaignPageController::class, 'join'])->name('vendor.campaigns.join');
    Route::get('vendor/campaign/{id}/manage-products', [CampaignPageController::class, 'manageProducts'])->name('vendor.campaign.manage.products');
    Route::post('vendor/campaign/{id}/add-products', [CampaignPageController::class, 'addProducts'])->name('vendor.campaign.add.products');
    Route::post('vendor/campaign/opt-in', [CampaignPageController::class, 'optIn'])->name('vendor.campaigns.optin');
    Route::post('vendor/campaign/opt-out', [CampaignPageController::class, 'optOut'])->name('vendor.campaigns.optout');
    Route::get('vendor/wallet', [\App\Http\Controllers\Vendor\WalletController::class, 'index'])->name('vendor.wallet');
    Route::post('add-vendor-bank-detail', [VendorProfileController::class, 'add_vendor_bank_detail'])->name('add.vendor.bank.detail');
    Route::post('vendor-policy/accept', [VendorPolicyController::class, 'accept'])->name('vendor.policy.accept');
    // vendor profile
    Route::get('vendor-profile', [VendorProfileController::class, 'vendor_profile'])->name('vendor.profile');
    Route::get('vendor-change-password', [VendorProfileController::class, 'vendor_change_password'])->name('vendor.change.password');
    Route::post('update-vendor-profile', [VendorProfileController::class, 'update_vendor_profile'])->name('update.vendor.profile');
    Route::post('update-vendor-logo', [VendorProfileController::class, 'update_vendor_logo'])->name('update.vendor.logo');
    Route::post('update-bank-proof', [VendorProfileController::class, 'update_bank_proof'])->name('update.bank.proof');
    Route::get('edit-vendor-profile', [VendorProfileController::class, 'edit_vendor_profile'])->name('edit.vendor.profile');
    Route::post('update-vendor-documents', [VendorProfileController::class, 'update_vendor_documents'])->name('vendor.documents.update');
    Route::post('store-vendor-documents', [VendorProfileController::class, 'update_vendor_documents'])->name('vendor.profile.document.store');
    Route::post('update-vendor-password', [VendorProfileController::class, 'update_vendor_password'])->name('vendor.update.password');

    // Sales Report
    Route::match(['get', 'post'], 'vendor/sales-report', [HomeController::class, 'vendor_sales_report'])->name('vendor.sales.report');

    // Support Center
    Route::get('vendor/support-center', [TicketController::class, 'index'])->name('vendor.support.center');

    Route::get('vendor/kyc-documents', [VendorProfileController::class, 'kyc_documents'])->name('vendor.kyc.documents');
    Route::get('vendor/delivery-settings', [VendorProfileController::class, 'delivery_settings'])->name('vendor.delivery.settings');
    Route::post('vendor/delivery-settings-update', [VendorProfileController::class, 'update_delivery_settings'])->name('vendor.delivery.settings.update');

    // Vendor Refund Request
    Route::get('vendor/refund-requests', [VendorRefundController::class, 'getVendorRefunds'])->name('vendor.refund.list');
    Route::get('vendor/refund-requests/{id}', [VendorRefundController::class, 'show'])->name('vendor.refund.show');
    Route::post('vendor/refund-action', [VendorRefundController::class, 'vendorAction'])->name('vendor.refund.action');

    // Vendor Payouts
    Route::match(['get', 'post'], 'vendor/payouts', [\App\Http\Controllers\Vendor\PayoutController::class, 'index'])->name('vendor.payouts');
    Route::get('vendor/payouts/{id}', [\App\Http\Controllers\Vendor\PayoutController::class, 'show'])->name('vendor.payouts.show');
    Route::post('vendor/payouts/export-selected', [\App\Http\Controllers\Vendor\PayoutController::class, 'export_selected'])->name('vendor.payouts.export.selected');
    Route::post('vendor/payouts/request', [\App\Http\Controllers\Vendor\PayoutController::class, 'requestPayout'])->name('vendor.payouts.request');
    Route::post('vendor/update-payout-frequency', [\App\Http\Controllers\Vendor\PayoutController::class, 'updateFrequency'])->name('vendor.update.payout.frequency');
    Route::post('vendor/withdraw-request', [\App\Http\Controllers\Vendor\WalletController::class, 'requestWithdrawal'])->name('vendor.withdraw.request');
});


// ======================== CUSTOMER FRONTEND ROUTES ========================
Route::group(['namespace' => 'App\Http\Controllers\Frontend\Template1', 'as' => 'frontend.'], function () {
    Route::get('/', 'HomeController@index')->name('home');

    // Auth
    Route::get('login', 'AuthController@showLoginForm')->name('login');
    Route::post('login', 'AuthController@login');
    Route::get('register', 'AuthController@showRegisterForm')->name('register');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout')->name('logout');

    // Products
    Route::get('shop', 'ProductController@index')->name('products.index');
    Route::get('product/{slug}', 'ProductController@show')->name('products.show');
    Route::get('api/products/search', 'ProductController@search')->name('products.search');

    // Cart
    Route::prefix('cart')->as('cart.')->group(function () {
        Route::get('/', 'CartController@index')->name('index');
        Route::post('add', 'CartController@add')->name('add');
        Route::post('update', 'CartController@update')->name('update');
        Route::get('remove/{id}', 'CartController@remove')->name('remove');
        Route::post('remove-by-product', 'CartController@removeByProduct')->name('remove-by-product');
        Route::get('count', 'CartController@count')->name('count');
    });

    // Wishlist
    Route::prefix('wishlist')->as('wishlist.')->group(function () {
        Route::get('/', 'WishlistController@index')->name('index');
        Route::post('toggle', 'WishlistController@toggle')->name('toggle');
        Route::get('count', 'WishlistController@count')->name('count');
    });

    // Checkout (Requires Auth)
    Route::middleware('auth')->group(function () {
        Route::prefix('checkout')->as('checkout.')->group(function () {
            Route::get('/', 'CheckoutController@index')->name('index');
            Route::post('place-order', 'CheckoutController@placeOrder')->name('place-order');
            Route::get('success/{reference_id}', 'CheckoutController@success')->name('success');
            Route::post('address/add', 'CheckoutController@addAddress')->name('address.add');
            Route::delete('address/{id}', 'CheckoutController@deleteAddress')->name('address.delete');
        });

        // User Account
        Route::prefix('my-account')->as('user.')->group(function () {
            Route::get('profile', 'UserController@profile')->name('profile');
            Route::post('profile/update', 'UserController@updateProfile')->name('profile.update');
            Route::get('orders', 'UserController@orders')->name('orders');
            Route::get('orders/{id}', 'UserController@orderDetail')->name('order-detail');
            Route::get('addresses', 'UserController@addresses')->name('addresses');
        });
    });

    // Vendor Registration
    Route::match(['get', 'post'], 'become-seller', function (Request $request) {
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['required', 'string', 'max:20'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'store_name' => ['required', 'string', 'max:255'],
                'business_name' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:500'],
                'documents.*' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,doc,docx', 'max:5120'],
            ]);

            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 2,
                'status' => 0,
                'store_name' => $request->store_name,
                'business_name' => $request->business_name,
                'address' => $request->address,
                'uqid' => 'VND-' . strtoupper(substr(md5(time()), 0, 8)),
            ]);

            // Upload documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $docId => $file) {
                    $docPath = \App\Helpers\ImageHelper::compressImage($file, 'uploads/vendor_documents');
                    \App\Models\VendorsDocument::create([
                        'vendor_id' => $user->id,
                        'document_id' => $docId,
                        'document' => $docPath,
                        'is_verify' => 0,
                    ]);
                }
            }

            // Notify Admin
            $admin_email = \App\Models\EmailSetting::where('status', 1)->value('mail_from_address') ?? 'admin@ecom.com';
            \App\Helpers\EmailHelper::send($admin_email, 'New Seller Registration: ' . $user->store_name,
                'A new seller has registered.<br><br><b>Shop Name:</b> ' . $user->store_name . '<br><b>Email:</b> ' . $user->email);
            \App\Helpers\NotificationHelper::notifyAdmins([
                'title' => 'New Seller Registration',
                'message' => 'New seller ' . $user->store_name . ' has registered.',
                'type' => 'system', 'url' => route('vendors.list'),
                'icon' => 'solar:user-bold-duotone', 'priority' => 'medium'
            ]);

            // Notify Vendor
            $appUrl = config('app.url');
            \App\Helpers\EmailHelper::send($user->email, 'Welcome to ' . config('app.name') . '! You have joined as a vendor',
                '', 'emails.registration', [
                    'owner_name' => $user->name,
                    'store_name' => $user->store_name,
                    'login_url'  => $appUrl . '/login',
                    'email'      => $user->email,
                    'password'   => $request->password
                ]);
            \App\Helpers\NotificationHelper::send($user, [
                'title' => 'Welcome to ' . config('app.name'),
                'message' => 'Your vendor account for ' . $user->store_name . ' has been successfully created. We will review your application.',
                'type' => 'system', 'url' => route('login'),
            ]);

            return redirect()->route('frontend.become-seller')->with('success', 'Registration successful! We will review your application and get back to you soon.');
        }

        $kycDocuments = \App\Models\KYC_Document::where('is_active', 1)->get();
        return view('frontend.auth.vendor-register', compact('kycDocuments'));
    })->name('become-seller');

    // Frontend Pages
    Route::get('blog', function () {
        $posts = \App\Models\Blog::where('status', 1)->with('author')->latest()->paginate(12);
        return view('frontend.pages.blog', compact('posts'));
    })->name('blog');
    Route::get('blog/{slug}', function ($slug) {
        $post = \App\Models\Blog::where('slug', $slug)->where('status', 1)->firstOrFail();
        return view('frontend.pages.blog-single', compact('post'));
    })->name('blog.show');
    Route::view('about', 'frontend.pages.about')->name('about');
    Route::view('contact', 'frontend.pages.contact')->name('contact');
    Route::view('faq', 'frontend.pages.faq')->name('faq');
    Route::view('vendors', 'frontend.pages.vendors')->name('vendors');
    Route::view('compare', 'frontend.pages.compare')->name('compare');
});

// Template1 static page routes (all converted HTML templates)
Route::get('template1/{page}', function ($page) {
    $validPages = [
        'become-a-vendor', 'blog-grid-2cols', 'blog-grid-3cols', 'blog-grid-4cols',
        'blog-grid-sidebar', 'blog-listing', 'blog-mask-grid', 'blog-mask-masonry',
        'blog-masonry-2cols', 'blog-masonry-3cols', 'blog-masonry-4cols', 'blog-masonry-sidebar',
        'coming-soon', 'error-404', 'element-accordions', 'element-alerts',
        'element-blog-posts', 'element-buttons', 'element-categories', 'element-cta',
        'element-icon-boxes', 'element-icons', 'element-instagrams', 'element-products',
        'element-tabs', 'element-testimonials', 'element-titles', 'element-typography',
        'element-vendors', 'elements', 'post-single',
        'product-accordion', 'product-default', 'product-extended', 'product-featured',
        'product-gallery', 'product-grid', 'product-masonry', 'product-section',
        'product-sticky-both', 'product-sticky-info', 'product-sticky-thumb', 'product-swatch',
        'product-variable', 'product-vertical', 'product-video', 'product-without-sidebar',
        'shop-banner-sidebar', 'shop-both-sidebar', 'shop-boxed-banner', 'shop-fullwidth-banner',
        'shop-grid-3cols', 'shop-grid-4cols', 'shop-grid-5cols', 'shop-grid-6cols',
        'shop-grid-7cols', 'shop-grid-8cols', 'shop-horizontal-filter', 'shop-infinite-scroll',
        'shop-list-sidebar', 'shop-list', 'shop-off-canvas', 'shop-right-sidebar',
        'vendor-dokan-store-grid', 'vendor-dokan-store-list', 'vendor-dokan-store',
        'vendor-wc-store-list', 'vendor-wc-store-product-grid', 'vendor-wc-store-product-list',
        'vendor-wcfm-store-list', 'vendor-wcfm-store-product-grid', 'vendor-wcfm-store-product-list',
        'vendor-wcmp-store-list', 'vendor-wcmp-store-product-grid', 'vendor-wcmp-store-product-list',
        'demo1', 'demo2', 'about-us', 'blog', 'cart', 'checkout', 'compare', 'contact-us',
        'faq', 'login', 'my-account', 'order', 'order-view', 'register', 'wishlist'
    ];
    if (in_array($page, $validPages)) {
        return view('frontend.template1.' . $page);
    }
    abort(404);
})->name('frontend.template1.page');

// Frontend Pages (placeholder) template 2 start

// Frontend Pages (placeholder) template 2 close






// ======================== EXISTING ADMIN/VENDOR ROUTES ========================
Route::get('admin-login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('do-login', [LoginController::class, 'do_login'])->name('do.login');
Route::post('google-login', [LoginController::class, 'google_login'])->name('google.login');
Route::post('facebook-login', [LoginController::class, 'facebook_login'])->name('facebook.login');
Route::get('facebook-debug', function () {
    $notificationSetting = \App\Models\GeneralSetting::first();
    return view('backend.facebook-debug', compact('notificationSetting'));
})->name('facebook.debug');
Route::get('admin-register', [LoginController::class, 'showRegistrationForm'])->name('admin.register');
Route::post('admin-register', [LoginController::class, 'register'])->name('do.register');
// Admin/vendor logout still uses the original route
Route::get('admin-logout', [LoginController::class, 'logout'])->name('admin.logout');
// Backward compatibility for backend views
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth','role:1,2'])->group(function () {
    Route::get('session/heartbeat', [SessionController::class, 'heartbeat'])->name('session.heartbeat');
    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/test', [NotificationController::class, 'sendTestNotification'])->name('notifications.test');
    Route::post('notifications/send-push', [NotificationController::class, 'sendManualPush'])->name('notifications.sendPush');
    Route::get('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('save-device-token', [NotificationController::class, 'updatePlayerId'])->name('save.device.token');
    Route::post('update-player-id', [NotificationController::class, 'updatePlayerId'])->name('update.player.id');
    Route::get('notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
});


Route::get('/test-mail', function () {
    \App\Helpers\EmailHelper::getMailer()->raw('SMTP Test Mail Working', function ($message) {
        $message->to('aartiburman65@gmail.com')
            ->subject('Laravel SMTP Test');
    });

    return "Mail Sent Successfully";
});
