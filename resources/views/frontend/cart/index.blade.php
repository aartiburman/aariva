@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">{{ __t('Shopping Cart') }}</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> {{ __t('Home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __t('Cart') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-4">
    <div class="container">
        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($cartItems->isEmpty())
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bx bx-cart" style="font-size: 80px; color: #dee2e6;"></i>
            </div>
            <h4 class="text-muted fw-light">{{ __t('Your cart is empty') }}</h4>
            <p class="text-muted mb-4">{{ __t("Looks like you haven't added anything yet.") }}</p>
            <a href="{{ route('frontend.products.index') }}" class="btn btn-dark btn-ecomm px-4 py-2">{{ __t('Continue Shopping') }}</a>
        </div>
        @else
        <div class="shop-cart">
            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0 fw-bold">{{ $cartItems->count() }} {{ $cartItems->count() > 1 ? __t('Items') : __t('Item') }} {{ __t('in your cart') }}</h5>
                    </div>
                    @foreach ($cartItems as $item)
                    @php
                        $product = $item->product;
                        $variant = $product && $product->relationLoaded('variants') ? $product->variants->where('id', $item->variant_id)->first() : null;
                        $img = $item->image ? App\Helpers\ImageHelper::getProductImage($item->image) : asset('frontend/assets/images/products/01.png');
                        $unitPrice = $item->variant->price ?? $item->price ?? 0;
                        $lineTotal = $unitPrice * $item->qty;
                    @endphp
                    <div class="cart-item-card mb-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-sm-3">
                                <div class="cart-item-img">
                                    <a href="{{ route('frontend.products.show', $product->slug ?? $product->id) }}">
                                        <img src="{{ $img }}" alt="{{ $product->name ?? '' }}">
                                    </a>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="cart-item-detail">
                                    <a href="{{ route('frontend.products.show', $product->slug ?? $product->id) }}" class="cart-item-title">{{ $product->name ?? 'Product' }}</a>
                                    @if ($variant && ($variant->size ?? $variant->color ?? $variant->option1))
                                    <p class="cart-item-variant mb-1">Size: <span>{{ $variant->size ?? $variant->option1 ?? 'N/A' }}</span></p>
                                    @endif
                                    @if ($variant && ($variant->color ?? $variant->option2))
                                    <p class="cart-item-variant mb-1">Color: <span>{{ $variant->color ?? $variant->option2 ?? 'N/A' }}</span></p>
                                    @endif
                                    <div class="cart-item-price d-sm-none">{{ App\Helpers\PriceHelper::formatPrice($unitPrice) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-2">
                                <div class="cart-item-price text-center d-none d-sm-block">{{ App\Helpers\PriceHelper::formatPrice($unitPrice) }}</div>
                            </div>
                            <div class="col-6 col-sm-2">
                                <div class="qty-control">
                                    <button type="button" class="qty-btn qty-minus" data-cart-id="{{ $item->id }}">−</button>
                                    <input type="number" class="qty-input cart-qty-input" value="{{ $item->qty }}" min="1" data-cart-id="{{ $item->id }}">
                                    <button type="button" class="qty-btn qty-plus" data-cart-id="{{ $item->id }}">+</button>
                                </div>
                            </div>
                            <div class="col-12 col-sm-1 text-center">
                                <button type="button" class="cart-remove-btn" data-cart-id="{{ $item->id }}" title="Remove item">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <a href="{{ route('frontend.products.index') }}" class="btn btn-outline-dark btn-ecomm"><i class="bx bx-arrow-back"></i> {{ __t('Continue Shopping') }}</a>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="order-summary-card">
                        <h5 class="fw-bold mb-0">{{ __t('Order Summary') }}</h5>
                        <hr class="my-3">
                        <div class="summary-row">
                            <span>{{ __t('Subtotal') }}</span>
                            <span id="cart-subtotal">{{ App\Helpers\PriceHelper::formatPrice($cartTotals['sub_total']) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>{{ __t('Shipping') }}</span>
                            <span id="cart-shipping">{{ $cartTotals['delivery_charges'] > 0 ? App\Helpers\PriceHelper::formatPrice($cartTotals['delivery_charges']) : __t('Free') }}</span>
                        </div>
                        <div class="summary-row">
                            <span>{{ __t('Taxes') }}</span>
                            <span id="cart-taxes">{{ App\Helpers\PriceHelper::formatPrice($cartTotals['taxes']) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>{{ __t('Discount') }}</span>
                            <span id="cart-discount">{{ $cartTotals['total_discount'] > 0 ? App\Helpers\PriceHelper::formatPrice($cartTotals['total_discount']) : '—' }}</span>
                        </div>
                        <hr class="my-3">
                        <div class="summary-row total-row">
                            <span class="fw-bold">{{ __t('Order Total') }}</span>
                            <span class="fw-bold" id="cart-total">{{ App\Helpers\PriceHelper::formatPrice($cartTotals['total_cost']) }}</span>
                        </div>
                        <div class="d-grid mt-4">
                            <a href="{{ route('frontend.checkout.index') }}" class="btn btn-dark btn-ecomm py-2 fw-semibold">{{ __t('Proceed to Checkout') }}</a>
                        </div>
                        <div class="text-center mt-3">
                            <img src="{{ asset('frontend/assets/images/payment-methods.png') }}" alt="Payment methods" class="img-fluid" style="max-height: 28px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
.cart-item-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: box-shadow .2s ease;
}
.cart-item-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,.06);
}
.cart-item-img {
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}
.cart-item-img img {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    display: block;
}
.cart-item-title {
    font-size: 15px;
    font-weight: 600;
    color: #212529;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}
.cart-item-title:hover { color: #000; }
.cart-item-variant {
    font-size: 13px;
    color: #6c757d;
}
.cart-item-variant span { color: #212529; font-weight: 500; }
.cart-item-price {
    font-size: 16px;
    font-weight: 600;
    color: #212529;
}
.qty-control {
    display: inline-flex;
    align-items: center;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}
.qty-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #f8f9fa;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
    color: #495057;
    line-height: 1;
}
.qty-btn:hover { background: #e9ecef; }
.qty-input {
    width: 44px;
    height: 36px;
    border: none;
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    outline: none;
    -moz-appearance: textfield;
}
.qty-input::-webkit-inner-spin-button,
.qty-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.cart-remove-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    color: #adb5bd;
    font-size: 18px;
    cursor: pointer;
    border-radius: 8px;
    transition: all .2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cart-remove-btn:hover {
    background: #fee2e2;
    color: #dc3545;
}
.order-summary-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 24px;
    position: sticky;
    top: 100px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 14px;
    color: #495057;
}
.total-row {
    font-size: 17px;
    color: #212529;
}
@media (max-width: 575.98px) {
    .cart-item-card { padding: 16px; }
    .qty-control { margin: 0 auto; }
    .cart-remove-btn { margin: 0 auto; }
}
</style>
@endpush

@push('scripts')
<script>
var cartUpdateTotals = function(totals) {
    $('#cart-subtotal').text(totals.sub_total_formatted || '{{ App\Helpers\PriceHelper::formatPrice(0) }}');
    $('#cart-shipping').text(totals.delivery_charges > 0 ? (totals.delivery_charges_formatted || '{{ App\Helpers\PriceHelper::formatPrice(0) }}') : 'Free');
    $('#cart-taxes').text(totals.taxes_formatted || '{{ App\Helpers\PriceHelper::formatPrice(0) }}');
    $('#cart-discount').text(totals.total_discount > 0 ? (totals.total_discount_formatted || '{{ App\Helpers\PriceHelper::formatPrice(0) }}') : '—');
    $('#cart-total').text(totals.total_cost_formatted || '{{ App\Helpers\PriceHelper::formatPrice(0) }}');
};

$(function() {
    $(document).on('change', '.cart-qty-input', function() {
        var $input = $(this);
        var id = $input.data('cart-id');
        var qty = parseInt($input.val()) || 1;
        if (qty < 1) { $input.val(1); qty = 1; }
        $.ajax({
            url: '{{ route("frontend.cart.update") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', id: id, qty: qty },
            success: function(res) {
                if (res.status && res.totals) cartUpdateTotals(res.totals);
            },
            error: function() { alert('Failed to update quantity'); location.reload(); }
        });
    });

    $(document).on('click', '.qty-plus', function() {
        var $input = $(this).siblings('.cart-qty-input');
        $input.val(parseInt($input.val()) + 1).trigger('change');
    });

    $(document).on('click', '.qty-minus', function() {
        var $input = $(this).siblings('.cart-qty-input');
        var val = parseInt($input.val());
        if (val > 1) $input.val(val - 1).trigger('change');
    });

    $(document).on('click', '.cart-remove-btn', function() {
        if (!confirm('Remove this item from cart?')) return;
        var $btn = $(this);
        var id = $btn.data('cart-id');
        var $row = $btn.closest('.cart-item-card');
        $.ajax({
            url: '{{ url("cart/remove") }}/' + id,
            method: 'GET',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.status) {
                    if (res.items_html) { $('.shop-cart').html(res.items_html); }
                    else { $row.fadeOut(300, function() { $(this).remove(); }); }
                    if (res.totals) cartUpdateTotals(res.totals);
                }
            },
            error: function() { alert('Failed to remove item'); location.reload(); }
        });
    });
});
</script>
@endpush
