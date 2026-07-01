@extends('frontend.layouts.app')

@section('title', 'FAQ - ' . config('app.name'))

@section('meta_description', 'Find answers to frequently asked questions about ' . config('app.name') . ' - shipping, returns, payments, orders and more. Quick help for a smooth shopping experience.')

@section('meta_keywords', 'FAQ, frequently asked questions, ' . config('app.name') . ', help, shipping, returns, payments')

@section('og_title', 'FAQ - ' . config('app.name'))
@section('og_description', 'Find answers to frequently asked questions about ' . config('app.name') . ' - shipping, returns, payments, orders and more. Quick help for a smooth shopping experience.')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">FAQ</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">FAQ</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">How to place an order?</button></h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">Browse products, add to cart, and proceed to checkout.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">What payment methods do you accept?</button></h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">We accept Cash on Delivery (COD) and Card payments.</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
