@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">{{ __t('Wishlist') }}</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __t('Wishlist') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-4">
    <div class="container">
        @if ($wishlistItems->isEmpty())
        <div class="text-center py-5">
            <h4 class="text-muted">{{ __t('Your wishlist is empty') }}</h4>
            <a href="{{ route('frontend.products.index') }}" class="btn btn-dark btn-ecomm mt-3">{{ __t('Browse Products') }}</a>
        </div>
        @else
        <div class="product-grid">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                @foreach ($wishlistItems as $wish)
                @php
                    $product = $wish->product;
                    $variant = $product && $product->relationLoaded('variants') ? $product->variants->where('id', $wish->variant_id)->first() : null;
                    $img = $wish->image ? App\Helpers\ImageHelper::getProductImage($wish->image) : asset('frontend/assets/images/products/01.png');
                    $originalPrice = $variant ? $variant->price : ($product->price ?? 0);
                    $finalPrice = $variant ? App\Helpers\PriceHelper::applyDiscount($variant->price, $variant->discount_type, $variant->discount_value) : $originalPrice;
                    $slug = $product->slug ?? $product->id;
                @endphp
                <div class="col">
                    <div class="product-card-modern h-100">
                        <div class="product-image-wrap">
                            <a href="{{ route('frontend.products.show', $slug) }}">
                                <img src="{{ $img }}" alt="{{ $product->name ?? '' }}">
                            </a>
                            <div class="product-overlay"></div>
                            <div class="product-actions">
                                <a href="javascript:;" class="action-btn add-to-cart" data-product-id="{{ $product->id }}" title="Add to Cart">
                                    <i class='bx bx-cart-add'></i>
                                </a>
                                <a href="javascript:;" class="action-btn remove-from-wishlist" data-product-id="{{ $product->id }}" title="Remove from Wishlist">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </div>
                        </div>
                        <div class="product-body">
                            <a href="javascript:;" class="product-category">{{ $product->category->name ?? __t('Category') }}</a>
                            <a href="{{ route('frontend.products.show', $slug) }}" class="product-title">{{ $product->name ?? '' }}</a>
                            <div class="product-pricing">
                                <span class="current-price">{{ App\Helpers\PriceHelper::formatPrice($finalPrice) }}</span>
                                @if ($finalPrice < $originalPrice)
                                <span class="old-price">{{ App\Helpers\PriceHelper::formatPrice($originalPrice) }}</span>
                                @endif
                            </div>
                            <a href="javascript:;" class="add-to-cart-btn add-to-cart" data-product-id="{{ $product->id }}">
                                <i class='bx bx-cart-add'></i> {{ __t('Add to Cart') }}
                            </a>
                            <a href="javascript:;" style="display:block;width:100%;margin-top:8px;padding:10px;border-radius:8px;background:transparent;color:#dc3545;border:1px solid #dc3545;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s;text-align:center;text-decoration:none;" class="remove-from-wishlist" data-product-id="{{ $product->id }}" onmouseover="this.style.background='#dc3545';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='#dc3545'">
                                <i class='bx bx-trash'></i> {{ __t('Remove') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('.remove-from-wishlist').on('click', function() {
        var productId = $(this).data('product-id');
        var $btn = $(this);
        $.ajax({
            url: '{{ route("frontend.wishlist.toggle") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', product_id: productId },
            success: function() { $btn.closest('.col').fadeOut(300, function() { $(this).remove(); }); }
        });
    });
});
</script>
@endpush
