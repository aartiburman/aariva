@extends('frontend.layouts.app')

@section('title', ($product->name ?? 'Product') . ' - ' . config('app.name'))

@section('meta_description', \Str::limit(strip_tags($product->description ?? $product->short_description ?? ''), 160))

@section('meta_keywords', ($product->name ?? 'product') . ', buy ' . ($product->name ?? '') . ', ' . config('app.name') . ', online shopping, best price')

@section('og_type', 'product')
@section('og_title', ($product->name ?? 'Product') . ' - ' . config('app.name'))
@section('og_description', \Str::limit(strip_tags($product->description ?? $product->short_description ?? ''), 160))
@php
    $variant = $product->firstVariant ?? $product->variants->first();
    $productOgImage = isset($variant) && $variant ? App\Helpers\ImageHelper::getProductImage($variant->image) : asset('frontend/assets/images/products/01.png');
@endphp
@section('og_image', $productOgImage)

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
                                $galleryImages = [];
                                if($product->variants->isNotEmpty()) {
                                    foreach($product->variants as $v) {
                                        if($v->image) {
                                            $imgs = is_array(json_decode($v->image, true)) ? json_decode($v->image, true) : explode(',', $v->image);
                                            foreach($imgs as $img) {
                                                $galleryImages[] = App\Helpers\ImageHelper::getProductImage(trim($img));
                                            }
                                        }
                                    }
                                    $galleryImages = array_unique($galleryImages);
                                }
                                if(empty($galleryImages)) {
                                    $galleryImages = [
                                        asset('frontend/assets/images/products/01.png'),
                                        asset('frontend/assets/images/products/02.png'),
                                        asset('frontend/assets/images/products/03.png'),
                                        asset('frontend/assets/images/products/04.png'),
                                    ];
                                }
                            @endphp
                            <div class="product-gallery owl-carousel owl-theme border mb-3 p-3" data-slider-id="1">
                                @foreach($galleryImages as $img)
                                    <div class="item">
                                        <img src="{{ $img }}" class="img-fluid" alt="{{ $product->name }}">
                                    </div>
                                @endforeach
                            </div>
                            <div class="owl-thumbs d-flex justify-content-center flex-wrap" data-slider-id="1">
                                @foreach($galleryImages as $img)
                                    <button class="owl-thumb-item mx-1 mb-2">
                                        <img src="{{ $img }}" class="" alt="{{ $product->name }}" style="width: 60px; height: 60px; object-fit: cover;">
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
                            <div class="d-flex align-items-center mt-3 gap-2">
                                @php
                                    $originalPrice = $variant ? $variant->price : 0;
                                    $finalPrice = $variant ? App\Helpers\PriceHelper::applyDiscount($variant->price, $variant->discount_type, $variant->discount_value) : 0;
                                @endphp
                                @if($finalPrice < $originalPrice)
                                    <h5 class="mb-0 text-decoration-line-through text-light-3">{{ App\Helpers\PriceHelper::formatPrice($originalPrice) }}</h5>
                                @endif
                                <h4 class="mb-0">{{ App\Helpers\PriceHelper::formatPrice($finalPrice) }}</h4>
                                @if($finalPrice < $originalPrice)
                                    <span class="badge bg-success ms-2">
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
                                            <option value="{{ $v->id }}" {{ $variant && $v->id === $variant->id ? 'selected' : '' }} data-price="{{ $v->price }}" data-discount-type="{{ $v->discount_type }}" data-discount-value="{{ $v->discount_value }}">
                                                {{ $v->product_variant_label ?? ('Variant ' . $loop->iteration) }} - {{ App\Helpers\PriceHelper::formatPrice(App\Helpers\PriceHelper::applyDiscount($v->price, $v->discount_type, $v->discount_value)) }}
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
                                <a href="javascript:;" 
                                    class="btn btn-dark btn-ecomm px-4 {{ $product->is_in_cart ? 'remove-from-cart' : 'add-to-cart' }}" 
                                    data-product-id="{{ $product->id }}" 
                                    data-variant-id="{{ $variant ? $variant->id : '' }}" 
                                    data-qty="1"
                                    id="cartBtn">
                                    <i class="bx {{ $product->is_in_cart ? 'bx-cart-x' : 'bx-cart-add' }}"></i> {{ $product->is_in_cart ? __('Remove from Cart') : __('Add to Cart') }}
                                </a>
                                <a href="javascript:;" 
                                    class="btn btn-outline-dark btn-ecomm px-4 add-to-wishlist ms-2 {{ $product->is_in_wishlist ? 'active text-danger' : '' }}" 
                                    data-product-id="{{ $product->id }}" 
                                    data-variant-id="{{ $variant ? $variant->id : '' }}" 
                                    data-qty="1"
                                    id="wishlistBtn">
                                    <i class="bx {{ $product->is_in_wishlist ? 'bxs-heart' : 'bx-heart' }}"></i> {{ $product->is_in_wishlist ? __('Remove from Wishlist') : __('Add to Wishlist') }}
                                </a>
                            </div>
                            <hr class="my-4">
                            <div class="product-sharing">
                                <h6 class="mb-2">{{ __('Share this product') }}:</h6>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="">
                                        <button type="button" class="btn-social bg-twitter"><i class="bx bxl-twitter"></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-facebook"><i class="bx bxl-facebook"></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-linkedin"><i class="bx bxl-linkedin"></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-youtube"><i class="bx bxl-youtube"></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-pinterest"><i class="bx bxl-pinterest"></i></button>
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
                                    <a href="javascript:;" class="{{ $similarProduct->is_in_cart ? 'remove-from-cart' : 'add-to-cart' }}" 
                                        data-product-id="{{ $similarProduct->id }}" 
                                        data-variant-id="{{ $similarProduct->firstVariant ? $similarProduct->firstVariant->id : '' }}" 
                                        data-qty="1">
                                        <i class="bx {{ $similarProduct->is_in_cart ? 'bx-cart-x' : 'bx-cart-add' }}"></i>
                                    </a>
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
                                        <a href="javascript:;" class="add-to-wishlist {{ $similarProduct->is_in_wishlist ? 'active text-danger' : '' }}" 
                                            data-product-id="{{ $similarProduct->id }}">
                                            <i class="bx {{ $similarProduct->is_in_wishlist ? 'bxs-heart' : 'bx-heart' }}"></i>
                                        </a>
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

@push('scripts')
<script>
$(function() {
    // Update cart button qty and variant id when select changes
    $('#qtySelect, #variantSelect').on('change', function() {
        var qty = $('#qtySelect').val();
        var variantId = $('#variantSelect').val() || '';
        $('#cartBtn').attr('data-qty', qty);
        $('#cartBtn').attr('data-variant-id', variantId);
        
        // Update displayed price when variant changes
        var selectedOption = $('#variantSelect option:selected');
        if(selectedOption.length > 0) {
            // We could update the price display here if needed
        }
    });
    
    // Initialize product gallery carousel
    $('.product-gallery').owlCarousel({
        items: 1,
        loop: true,
        margin: 0,
        nav: false,
        dots: false,
        thumbs: true,
        thumbImage: true,
        thumbContainerClass: 'owl-thumbs',
        thumbItemClass: 'owl-thumb-item'
    });
    
    // Initialize similar products carousel
    $('.similar-products').owlCarousel({
        loop: true,
        margin: 30,
        nav: true,
        dots: false,
        responsive: {
            0: { items: 1 },
            576: { items: 2 },
            768: { items: 3 },
            992: { items: 4 }
        }
    });
});
</script>
@endpush
