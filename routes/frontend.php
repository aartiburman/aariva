<?php

use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendController::class, 'home'])->name('frontend.home');
Route::get('/about-us', [FrontendController::class, 'aboutUs'])->name('frontend.about-us');
Route::get('/contact-us', [FrontendController::class, 'contactUs'])->name('frontend.contact-us');
Route::get('/blog-post', [FrontendController::class, 'blogPost'])->name('frontend.blog-post');
Route::get('/blog-read', [FrontendController::class, 'blogRead'])->name('frontend.blog-read');
Route::get('/shop-grid', [FrontendController::class, 'shopGrid'])->name('frontend.shop-grid');
Route::get('/shop-grid-left-sidebar', [FrontendController::class, 'shopGridLeftSidebar'])->name('frontend.shop-grid-left-sidebar');
Route::get('/shop-grid-right-sidebar', [FrontendController::class, 'shopGridRightSidebar'])->name('frontend.shop-grid-right-sidebar');
Route::get('/shop-grid-filter-on-top', [FrontendController::class, 'shopGridFilterOnTop'])->name('frontend.shop-grid-filter-on-top');
Route::get('/shop-list-left-sidebar', [FrontendController::class, 'shopListLeftSidebar'])->name('frontend.shop-list-left-sidebar');
Route::get('/shop-list-right-sidebar', [FrontendController::class, 'shopListRightSidebar'])->name('frontend.shop-list-right-sidebar');
Route::get('/shop-list-filter-on-top', [FrontendController::class, 'shopListFilterOnTop'])->name('frontend.shop-list-filter-on-top');
Route::get('/shop-cart', [FrontendController::class, 'shopCart'])->name('frontend.shop-cart');
Route::get('/shop-categories', [FrontendController::class, 'shopCategories'])->name('frontend.shop-categories');
Route::get('/product-details', [FrontendController::class, 'productDetails'])->name('frontend.product-details');
Route::get('/product-comparison', [FrontendController::class, 'productComparison'])->name('frontend.product-comparison');
Route::get('/checkout-details', [FrontendController::class, 'checkoutDetails'])->name('frontend.checkout-details');
Route::get('/checkout-shipping', [FrontendController::class, 'checkoutShipping'])->name('frontend.checkout-shipping');
Route::get('/checkout-payment', [FrontendController::class, 'checkoutPayment'])->name('frontend.checkout-payment');
Route::get('/checkout-review', [FrontendController::class, 'checkoutReview'])->name('frontend.checkout-review');
Route::get('/checkout-complete', [FrontendController::class, 'checkoutComplete'])->name('frontend.checkout-complete');
Route::get('/order-tracking', [FrontendController::class, 'orderTracking'])->name('frontend.order-tracking');
Route::get('/account-dashboard', [FrontendController::class, 'accountDashboard'])->name('frontend.account-dashboard');
Route::get('/account-orders', [FrontendController::class, 'accountOrders'])->name('frontend.account-orders');
Route::get('/account-downloads', [FrontendController::class, 'accountDownloads'])->name('frontend.account-downloads');
Route::get('/account-addresses', [FrontendController::class, 'accountAddresses'])->name('frontend.account-addresses');
Route::get('/account-user-details', [FrontendController::class, 'accountUserDetails'])->name('frontend.account-user-details');
Route::get('/account-payment-methods', [FrontendController::class, 'accountPaymentMethods'])->name('frontend.account-payment-methods');
Route::get('/authentication-login', [FrontendController::class, 'authenticationLogin'])->name('frontend.authentication-login');
Route::get('/authentication-register', [FrontendController::class, 'authenticationRegister'])->name('frontend.authentication-register');
Route::get('/authentication-forgot-password', [FrontendController::class, 'authenticationForgotPassword'])->name('frontend.authentication-forgot-password');
Route::get('/authentication-reset-password', [FrontendController::class, 'authenticationResetPassword'])->name('frontend.authentication-reset-password');
Route::get('/jproduct-details', [FrontendController::class, 'jproductDetails'])->name('frontend.jproduct-details');
Route::get('/tv-shows', [FrontendController::class, 'tvShows'])->name('frontend.tv-shows');
