<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function home()
    {
        return view('frontend.index');
    }

    public function aboutUs()
    {
        return view('frontend.about-us');
    }

    public function contactUs()
    {
        return view('frontend.contact-us');
    }

    public function blogPost()
    {
        return view('frontend.blog-post');
    }

    public function blogRead()
    {
        return view('frontend.blog-read');
    }

    public function shopGrid()
    {
        return view('frontend.shop-grid');
    }

    public function shopGridLeftSidebar()
    {
        return view('frontend.shop-grid-left-sidebar');
    }

    public function shopGridRightSidebar()
    {
        return view('frontend.shop-grid-right-sidebar');
    }

    public function shopGridFilterOnTop()
    {
        return view('frontend.shop-grid-filter-on-top');
    }

    public function shopListLeftSidebar()
    {
        return view('frontend.shop-list-left-sidebar');
    }

    public function shopListRightSidebar()
    {
        return view('frontend.shop-list-right-sidebar');
    }

    public function shopListFilterOnTop()
    {
        return view('frontend.shop-list-filter-on-top');
    }

    public function shopCart()
    {
        return view('frontend.shop-cart');
    }

    public function shopCategories()
    {
        return view('frontend.shop-categories');
    }

    public function productDetails()
    {
        return view('frontend.product-details');
    }

    public function productComparison()
    {
        return view('frontend.product-comparison');
    }

    public function checkoutDetails()
    {
        return view('frontend.checkout-details');
    }

    public function checkoutShipping()
    {
        return view('frontend.checkout-shipping');
    }

    public function checkoutPayment()
    {
        return view('frontend.checkout-payment');
    }

    public function checkoutReview()
    {
        return view('frontend.checkout-review');
    }

    public function checkoutComplete()
    {
        return view('frontend.checkout-complete');
    }

    public function orderTracking()
    {
        return view('frontend.order-tracking');
    }

    public function accountDashboard()
    {
        return view('frontend.account-dashboard');
    }

    public function accountOrders()
    {
        return view('frontend.account-orders');
    }

    public function accountDownloads()
    {
        return view('frontend.account-downloads');
    }

    public function accountAddresses()
    {
        return view('frontend.account-addresses');
    }

    public function accountUserDetails()
    {
        return view('frontend.account-user-details');
    }

    public function accountPaymentMethods()
    {
        return view('frontend.account-payment-methods');
    }

    public function authenticationLogin()
    {
        return view('frontend.authentication-login');
    }

    public function authenticationRegister()
    {
        return view('frontend.authentication-register');
    }

    public function authenticationForgotPassword()
    {
        return view('frontend.authentication-forgot-password');
    }

    public function authenticationResetPassword()
    {
        return view('frontend.authentication-reset-password');
    }

    public function wishlist()
    {
        return view('frontend.wishlist');
    }

    public function index2()
    {
        return view('frontend.index-2');
    }

    public function jproductDetails()
    {
        return view('frontend.jproduct-details');
    }

    public function tvShows()
    {
        return view('frontend.tv-shows');
    }
}
