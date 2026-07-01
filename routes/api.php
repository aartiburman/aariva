<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Api\UserController;
use  App\Http\Controllers\Api\WishlistController;
use  App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\HomeScreenController;
use  App\Http\Controllers\Api\UserCommonController;
use  App\Http\Controllers\Api\UserProductController;
use  App\Http\Controllers\Api\BlogApiController;
use  App\Http\Controllers\Api\UserCheckout;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Vendor\RefundController as VendorRefundController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\VendorCampaignController;
use App\Http\Controllers\Api\VendorMetricsController;

use App\Http\Controllers\Api\PayPalApiController;
use App\Http\Controllers\Admin\OrderController ;
use App\Helpers\EmailHelper;

use App\Http\Controllers\Api\NotificationApiController;

use App\Http\Controllers\Api\TestApiController;

Route::get('test-mail', [TestApiController::class, 'test_mail']);
Route::post('test-mail', [TestApiController::class, 'test_mail']);

// Notifications
Route::post('send-notification', [NotificationApiController::class, 'send_notification']);
Route::get('get-notifications', [NotificationApiController::class, 'get_notifications']);
Route::post('mark-notification-as-read', [NotificationApiController::class, 'mark_as_read']);
Route::get('test-push-notification', [NotificationApiController::class, 'test_push_notification']);
Route::post('send-push-by-token', [NotificationApiController::class, 'send_push_by_token']);
Route::get('test-device-token-notification', [NotificationApiController::class, 'test_device_token_notification']);
Route::get('generate-test-token', [NotificationApiController::class, 'generate_test_token']);
Route::get('get-fcm-config', [NotificationApiController::class, 'get_fcm_config']);
Route::get('get-user-token', [NotificationApiController::class, 'get_user_token']);
Route::post('register-device-token', [NotificationApiController::class, 'register_device_token']);

Route::post('signup', [UserController::class, 'signup']);
Route::get('my-profile', [UserController::class, 'my_profile']);
Route::get('get-referral-details', [UserController::class, 'get_referral_details']);
Route::get('referral-share', [UserController::class, 'get_referral_details']); // Alias: share or copy referral
Route::get('get-kyc-document', [UserController::class, 'get_kyc_document']);
Route::post('update-kyc-documents', [UserController::class, 'update_kyc_documents']);
Route::get('get-my-document', [UserController::class, 'get_my_documents']);
Route::get('get-my-card', [UserController::class, 'get_my_card']);
Route::get('get_my_card', [UserController::class, 'get_my_card']); // Alias for backward compatibility
Route::post('add-card', [UserController::class, 'add_card']);
Route::post('update-card', [UserController::class, 'update_card']);
Route::post('delete-card', [UserController::class, 'delete_card']);
Route::get('edit-card', [UserController::class, 'edit_card']);

Route::post('update-profile', [UserController::class, 'update_profile']);
Route::post('add-to-wishlist', [WishlistController::class, 'add_to_wishlist']);
Route::post('get-wishlist', [WishlistController::class, 'get_wishlist']);
Route::post('add-remove-cart', [CartController::class, 'add_remove_cart']);
Route::post('apply-offer', [CartController::class, 'applyOffer']);

Route::get('get-product-detail', [UserProductController::class, 'get_product_detail']);
Route::get('product-list', [UserProductController::class, 'product_list']);
Route::get('product-search', [UserProductController::class, 'product_search']);
Route::get('product-coupons', [UserProductController::class, 'getProductCoupon']);
Route::get('get-coupons', [UserProductController::class, 'getCoupons']);
Route::get('get-cart-detail', [CartController::class, 'get_cart_detail']);


Route::post('home', [HomeScreenController::class, 'home']);
Route::post('userlogin', [UserController::class, 'userlogin']);
Route::post('social-login', [UserController::class, 'social_login']);
Route::post('facebook-login', [UserController::class, 'facebook_login']);
Route::get('logout', [UserController::class, 'logout']);
Route::post('forgot-password', [UserController::class, 'forgot_password']);
Route::post('reset-password', [UserController::class, 'reset_password']);
Route::post('verify-otp', [UserController::class, 'verify_otp']);
Route::post('change-password', [UserController::class, 'change_password']);
Route::post('update-notification-status', [UserController::class, 'updateNotificationStatus']);
Route::get('get-notification-status', [UserController::class, 'getNotificationStatus']);

Route::post('place-order', [UserCheckout::class, 'place_order']);
Route::post('checkout', [UserCheckout::class, 'checkout']);
Route::post('checkout-amount', [UserCheckout::class, 'checkout_amount']);
Route::post('buy-now', [UserCheckout::class, 'buy_now']);
Route::post('add-shipping-address', [UserCheckout::class, 'add_shipping_address']);
Route::post('update-shipping-address', [UserCheckout::class, 'update_shipping_address']);
Route::get('edit-shipping-address', [UserCheckout::class, 'edit_shipping_address']);
Route::get('get-shipping-address', [UserCheckout::class, 'get_shipping_address']);
// PhonePe Payment
Route::post('phonepe/initiate', [\App\Http\Controllers\Api\PhonePeApiController::class, 'initiatePayment']);
Route::post('phonepe/verify', [\App\Http\Controllers\Api\PhonePeApiController::class, 'verifyPayment']);
Route::get('phonepe/success', [\App\Http\Controllers\Api\PhonePeApiController::class, 'success']);
Route::get('phonepe/failure', [\App\Http\Controllers\Api\PhonePeApiController::class, 'failure']);
// Admin prefix PhonePe
Route::post('admin/api/phonepe/verify', [\App\Http\Controllers\Api\PhonePeApiController::class, 'verifyPayment']);
Route::get('admin/api/phonepe/success', [\App\Http\Controllers\Api\PhonePeApiController::class, 'success']);
Route::get('admin/api/phonepe/failure', [\App\Http\Controllers\Api\PhonePeApiController::class, 'failure']);

// Paytm Payment
Route::post('paytm/initiate', [\App\Http\Controllers\Api\PaytmApiController::class, 'initiatePayment']);
Route::post('paytm/verify', [\App\Http\Controllers\Api\PaytmApiController::class, 'verifyPayment']);
Route::post('paytm/success', [\App\Http\Controllers\Api\PaytmApiController::class, 'success']);
Route::post('paytm/failure', [\App\Http\Controllers\Api\PaytmApiController::class, 'failure']);
// Admin prefix Paytm
Route::post('admin/api/paytm/verify', [\App\Http\Controllers\Api\PaytmApiController::class, 'verifyPayment']);
Route::post('admin/api/paytm/success', [\App\Http\Controllers\Api\PaytmApiController::class, 'success']);
Route::post('admin/api/paytm/failure', [\App\Http\Controllers\Api\PaytmApiController::class, 'failure']);

Route::post('delete-shipping-address', [UserCheckout::class, 'delete_shipping_address']);
Route::get('my-orders', [UserCheckout::class, 'my_orders']);
Route::get('get-order-detail', [UserCheckout::class, 'get_order_detail']);
Route::get('track-order', [UserCheckout::class, 'track_order']);

Route::get('get-categories', [UserCommonController::class, 'get_categories']);
Route::get('get-subcategories', [UserCommonController::class, 'get_subcategories']);
Route::get('get-childcategories', [UserCommonController::class, 'get_childcategories']);
Route::get('get-brands', [UserCommonController::class, 'get_brands']);

Route::get('get-countries', [UserCommonController::class, 'get_countries']);
Route::get('get-states', [UserCommonController::class, 'get_states']);
Route::get('get-vendor-policies', [UserCommonController::class, 'get_vendor_policies']);
Route::get('get-cities', [UserCommonController::class, 'get_cities']);
Route::get('get-payment-methods', [UserCommonController::class, 'get_payment_methods']);
Route::get('get-payment-method-detail', [UserCommonController::class, 'get_payment_method_detail']);

Route::get('get-bestseller-product', [UserProductController::class, 'get_bestsellr_product']);
Route::get('get-featured-product', [UserProductController::class, 'get_featured_product']);
Route::get('get-ondeal-product', [UserProductController::class, 'get_ondeal_product']);
Route::get('get-trending-product', [UserProductController::class, 'get_trending_product']);
Route::get('get-popular-product', [UserProductController::class, 'get_popular_product']);

Route::get('footer-menu', [UserProductController::class, 'footer_menu']);
Route::get('get-terms-and-condition', [UserCommonController::class, 'get_terms_and_condition']);
Route::get('get-privacy-policy', [UserCommonController::class, 'get_privacy_policy']);
Route::get('get-about-us', [UserCommonController::class, 'get_about_us']);
Route::get('get-faqs', [UserCommonController::class, 'get_faqs']);
Route::get('get-company-info', [UserCommonController::class, 'get_company_info']);

// Blog Routes
Route::get('blog', [BlogApiController::class, 'index']);
Route::get('blog-detail', [BlogApiController::class, 'show']);

Route::post('seller-registration', [UserController::class, 'seller_registration']);

// Vendor Campaign Opt-in
Route::post('vendor/campaign/opt-in', [VendorCampaignController::class, 'optIn']);
Route::post('vendor/campaign/opt-out', [VendorCampaignController::class, 'optOut']);
Route::post('vendor/campaign/join', [VendorCampaignController::class, 'join']);

// Vendor Metrics
Route::get('vendor/margin-summary', [VendorMetricsController::class, 'marginSummary']);
Route::get('vendor-earning-estimate', [UserProductController::class, 'vendor_earning_estimate']);

Route::post('add-rating-and-review', [UserProductController::class, 'add_rating_and_review']);
Route::get('my-reviews', [UserController::class, 'my_reviews']);
Route::post('update-review', [UserController::class, 'update_review']);
Route::post('delete-review', [UserController::class, 'delete_review']);
Route::post('like-dislike-review', [UserProductController::class, 'like_dislike_review']);


 Route::get('ticket-list', [TicketApiController::class, 'ticket_index']);
    Route::post('create-ticket', [TicketApiController::class, 'store']);
    Route::get('ticket-detail', [TicketApiController::class, 'show']);
    Route::post('reply-ticket', [TicketApiController::class, 'reply']);

// Refund & Wallet Routes

  Route::post('refund-request', [RefundController::class, 'createRefundRequest']);
    Route::get('my-refunds', [RefundController::class, 'getUserRefunds']);
    Route::get('wallet-details', [RefundController::class, 'getWalletDetails']);

// PayPal API Routes
Route::post('paypal/create-payment', [PayPalApiController::class, 'createPayment']);
Route::post('paypal/capture-payment', [PayPalApiController::class, 'capturePayment']);
Route::get('paypal/success', [PayPalApiController::class, 'success'])->name('api.paypal.success');
Route::get('paypal/cancel', [PayPalApiController::class, 'cancel'])->name('api.paypal.cancel');


// Route::get('test-sms', [UserController::class, 'testSms']);
// Route::get('send-notification', [UserController::class, 'send_notification']);

// Route::match(['get', 'post'], 'update-fcm-settings', function(\Illuminate\Http\Request $request) {
//     $data = [
//         'firebase_api_key' => $request->apiKey ?? 'AIzaSyCBozqKSO6IqmmHVlRvTVQYtQV7RIgGpUY',
//         'firebase_auth_domain' => $request->authDomain ?? 'nepoora-auth.firebaseapp.com',
//         'firebase_project_id' => $request->projectId ?? 'nepoora-auth',
//         'firebase_storage_bucket' => $request->storageBucket ?? 'nepoora-auth.firebasestorage.app',
//         'firebase_messaging_sender_id' => $request->messagingSenderId ?? '288333381789',
//         'firebase_app_id' => $request->appId ?? '1:288333381789:web:e8d02fd0f0f899cb729474',
//         'measurementId' => $request->measurementId ?? 'G-W0MZC761Q3',
//         'status' => 1
//     ];

//     if ($request->has('serviceAccount')) {
//         $data['firebase_service_account'] = is_array($request->serviceAccount) 
//             ? json_encode($request->serviceAccount) 
//             : $request->serviceAccount;
//     }

//     $setting = \App\Models\NotificationSetting::updateOrCreate(['id' => 1], $data);

//     return response()->json([
//         'status' => true, 
//         'message' => 'FCM settings updated successfully', 
//         'service_account_saved' => $request->has('serviceAccount') ? 'Yes' : 'No',
//         'current_service_account_status' => empty($setting->firebase_service_account) ? 'Empty' : 'Present',
//         'data' => $setting
//     ]);
// });

// Route::get('check-fcm-config', function() {
//     $setting = \App\Models\NotificationSetting::first();
//     if (!$setting) return response()->json(['status' => false, 'message' => 'No settings found']);
    
//     try {
//         $dummyToken = 'dummy_token_at_least_140_characters_long_to_pass_sdk_validation_check_' . str_repeat('a', 100);
//         $serviceAccount = \App\Helpers\NotificationHelper::sendPushByToken($dummyToken, ['title' => 'test', 'message' => 'test']);
//     } catch (\Exception $e) {
//         $message = $e->getMessage();
//         if (
//             strpos($message, 'Requested entity was not found') !== false || 
//             strpos($message, 'Invalid registration token') !== false ||
//             strpos($message, 'not a valid FCM registration token') !== false
//         ) {
//             return response()->json(['status' => true, 'message' => 'Credentials are VALID (Token was dummy but auth succeeded)']);
//         }
//         return response()->json(['status' => false, 'message' => 'FCM Auth Failed: ' . $message]);
//     }
// });



