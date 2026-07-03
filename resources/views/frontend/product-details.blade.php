@extends('frontend.layouts.app')

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
                        <li class="breadcrumb-item active" aria-current="page">Product Details</li>
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
                            <div class="product-gallery owl-carousel owl-theme border mb-3 p-3" data-slider-id="1">
                                @if($product->images && count($product->images) > 0)
                                    @foreach($product->images as $image)
                                    <div class="item">
                                        <img src="{{ $image }}" class="img-fluid" alt="{{ $product->name }}">
                                    </div>
                                    @endforeach
                                @else
                                    <div class="item">
                                        <img src="{{ asset('frontend/assets/images/products/01.png') }}" class="img-fluid" alt="">
                                    </div>
                                    <div class="item">
                                        <img src="{{ asset('frontend/assets/images/products/02.png') }}" class="img-fluid" alt="">
                                    </div>
                                    <div class="item">
                                        <img src="{{ asset('frontend/assets/images/products/03.png') }}" class="img-fluid" alt="">
                                    </div>
                                    <div class="item">
                                        <img src="{{ asset('frontend/assets/images/products/04.png') }}" class="img-fluid" alt="">
                                    </div>
                                @endif
                            </div>
                            <div class="owl-thumbs d-flex justify-content-center" data-slider-id="1">
                                @if($product->images && count($product->images) > 0)
                                    @foreach($product->images as $image)
                                    <button class="owl-thumb-item">
                                        <img src="{{ $image }}" class="" alt="{{ $product->name }}">
                                    </button>
                                    @endforeach
                                @else
                                    <button class="owl-thumb-item">
                                        <img src="{{ asset('frontend/assets/images/products/01.png') }}" class="" alt="">
                                    </button>
                                    <button class="owl-thumb-item">
                                        <img src="{{ asset('frontend/assets/images/products/02.png') }}" class="" alt="">
                                    </button>
                                    <button class="owl-thumb-item">
                                        <img src="{{ asset('frontend/assets/images/products/03.png') }}" class="" alt="">
                                    </button>
                                    <button class="owl-thumb-item">
                                        <img src="{{ asset('frontend/assets/images/products/04.png') }}" class="" alt="">
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="product-info-section p-3">
                            <h3 class="mt-3 mt-lg-0 mb-0">{{ $product->name }}</h3>
                            <div class="product-rating d-flex align-items-center mt-2">
                                <div class="rates cursor-pointer font-13">
                                    <i class="bx bxs-star text-warning"></i>
                                    <i class="bx bxs-star text-warning"></i>
                                    <i class="bx bxs-star text-warning"></i>
                                    <i class="bx bxs-star text-warning"></i>
                                    <i class="bx bxs-star text-light-4"></i>
                                </div>
                                <div class="ms-1">
                                    <p class="mb-0">({{ $product->reviews_count ?? 0 }} Reviews)</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-3 gap-2">
                                @if($product->original_price > $product->price)
                                <h5 class="mb-0 text-decoration-line-through text-light-3">
                                    {{ \App\Helpers\CurrencyHelper::formatCurrency($product->original_price) }}
                                </h5>
                                @endif
                                <h4 class="mb-0">
                                    {{ \App\Helpers\CurrencyHelper::formatCurrency($product->price) }}
                                </h4>
                            </div>
                            <div class="mt-3">
                                <h6>{{ __t('Description') }}:</h6>
                                <p class="mb-0">{{ $product->short_description ?? $product->description ?? 'No description available.' }}</p>
                            </div>
                            <dl class="row mt-3">
                                <dt class="col-sm-3">{{ __t('Product ID') }}:</dt>
                                <dd class="col-sm-9">#{{ $product->id }}</dd>
                            </dl>
                            <div class="row row-cols-auto align-items-center mt-3">
                                <div class="col">
                                    <label class="form-label">{{ __t('Quantity') }}:</label>
                                    <select class="form-select form-select-sm" id="qtySelect">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                @if($product->variants && count($product->variants) > 0)
                                <div class="col">
                                    <label class="form-label">{{ __t('Variant') }}:</label>
                                    <select class="form-select form-select-sm" id="variantSelect">
                                        @foreach($product->variants as $variant)
                                        <option value="{{ $variant->id }}" {{ $loop->first ? 'selected' : '' }}>
                                            {{ $variant->name ?? 'Variant ' . $loop->iteration }} - {{ \App\Helpers\CurrencyHelper::formatCurrency($variant->price) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <a href="javascript:;" 
                                    class="btn btn-dark btn-ecomm px-4 {{ $product->is_in_cart ? 'remove-from-cart' : 'add-to-cart' }}" 
                                    data-product-id="{{ $product->id }}" 
                                    data-variant-id="{{ $product->firstVariant ? $product->firstVariant->id : '' }}" 
                                    data-qty="1"
                                    id="cartBtn">
                                    <i class='bx {{ $product->is_in_cart ? "bx-cart-x" : "bx-cart-add" }}'></i> {{ $product->is_in_cart ? __t('Remove from Cart') : __t('Add to Cart') }}
                                </a>
                                <a href="javascript:;" 
                                    class="btn btn-outline-dark btn-ecomm px-4 add-to-wishlist ms-2 {{ $product->is_in_wishlist ? 'active text-danger' : '' }}" 
                                    data-product-id="{{ $product->id }}" 
                                    data-variant-id="{{ $product->firstVariant ? $product->firstVariant->id : '' }}" 
                                    data-qty="1"
                                    id="wishlistBtn">
                                    <i class='bx {{ $product->is_in_wishlist ? "bxs-heart" : "bx-heart" }}'></i> {{ $product->is_in_wishlist ? __t('Remove from Wishlist') : __t('Add to Wishlist') }}
                                </a>
                            </div>
                            <hr>
                            <div class="product-sharing">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="">
                                        <button type="button" class="btn-social bg-twitter"><i class='bx bxl-twitter'></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-facebook"><i class='bx bxl-facebook'></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-linkedin"><i class='bx bxl-linkedin'></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-youtube"><i class='bx bxl-youtube'></i></button>
                                    </div>
                                    <div class="">
                                        <button type="button" class="btn-social bg-pinterest"><i class='bx bxl-pinterest'></i></button>
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
                            <div class="tab-title text-uppercase fw-500">{{ __t('Description') }}</div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#reviews">
                        <div class="d-flex align-items-center">
                            <div class="tab-title text-uppercase fw-500">({{ $product->reviews_count ?? 0 }}) {{ __t('Reviews') }}</div>
                        </div>
                    </a>
                </li>
            </ul>
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="discription">
                    <p>{{ $product->description ?? 'No detailed description available.' }}</p>
                </div>
                <div class="tab-pane fade" id="reviews">
                    <div class="row">
                        <div class="col col-lg-8">
                            <div class="product-review">
                                <h5 class="mb-4">{{ __t('Reviews for the Product') }}</h5>
                                <div class="review-list">
                                    @if($product->reviews && count($product->reviews) > 0)
                                        @foreach($product->reviews as $review)
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="review-user">
                                                <img src="{{ asset('frontend/assets/images/avatars/avatar-1.png') }}" width="65" height="65" class="rounded-circle" alt="">
                                            </div>
                                            <div class="review-content ms-3">
                                                <div class="rates cursor-pointer fs-6">
                                                    @for($i=1; $i<=5; $i++)
                                                        <i class="bx {{ $i <= $review->rating ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                                    @endfor
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="mb-0">{{ $review->user->name ?? 'Anonymous' }}</h6>
                                                    <p class="mb-0 ms-auto">{{ $review->created_at->format('F d, Y') }}</p>
                                                </div>
                                                <p>{{ $review->comment ?? '' }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <p>{{ __t('No reviews yet.') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col col-lg-4">
                            <div class="add-review border">
                                <div class="form-body p-3">
                                    <h4 class="mb-4">{{ __t('Write a Review') }}</h4>
                                    <form id="reviewForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">{{ __t('Your Name') }}</label>
                                            <input type="text" class="form-control rounded-0" name="name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __t('Your Email') }}</label>
                                            <input type="email" class="form-control rounded-0" name="email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __t('Rating') }}</label>
                                            <select class="form-select rounded-0" name="rating" required>
                                                <option value="" selected>{{ __t('Choose Rating') }}</option>
                                                <option value="5">5</option>
                                                <option value="4">4</option>
                                                <option value="3">3</option>
                                                <option value="2">2</option>
                                                <option value="1">1</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __t('Your Review') }}</label>
                                            <textarea class="form-control rounded-0" name="comment" rows="3" required></textarea>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-dark btn-ecomm">{{ __t('Submit a Review') }}</button>
                                        </div>
                                    </form>
                                </div>
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
@if($similarProducts && count($similarProducts) > 0)
<section class="py-4">
    <div class="container">
        <div class="separator pb-4">
            <div class="line"></div>
            <h5 class="mb-0 fw-bold separator-title">{{ __t('Similar Products') }}</h5>
            <div class="line"></div>
        </div>
        <div class="product-grid">
            <div class="similar-products owl-carousel owl-theme position-relative">
                @foreach($similarProducts as $similarProduct)
                <div class="item">
                    <div class="card">
                        <div class="position-relative overflow-hidden">
                            <div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
                                <a href="javascript:;" class="{{ $similarProduct->is_in_cart ? 'remove-from-cart' : 'add-to-cart' }}" 
                                    data-product-id="{{ $similarProduct->id }}" 
                                    data-variant-id="{{ $similarProduct->firstVariant ? $similarProduct->firstVariant->id : '' }}" 
                                    data-qty="1">
                                    <i class='bx {{ $similarProduct->is_in_cart ? "bx-cart-x" : "bx-cart-add" }}'></i>
                                </a>
                            </div>
                            <div class="quick-view position-absolute start-0 bottom-0 end-0">
                                <a href="{{ route('frontend.products.show', $similarProduct->slug) }}" class="btn btn-light btn-sm">{{ __t('View Product') }}</a>
                            </div>
                            <a href="{{ route('frontend.products.show', $similarProduct->slug) }}">
                                <img src="{{ $similarProduct->featured_image ?? asset('frontend/assets/images/similar-products/01.png') }}" class="img-fluid" alt="{{ $similarProduct->name }}">
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
                                        <i class='bx {{ $similarProduct->is_in_wishlist ? "bxs-heart" : "bx-heart" }}'></i>
                                    </a>
                                </div>
                            </div>
                            <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
                                @if($similarProduct->original_price > $similarProduct->price)
                                    <div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">
                                        {{ \App\Helpers\CurrencyHelper::formatCurrency($similarProduct->original_price) }}
                                    </div>
                                @endif
                                <div class="h6 fw-bold">{{ \App\Helpers\CurrencyHelper::formatCurrency($similarProduct->price) }}</div>
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
@endsection

@push('scripts')
<script>
$(function() {
    // Update cart button qty and variant id when select changes
    $('#qtySelect, #variantSelect').on('change', function() {
        var qty = $('#qtySelect').val();
        var variantId = $('#variantSelect').val() || '';
        $('#cartBtn').attr('data-qty', qty);
        $('#cartBtn').attr('data-variant-id', variantId);
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
