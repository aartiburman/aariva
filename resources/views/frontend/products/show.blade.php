@extends('frontend.layouts.app')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                @php
                    $variant = $product->firstVariant ?? $product->variants->first();
                    $image = $variant ? App\Helpers\ImageHelper::getProductImage($variant->image) : asset('frontend/assets/images/products/01.png');
                @endphp
                <img src="{{ $image }}" class="img-fluid rounded" alt="{{ $product->name }}">
            </div>
            <div class="col-md-6">
                <h2 class="fw-bold">{{ $product->name }}</h2>
                <p class="text-muted">{{ $product->category->name ?? '' }}</p>
                @php
                    $originalPrice = $variant ? $variant->price : 0;
                    $finalPrice = $variant ? App\Helpers\PriceHelper::applyDiscount($variant->price, $variant->discount_type, $variant->discount_value) : 0;
                    $avgRating = $product->approvedReviews->avg('rating') ?? 0;
                @endphp
                <div class="product-price d-flex align-items-center gap-2 mt-3">
                    @if ($finalPrice < $originalPrice)
                    <span class="h5 text-secondary text-decoration-line-through">{{ App\Helpers\PriceHelper::formatPrice($originalPrice) }}</span>
                    @endif
                    <span class="h4 fw-bold">{{ App\Helpers\PriceHelper::formatPrice($finalPrice) }}</span>
                </div>
                <div class="rating mt-2">
                    @for ($i = 1; $i <= 5; $i++)
                    <i class="bx {{ $i <= round($avgRating) ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                    @endfor
                    <span class="ms-2 small">({{ $product->approvedReviews->count() }} {{ __t('reviews') }})</span>
                </div>
                <p class="mt-3">{{ $product->short_description }}</p>
                <div class="mt-4">
                                    <a href="javascript:;" class="btn btn-dark btn-ecomm px-4 {{ $product->is_in_cart ? 'remove-from-cart' : 'add-to-cart' }}" data-product-id="{{ $product->id }}">
                                        <i class='bx {{ $product->is_in_cart ? "bx-cart-x" : "bx-cart-add" }}'></i> {{ $product->is_in_cart ? __t('Remove from Cart') : __t('Add to Cart') }}
                                    </a>
                                    <a href="javascript:;" class="btn btn-outline-dark btn-ecomm px-4 add-to-wishlist ms-2" data-product-id="{{ $product->id }}">
                                        <i class='bx {{ $product->is_in_wishlist ? "bxs-heart" : "bx-heart" }}'></i> {{ $product->is_in_wishlist ? __t('Remove from Wishlist') : __t('Add to Wishlist') }}
                                    </a>
                                </div>
            </div>
        </div>
    </div>
</section>
@endsection
