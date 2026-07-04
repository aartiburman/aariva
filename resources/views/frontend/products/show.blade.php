@php
    $variant = $product->variants->first();
    $productOgImage = $variant && !empty($variant->images) ? $variant->images[0] : asset('frontend/assets/images/products/01.png');
    $metaTitle = $product->meta_title ?: ($product->name . ' - ' . config('app.name'));
    $metaDescription = $product->meta_description ?: \Str::limit(strip_tags($product->description ?? $product->short_description ?? ''), 160);
    $inStock = $variant && $variant->stock > 0;
@endphp

@extends('frontend.layouts.app')

@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', ($product->name ?? 'product') . ', buy ' . ($product->name ?? '') . ', ' . config('app.name') . ', online shopping, best price')
@section('canonical', route('frontend.products.show', $product->slug))

@section('og_type', 'product')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@section('og_image', $productOgImage)
@section('og_url', route('frontend.products.show', $product->slug))
@push('head-links')
<meta property="product:availability" content="{{ $inStock ? 'in stock' : 'out of stock' }}" />
<meta property="product:price:amount" content="{{ $variant ? $variant->actual_price : 0 }}" />
<meta property="product:price:currency" content="{{ session('currency_code', 'USD') }}" />
@if($product->category)
    <link rel="prefetch" href="{{ route('frontend.products.index', ['category' => $product->category->slug]) }}">
@endif
@endpush

@section('content')
<!--start breadcrumb-->
<section class="py-3 border-bottom border-top d-none d-sm-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">{{ $product->name }}</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        @if($product->category)
                            <li class="breadcrumb-item"><a href="{{ route('frontend.products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<!--end breadcrumb-->

<!--start product detail-->
<section class="py-4">
    <div class="container">
        <div class="product-detail-card">
            <div class="product-detail-body">
                <div class="row g-0">
                    <div class="col-12 col-lg-5">
                        <div class="image-zoom-section">
                            @php
                                $selectedVariant = $variant ?? $product->variants->first();
                                $initialImages = $selectedVariant && !empty($selectedVariant->images)
                                    ? $selectedVariant->images
                                    : [
                                        asset('frontend/assets/images/products/01.png'),
                                        asset('frontend/assets/images/products/02.png'),
                                        asset('frontend/assets/images/products/03.png'),
                                        asset('frontend/assets/images/products/04.png'),
                                    ];
                            @endphp
                            <div class="product-gallery owl-carousel owl-theme border mb-3 p-3">
                                @foreach($initialImages as $i => $img)
                                    <div class="item">
                                        <img src="{{ $img }}" class="img-fluid" alt="{{ $product->name }}" {{ $i > 0 ? 'loading="lazy"' : 'fetchpriority="high"' }}>
                                    </div>
                                @endforeach
                            </div>
                            <div class="product-thumbs d-flex justify-content-center flex-wrap">
                                @foreach($initialImages as $i => $img)
                                    <button class="product-thumb-item mx-1 mb-2 border">
                                        <img src="{{ $img }}" alt="{{ $product->name }}" style="width: 60px; height: 60px; object-fit: cover;" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="product-info-section p-3">
                            <h3 class="mt-3 mt-lg-0 mb-0">{{ $product->name }}</h3>
                            <div class="product-rating d-flex align-items-center mt-2">
                                <div class="rates cursor-pointer font-13">
                                    @php
                                        $avgRating = $product->approvedReviews->avg('rating') ?? 0;
                                    @endphp
                                    @for($i=1; $i<=5; $i++)
                                        <i class="bx {{ $i <= round($avgRating) ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                    @endfor
                                </div>
                                <div class="ms-1">
                                    <p class="mb-0">({{ $product->approvedReviews->count() }} Reviews)</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-3 gap-2" id="priceSection">
                                @php
                                    $originalPrice = $variant ? $variant->price : 0;
                                    $finalPrice = $variant ? App\Helpers\PriceHelper::applyDiscount($variant->price, $variant->discount_type, $variant->discount_value) : 0;
                                @endphp
                                @if($finalPrice < $originalPrice)
                                    <h5 class="mb-0 text-decoration-line-through text-light-3" id="originalPrice">{{ App\Helpers\PriceHelper::formatPrice($originalPrice) }}</h5>
                                @endif
                                <h4 class="mb-0" id="finalPrice">{{ App\Helpers\PriceHelper::formatPrice($finalPrice) }}</h4>
                                @if($finalPrice < $originalPrice)
                                    <span class="badge bg-success ms-2" id="discountBadge">
                                        @if($variant->discount_type === 'percent' || $variant->discount_type === '%' || $variant->discount_type === 'percentage')
                                            {{ $variant->discount_value }}% OFF
                                        @else
                                            {{ App\Helpers\PriceHelper::formatPrice($variant->discount_value) }} OFF
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="mt-3">
                                <h6>{{ __('Description') }}:</h6>
                                <p class="mb-0">{{ $product->short_description ?? $product->description ?? __('No description available.') }}</p>
                            </div>
                            
                            @if($product->variants->count() > 1)
                                <div class="mt-3">
                                    <h6>{{ __('Variants') }}:</h6>
                                    <select class="form-select form-select-sm" id="variantSelect">
                                        @foreach($product->variants as $v)
                                            @php
                                                $vFinalPrice = App\Helpers\PriceHelper::applyDiscount($v->price, $v->discount_type, $v->discount_value);
                                                $vHasDiscount = $vFinalPrice < $v->price;
                                                $vDiscountLabel = '';
                                                if ($vHasDiscount) {
                                                    $dType = strtolower($v->discount_type);
                                                    $vDiscountLabel = in_array($dType, ['percent', 'percentage', '%'])
                                                        ? $v->discount_value . '% OFF'
                                                        : App\Helpers\PriceHelper::formatPrice($v->discount_value) . ' OFF';
                                                }
                                            @endphp
                                            <option value="{{ $v->id }}" {{ $variant && $v->id === $variant->id ? 'selected' : '' }}
                                                data-price="{{ $v->price }}"
                                                data-discount-type="{{ $v->discount_type }}"
                                                data-discount-value="{{ $v->discount_value }}"
                                                data-images='{{ json_encode($v->images ?? []) }}'
                                                data-stock="{{ $v->stock ?? 0 }}"
                                                data-formatted-original="{{ $vHasDiscount ? App\Helpers\PriceHelper::formatPrice($v->price) : '' }}"
                                                data-formatted-final="{{ App\Helpers\PriceHelper::formatPrice($vFinalPrice) }}"
                                                data-discount-label="{{ $vDiscountLabel }}">
                                                {{ $v->product_variant_label ?? ('Variant ' . $loop->iteration) }} - {{ App\Helpers\PriceHelper::formatPrice($vFinalPrice) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="row row-cols-auto align-items-center mt-3">
                                <div class="col">
                                    <label class="form-label">{{ __('Quantity') }}:</label>
                                    <select class="form-select form-select-sm" id="qtySelect">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <button type="button" 
                                    class="btn btn-dark btn-ecomm px-4 {{ $product->is_in_cart ? 'remove-from-cart' : 'add-to-cart' }}" 
                                    data-product-id="{{ $product->id }}" 
                                    data-variant-id="{{ $variant ? $variant->id : '' }}" 
                                    data-qty="1"
                                    id="cartBtn">
                                    <i class="bx {{ $product->is_in_cart ? 'bx-cart-x' : 'bx-cart-add' }}"></i> {{ $product->is_in_cart ? __('Remove from Cart') : __('Add to Cart') }}
                                </button>
                                <button type="button" 
                                    class="btn btn-outline-dark btn-ecomm px-4 add-to-wishlist ms-2 {{ $product->is_in_wishlist ? 'active text-danger' : '' }}" 
                                    data-product-id="{{ $product->id }}" 
                                    data-variant-id="{{ $variant ? $variant->id : '' }}" 
                                    data-qty="1"
                                    id="wishlistBtn">
                                    <i class="bx {{ $product->is_in_wishlist ? 'bxs-heart' : 'bx-heart' }}"></i> {{ $product->is_in_wishlist ? __('Remove from Wishlist') : __('Add to Wishlist') }}
                                </button>
                            </div>
                            <hr class="my-4">
                            <div class="product-sharing">
                                <h6 class="mb-2">{{ __('Share this product') }}:</h6>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="">
                                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($product->name) }}&url={{ urlencode(route('frontend.products.show', $product->slug)) }}" target="_blank" rel="noopener noreferrer" class="btn-social bg-twitter"><i class="bx bxl-twitter"></i></a>
                                    </div>
                                    <div class="">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('frontend.products.show', $product->slug)) }}" target="_blank" rel="noopener noreferrer" class="btn-social bg-facebook"><i class="bx bxl-facebook"></i></a>
                                    </div>
                                    <div class="">
                                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('frontend.products.show', $product->slug)) }}&title={{ urlencode($product->name) }}" target="_blank" rel="noopener noreferrer" class="btn-social bg-linkedin"><i class="bx bxl-linkedin"></i></a>
                                    </div>
                                    <div class="">
                                        <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(route('frontend.products.show', $product->slug)) }}&media={{ urlencode($productOgImage) }}&description={{ urlencode($product->name) }}" target="_blank" rel="noopener noreferrer" class="btn-social bg-pinterest"><i class="bx bxl-pinterest"></i></a>
                                    </div>
                                    <div class="">
                                        <a href="https://api.whatsapp.com/send?text={{ urlencode($product->name . ' - ' . route('frontend.products.show', $product->slug)) }}" target="_blank" rel="noopener noreferrer" class="btn-social bg-success"><i class="bx bxl-whatsapp"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--end product detail-->

<!--start product more info-->
<section class="py-4">
    <div class="container">
        <div class="product-more-info">
            <ul class="nav nav-tabs mb-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#discription">
                        <div class="d-flex align-items-center">
                            <div class="tab-title text-uppercase fw-500">{{ __('Description') }}</div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#reviews">
                        <div class="d-flex align-items-center">
                            <div class="tab-title text-uppercase fw-500">({{ $product->approvedReviews->count() }}) {{ __('Reviews') }}</div>
                        </div>
                    </a>
                </li>
            </ul>
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="discription">
                    <div>{!! $product->description ?? '<p>' . __('No detailed description available.') . '</p>' !!}</div>
                </div>
                <div class="tab-pane fade" id="reviews">
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <div class="product-review">
                                <h5 class="mb-4">{{ __('Reviews for the Product') }}</h5>
                                <div class="review-list">
                                    @if($product->approvedReviews->count() > 0)
                                        @foreach($product->approvedReviews as $review)
                                            <div class="d-flex align-items-start mb-4 pb-4 border-bottom">
                                                <div class="review-user">
                                                    <img src="{{ asset('frontend/assets/images/avatars/avatar-1.png') }}" width="65" height="65" class="rounded-circle" alt="{{ $review->user->name ?? 'User' }}">
                                                </div>
                                                <div class="review-content ms-3 flex-grow-1">
                                                    <div class="rates cursor-pointer fs-6">
                                                        @for($i=1; $i<=5; $i++)
                                                            <i class="bx {{ $i <= $review->rating ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <div class="d-flex align-items-center mb-2 justify-content-between">
                                                        <h6 class="mb-0">{{ $review->user->name ?? 'Anonymous' }}</h6>
                                                        <p class="mb-0">{{ $review->created_at ? $review->created_at->format('F d, Y') : '' }}</p>
                                                    </div>
                                                    <p class="mb-0">{{ $review->comment ?? '' }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">{{ __('No reviews yet. Be the first to review this product!') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="add-review border p-4">
                                <h4 class="mb-4">{{ __('Write a Review') }}</h4>
                                <form id="reviewForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Your Name') }}</label>
                                        <input type="text" class="form-control rounded-0" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Your Email') }}</label>
                                        <input type="email" class="form-control rounded-0" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Rating') }}</label>
                                        <select class="form-select rounded-0" name="rating" required>
                                            <option value="" selected>{{ __('Choose Rating') }}</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Good</option>
                                            <option value="3">3 - Average</option>
                                            <option value="2">2 - Poor</option>
                                            <option value="1">1 - Terrible</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Your Review') }}</label>
                                        <textarea class="form-control rounded-0" name="comment" rows="3" required></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-dark btn-ecomm">{{ __('Submit a Review') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--end product more info-->

<!--start similar products-->
@if(isset($relatedProducts) && $relatedProducts->count() > 0)
<section class="py-4">
    <div class="container">
        <div class="separator pb-4">
            <div class="line"></div>
            <h5 class="mb-0 fw-bold separator-title">{{ __('Similar Products') }}</h5>
            <div class="line"></div>
        </div>
        <div class="product-grid">
            <div class="similar-products owl-carousel owl-theme position-relative">
                @foreach($relatedProducts as $similarProduct)
                    <div class="item">
                        <div class="card">
                            <div class="position-relative overflow-hidden">
                                <div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
                                    <button type="button" class="{{ $similarProduct->is_in_cart ? 'remove-from-cart' : 'add-to-cart' }}" 
                                        data-product-id="{{ $similarProduct->id }}" 
                                        data-variant-id="{{ $similarProduct->firstVariant ? $similarProduct->firstVariant->id : '' }}" 
                                        data-qty="1">
                                        <i class="bx {{ $similarProduct->is_in_cart ? 'bx-cart-x' : 'bx-cart-add' }}"></i>
                                    </button>
                                </div>
                                <div class="quick-view position-absolute start-0 bottom-0 end-0">
                                    <a href="{{ route('frontend.products.show', $similarProduct->slug) }}" class="btn btn-light btn-sm">{{ __('View Product') }}</a>
                                </div>
                                <a href="{{ route('frontend.products.show', $similarProduct->slug) }}">
                                    @php
                                        $similarVariant = $similarProduct->firstVariant ?? $similarProduct->variants->first();
                                        $similarImg = $similarVariant && $similarVariant->image ? App\Helpers\ImageHelper::getProductImage($similarVariant->image) : asset('frontend/assets/images/similar-products/01.png');
                                    @endphp
                                    <img src="{{ $similarImg }}" class="img-fluid" alt="{{ $similarProduct->name }}">
                                </a>
                            </div>
                            <div class="card-body px-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="">
                                        <p class="mb-1 product-short-name">{{ $similarProduct->category->name ?? 'Category' }}</p>
                                        <h6 class="mb-0 fw-bold product-short-title">{{ $similarProduct->name }}</h6>
                                    </div>
                                    <div class="icon-wishlist">
                                        <button type="button" class="add-to-wishlist {{ $similarProduct->is_in_wishlist ? 'active text-danger' : '' }}" 
                                            data-product-id="{{ $similarProduct->id }}">
                                            <i class="bx {{ $similarProduct->is_in_wishlist ? 'bxs-heart' : 'bx-heart' }}"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
                                    @php
                                        $sOriginalPrice = $similarVariant ? $similarVariant->price : 0;
                                        $sFinalPrice = $similarVariant ? App\Helpers\PriceHelper::applyDiscount($similarVariant->price, $similarVariant->discount_type, $similarVariant->discount_value) : 0;
                                    @endphp
                                    @if($sFinalPrice < $sOriginalPrice)
                                        <div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">
                                            {{ App\Helpers\PriceHelper::formatPrice($sOriginalPrice) }}
                                        </div>
                                    @endif
                                    <div class="h6 fw-bold">{{ App\Helpers\PriceHelper::formatPrice($sFinalPrice) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
<!--end similar products-->

@php
    $avgRating = $product->approvedReviews->avg('rating') ?? 0;
    $reviewCount = $product->approvedReviews->count();
    $brandName = $product->brand->name ?? '';
    $currency = session('currency_code', 'USD');
    $offerPrice = $variant ? $variant->actual_price : 0;
    $originalPrice = $variant ? $variant->original_price : 0;
@endphp

<script type="application/ld+json">
{
    "<?php echo '@'; ?>context": "https://schema.org",
    "<?php echo '@'; ?>type": "BreadcrumbList",
    "itemListElement": [
        {"<?php echo '@'; ?>type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('frontend.home') }}" }
        @if($product->category)
        ,{"<?php echo '@'; ?>type": "ListItem", "position": 2, "name": "{{ $product->category->name }}", "item": "{{ route('frontend.products.index', ['category' => $product->category->slug]) }}" }
        @endif
        ,{"<?php echo '@'; ?>type": "ListItem", "position": {{ $product->category ? 3 : 2 }}, "name": "{{ $product->name }}", "item": "{{ route('frontend.products.show', $product->slug) }}" }
    ]
}
</script>

<script type="application/ld+json">
{
    "<?php echo '@'; ?>context": "https://schema.org",
    "<?php echo '@'; ?>type": "Product",
    "name": "{{ $product->name }}",
    "description": "{{ \Str::limit(strip_tags($product->description ?? $product->short_description ?? ''), 300) }}",
    "sku": "{{ $variant->sku ?? $product->id }}",
    @if($brandName)
    "brand": {
        "<?php echo '@'; ?>type": "Brand",
        "name": "{{ $brandName }}"
    },
    @endif
    "image": "{{ $productOgImage }}",
    @if($reviewCount > 0)
    "aggregateRating": {
        "<?php echo '@'; ?>type": "AggregateRating",
        "ratingValue": "{{ number_format($avgRating, 1) }}",
        "reviewCount": "{{ $reviewCount }}",
        "bestRating": "5"
    },
    @endif
    "offers": {
        "<?php echo '@'; ?>type": "Offer",
        "url": "{{ route('frontend.products.show', $product->slug) }}",
        "priceCurrency": "{{ $currency }}",
        "price": "{{ $offerPrice }}",
        "priceValidUntil": "{{ now()->addMonths(6)->toDateString() }}",
        "availability": "{{ $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "itemCondition": "https://schema.org/NewCondition"
    }
}
</script>

@endsection

@push('styles')
<style>
.product-thumb-item {
    width: 80px; height: 80px; padding: 2px;
    background: transparent; cursor: pointer; opacity: 0.6;
}
.product-thumb-item.active-thumb {
    opacity: 1; border-color: #000 !important;
}
.product-thumb-item img { width: 100%; height: 100%; object-fit: cover; }

@media (max-width: 1199px) {
    .wrapper { display: block !important; }
    .header-wrapper.fixed-header {
        position: relative !important;
        top: auto !important;
    }
    body { padding-top: 0 !important; }
    .page-wrapper { margin-top: 0 !important; }
}
</style>
@endpush

@push('scripts')
<script>
$(function() {
    var productName = '{{ $product->name }}';
    var allVariantImages = {!! json_encode($product->variants->pluck('images', 'id')->map(function($imgs) { return $imgs ?: []; })->toArray()) !!};

    function initGallery() {
        var $g = $('.product-gallery');

        $g.owlCarousel({
            items: 1, loop: true, margin: 0,
            nav: false, dots: false
        });

        // Sync active thumb on carousel change
        $g.on('changed.owl.carousel', function(e) {
            $('.product-thumb-item').removeClass('active-thumb')
                .eq(e.item.index).addClass('active-thumb');
        });

        // Thumbnail click -> go to slide
        $('.product-thumb-item').on('click', function() {
            var idx = $(this).index();
            $g.trigger('to.owl.carousel', [idx, 300]);
        });

        // Mark first thumb active
        $('.product-thumb-item').first().addClass('active-thumb');
    }

    function replaceGallery(images) {
        var $g = $('.product-gallery');
        var $t = $('.product-thumbs');

        if (!images || !images.length) return;

        if ($g.data('owl.carousel')) {
            $g.owlCarousel('destroy');
        }
        $g.empty();
        $t.empty();

        $.each(images, function(i, src) {
            $g.append('<div class="item"><img src="' + src + '" class="img-fluid" alt="' + productName + '"></div>');
            $t.append('<button class="product-thumb-item mx-1 mb-2 border"><img src="' + src + '" alt="' + productName + '" style="width: 60px; height: 60px; object-fit: cover;"></button>');
        });

        initGallery();
    }

    // Variant switching
    $('#variantSelect').on('change', function() {
        var vid = $(this).val();
        if (!vid) return;

        $('#cartBtn, #wishlistBtn').attr('data-variant-id', vid);

        var imgs = allVariantImages[vid];
        if (imgs && imgs.length) {
            replaceGallery(imgs);
        }

        var opt = $(this).find('option:selected');
        var ff = opt.data('formatted-final') || '';
        var fo = opt.data('formatted-original') || '';
        var dl = opt.data('discount-label') || '';

        if (fo && dl) {
            var $o = $('#originalPrice');
            var $b = $('#discountBadge');
            if (!$o.length) {
                $('#priceSection').prepend('<h5 class="mb-0 text-decoration-line-through text-light-3" id="originalPrice">' + fo + '</h5>');
            } else {
                $o.text(fo).show();
            }
            if (!$b.length) {
                $('#priceSection').append('<span class="badge bg-success ms-2" id="discountBadge">' + dl + '</span>');
            } else {
                $b.text(dl).show();
            }
        } else {
            $('#originalPrice').remove();
            $('#discountBadge').remove();
        }
        $('#finalPrice').text(ff);
    });

    $('#qtySelect').on('change', function() {
        var qty = $(this).val();
        var vid = $('#variantSelect').val() || '';
        $('#cartBtn').attr('data-qty', qty).attr('data-variant-id', vid);
    });

    initGallery();

    $('.similar-products').owlCarousel({
        loop: true, margin: 30, nav: true, dots: false,
        responsive: {
            0: { items: 1 }, 576: { items: 2 },
            768: { items: 3 }, 992: { items: 4 }
        }
    });
});
</script>
@endpush
