@extends('frontend.layouts.app')

@section('content')
<section class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0 fw-bold text-uppercase">{{ __t('Categories') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <a href="{{ route('frontend.products.index') }}" class="text-decoration-none {{ !request('category') && !request('category_id') && !request('subcategory') ? 'fw-bold text-dark' : '' }}">{{ __t('All Products') }}</a>
                            </li>
                            @foreach ($categories as $cat)
                            <li class="list-group-item">
                                <a href="{{ route('frontend.products.index', ['category' => $cat->slug]) }}" class="text-decoration-none {{ request('category') == $cat->slug ? 'fw-bold text-dark' : '' }}">{{ $cat->name }}</a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="mb-0 fw-bold text-uppercase">
                        @if (request('category'))
                        {{ $categories->firstWhere('slug', request('category'))->name ?? 'Shop' }}
                        @elseif (request('search'))
                        {{ __t('Search:') }} {{ request('search') }}
                        @else
                        {{ __t('All Products') }}
                        @endif
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">{{ $products->total() }} {{ __t('Products found') }}</span>
                    </div>
                </div>

                <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-sm-4">
                    @forelse ($products as $product)
                    @php
                        $variant = $product->firstVariant;
                        $image = $variant ? App\Helpers\ImageHelper::getProductImage($variant->image) : asset('frontend/assets/images/products/01.png');
                        $originalPrice = $variant ? $variant->price : 0;
                        $finalPrice = $variant ? App\Helpers\PriceHelper::applyDiscount($variant->price, $variant->discount_type, $variant->discount_value) : 0;
                        $avgRating = $product->approvedReviews->avg('rating') ?? 0;
                        $inWishlist = in_array($product->id, $wishlistProductIds ?? []);
                        $slug = $product->slug ?? $product->id;
                        $discountPercent = $originalPrice > 0 ? round((1 - $finalPrice / $originalPrice) * 100) : 0;
                    @endphp
                    <div class="col">
                        <div class="product-card-modern h-100">
                            <div class="product-image-wrap">
                                <a href="{{ route('frontend.products.show', $slug) }}">
                                    <img src="{{ $image }}" alt="{{ $product->name }}">
                                </a>
                                <div class="product-overlay"></div>
                                <div class="product-actions">
                                    <a href="javascript:;" class="action-btn {{ in_array($product->id, $cartProductIds) ? 'remove-from-cart' : 'add-to-cart' }}" data-product-id="{{ $product->id }}" title="{{ in_array($product->id, $cartProductIds) ? 'Remove from Cart' : 'Add to Cart' }}">
                                        <i class='bx {{ in_array($product->id, $cartProductIds) ? "bx-cart-x" : "bx-cart-add" }}'></i>
                                    </a>
                                    <a href="javascript:;" class="action-btn add-to-wishlist {{ $inWishlist ? 'active' : '' }}" data-product-id="{{ $product->id }}" title="Add to Wishlist">
                                        <i class="bx {{ $inWishlist ? 'bxs-heart' : 'bx-heart' }}"></i>
                                    </a>
                                </div>
                                <div class="quick-view-overlay">
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct" data-product-id="{{ $product->id }}">
                                        <i class='bx bx-show-alt'></i> {{ __t('Quick View') }}
                                    </a>
                                </div>
                                @if ($discountPercent > 0)
                                <span class="discount-badge">{{ $discountPercent }}% {{ __t('OFF') }}</span>
                                @endif
                            </div>
                            <div class="product-body">
                                <a href="javascript:;" class="product-category">{{ $product->category->name ?? '' }}</a>
                                <a href="{{ route('frontend.products.show', $slug) }}" class="product-title">{{ $product->name }}</a>
                                <div class="product-rating">
                                    @for ($i = 1; $i <= 5; $i++)
                                    <i class="bx {{ $i <= round($avgRating) ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                    @endfor
                                    @if ($product->approvedReviews->count() > 0)
                                    <span class="rating-count">({{ $product->approvedReviews->count() }})</span>
                                    @endif
                                </div>
                                <div class="product-pricing">
                                    <span class="current-price">{{ App\Helpers\PriceHelper::formatPrice($finalPrice) }}</span>
                                    @if ($finalPrice < $originalPrice)
                                    <span class="old-price">{{ App\Helpers\PriceHelper::formatPrice($originalPrice) }}</span>
                                    @endif
                                </div>
                                <a href="javascript:;" class="add-to-cart-btn {{ in_array($product->id, $cartProductIds) ? 'remove-from-cart' : 'add-to-cart' }}" data-product-id="{{ $product->id }}">
                                    <i class='bx {{ in_array($product->id, $cartProductIds) ? "bx-cart-x" : "bx-cart-add" }}'></i> {{ in_array($product->id, $cartProductIds) ? __t('Remove from Cart') : __t('Add to Cart') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5">
                        <h5 class="text-muted">{{ __t('No products found') }}</h5>
                        <a href="{{ route('frontend.products.index') }}" class="btn btn-dark btn-ecomm mt-3">{{ __t('Clear Filters') }}</a>
                    </div>
                    @endforelse
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
