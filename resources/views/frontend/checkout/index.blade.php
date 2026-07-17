@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">{{ __t('Checkout') }}</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> {{ __t('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.cart.index') }}">{{ __t('Cart') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __t('Checkout') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-4">
    <div class="container">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
        @endif
        <div class="shop-cart">
            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="card rounded-0">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __t('Shipping Address') }}</h5>

                            @if ($shippingAddresses->isNotEmpty())
                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ __t('Select Saved Address') }}</label>
                                @foreach ($shippingAddresses as $addr)
                                <div class="form-check mb-2">
                                    <input class="form-check-input saved-address-radio" type="radio" name="shipping_address_id" value="{{ $addr->id }}" id="addr{{ $addr->id }}" data-name="{{ $addr->name }}" data-phone="{{ $addr->phone }}" data-email="{{ $addr->email ?? '' }}" data-address="{{ $addr->address }}" data-city="{{ $addr->city }}" data-state="{{ $addr->state ?? '' }}" data-country="{{ $addr->country ?? '' }}" data-zip="{{ $addr->zip ?? '' }}">
                                    <label class="form-check-label" for="addr{{ $addr->id }}">
                                        <strong>{{ $addr->name }}</strong> - {{ $addr->phone }}<br>
                                        <small>{{ $addr->address }}, {{ $addr->city }}, {{ $addr->state }} {{ $addr->zip }}</small>
                                    </label>
                                </div>
                                @endforeach
                                <hr>
                            </div>
                            @endif

                            <form id="checkoutForm" method="POST" action="{{ route('frontend.checkout.place-order') }}">
                                @csrf
                                <input type="hidden" name="shipping_address_id" id="selected_address_id">

                                <div class="row g-3" id="addressForm">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Full Name *') }}</label>
                                        <input type="text" name="name" class="form-control rounded-0" value="{{ old('name', Auth::user()->name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Phone *') }}</label>
                                        <input type="text" name="phone" class="form-control rounded-0" value="{{ old('phone', Auth::user()->phone ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Email') }}</label>
                                        <input type="email" name="email" class="form-control rounded-0" value="{{ old('email', Auth::user()->email ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('City *') }}</label>
                                        <input type="text" name="city" class="form-control rounded-0" value="{{ old('city') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('State') }}</label>
                                        <input type="text" name="state" class="form-control rounded-0" value="{{ old('state') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Zip/Postal Code') }}</label>
                                        <input type="text" name="zip" class="form-control rounded-0" value="{{ old('zip') }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">{{ __t('Address *') }}</label>
                                        <textarea name="address" class="form-control rounded-0" rows="2" required>{{ old('address') }}</textarea>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h5 class="mb-3">{{ __t('Payment Method') }}</h5>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_mode" value="COD" id="cod" checked>
                                        <label class="form-check-label" for="cod">{{ __t('Cash on Delivery (COD)') }}</label>
                                    </div>
                                    @if ($userCards->isNotEmpty())
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_mode" value="Card" id="card">
                                        <label class="form-check-label" for="card">{{ __t('Card Payment') }}</label>
                                    </div>
                                    <div id="cardSelection" class="ms-4 mt-2 d-none">
                                        @foreach ($userCards as $card)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="card_id" value="{{ $card->id }}" id="card{{ $card->id }}">
                                            <label class="form-check-label" for="card{{ $card->id }}">
                                                **** **** **** {{ $card->last_four ?? 'N/A' }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-dark btn-ecomm">{{ __t('Place Order') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="order-summary">
                        <div class="card rounded-0">
                            <div class="card-body">
                                <h5 class="mb-3">{{ __t('Order Summary') }}</h5>
                                @foreach ($cartItems as $item)
                                @php
                                    $product = $item->product;
                                    $img = $item->image ? App\Helpers\ImageHelper::getProductImage($item->image) : asset('frontend/assets/images/products/01.png');
                                @endphp
                                <div class="d-flex align-items-center mb-2">
                                    <a class="d-block flex-shrink-0" href="javascript:;">
                                        <img src="{{ $img }}" class="checkout-product-img" alt="">
                                    </a>
                                    <div class="ps-2">
                                        <h6 class="mb-0 small">{{ $product->name ?? 'Product' }}</h6>
                                        <small>{{ App\Helpers\PriceHelper::formatPrice($item->price) }} x {{ $item->qty }}</small>
                                    </div>
                                </div>
                                @endforeach
                                <div class="my-3 border-top"></div>
                                <p class="mb-2">{{ __t('Subtotal:') }} <span class="float-end">{{ App\Helpers\PriceHelper::formatPrice($cartTotals['sub_total']) }}</span></p>
                                <p class="mb-2">{{ __t('Shipping:') }} <span class="float-end">{{ $cartTotals['delivery_charges'] > 0 ? App\Helpers\PriceHelper::formatPrice($cartTotals['delivery_charges']) : __t('Free') }}</span></p>
                                <p class="mb-2">{{ __t('Taxes:') }} <span class="float-end">{{ App\Helpers\PriceHelper::formatPrice($cartTotals['taxes']) }}</span></p>
                                <p class="mb-0">{{ __t('Discount:') }} <span class="float-end">{{ $cartTotals['total_discount'] > 0 ? '- ' . App\Helpers\PriceHelper::formatPrice($cartTotals['total_discount']) : '--' }}</span></p>
                                <div class="my-3 border-top"></div>
                                <h5 class="mb-0">{{ __t('Total:') }} <span class="float-end">{{ App\Helpers\PriceHelper::formatPrice($cartTotals['total_cost']) }}</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('.saved-address-radio').on('change', function() {
        var $this = $(this);
        $('#selected_address_id').val($this.val());
        $('#addressForm input[name="name"]').val($this.data('name'));
        $('#addressForm input[name="phone"]').val($this.data('phone'));
        $('#addressForm input[name="email"]').val($this.data('email'));
        $('#addressForm input[name="city"]').val($this.data('city'));
        $('#addressForm input[name="state"]').val($this.data('state'));
        $('#addressForm input[name="zip"]').val($this.data('zip'));
        $('#addressForm textarea[name="address"]').val($this.data('address'));
    });

    $('input[name="payment_mode"]').on('change', function() {
        $('#cardSelection').toggleClass('d-none', $(this).val() !== 'Card');
    });
});
</script>
@endpush
