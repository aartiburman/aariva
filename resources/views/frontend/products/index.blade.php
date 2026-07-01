@extends('frontend.layouts.app')

@section('title', 'Shop - ' . config('app.name'))

@section('meta_description', 'Shop endless collection at ' . config('app.name') . ' - Browse thousands of products across fashion, electronics, beauty, home &amp; more. Best prices, fast shipping, easy returns.')

@section('meta_keywords', 'shop online, ' . config('app.name') . ', buy fashion, electronics, beauty products, home decor, best deals, online store')

@section('og_title', 'Shop - ' . config('app.name'))
@section('og_description', 'Shop endless collection at ' . config('app.name') . ' - Browse thousands of products across fashion, electronics, beauty, home &amp; more. Best prices, fast shipping, easy returns.')

@section('content')
<!--start breadcrumb-->
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">
                @if (request('category'))
                {{ $categories->firstWhere('slug', request('category'))->name ?? 'Shop' }}
                @elseif (request('search'))
                {{ __t('Search:') }} {{ request('search') }}
                @else
                {{ __t('Shop') }}
                @endif
            </h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            @if (request('category'))
                            {{ $categories->firstWhere('slug', request('category'))->name ?? 'Shop' }}
                            @elseif (request('search'))
                            {{ __t('Search') }}
                            @else
                            {{ __t('Shop') }}
                            @endif
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<!--end breadcrumb-->

<!--start shop area-->
<section class="py-4">
    <div class="container">
        <div class="btn btn-dark btn-ecomm d-xl-none position-fixed top-50 start-0 translate-middle-y z-index-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbarFilter"><span><i class='bx bx-filter-alt me-1'></i>{{ __t('Filters') }}</span></div>
        <div class="row">
            <div class="col-12 col-xl-3 filter-column">
                <nav class="navbar navbar-expand-xl flex-wrap p-0">
                  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbarFilter" aria-labelledby="offcanvasNavbarFilterLabel">
                    <div class="offcanvas-header">
                      <h5 class="offcanvas-title mb-0 fw-bold" id="offcanvasNavbarFilterLabel">{{ __t('Filters') }}</h5>
                      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                      <div class="filter-sidebar">
                        <div class="card rounded-0 shadow-none border">
                          <div class="card-header d-none d-xl-block bg-transparent">
                              <h5 class="mb-0 fw-bold">{{ __t('Filters') }}</h5>
                          </div>
                          <div class="card-body">
                            <h6 class="p-1 fw-bold bg-light">{{ __t('Categories') }}</h6>
                              <div class="categories">
                               <div class="categories-wrapper height-1 p-1">
                               <div class="form-check mb-2">
                                  <a href="{{ route('frontend.products.index') }}" class="text-decoration-none {{ !request('category') && !request('category_id') && !request('subcategory') ? 'fw-bold text-dark' : 'text-muted' }}">
                                    <i class='bx bx-category' ></i> {{ __t('All Products') }}
                                  </a>
                                </div>
                                @foreach ($categories as $cat)
                                <div class="form-check mb-2">
                                  <a href="{{ route('frontend.products.index', ['category' => $cat->slug]) }}" class="text-decoration-none {{ request('category') == $cat->slug ? 'fw-bold text-dark' : 'text-muted' }}">
                                    <span>{{ $cat->name }}</span><span class="product-number">({{ $cat->products_count }})</span>
                                  </a>
                                </div>
                                @endforeach
                               </div>
                            </div>
                            <hr>
                            @if($brands->count() > 0)
                            <div class="brands">
                              <h6 class="p-1 fw-bold bg-light">{{ __t('Brands') }}</h6>
                               <div class="brands-wrapper height-1 p-1">
                                @foreach($brands as $brand)
                                <div class="form-check mb-2">
                                  <a href="{{ route('frontend.products.index', ['brand' => $brand->slug]) }}" class="text-decoration-none {{ request('brand') == $brand->slug ? 'fw-bold text-dark' : 'text-muted' }}">
                                    <span>{{ $brand->name }}</span><span class="product-number">({{ $brand->products_count }})</span>
                                  </a>
                                </div>
                                @endforeach
                               </div>
                            </div>
                            @endif
                            <hr>
                            <div class="Price">
                              <h6 class="p-1 fw-bold bg-light">{{ __t('Price') }}</h6>
                               <div class="Price-wrapper p-1">
                                <form id="priceFilterForm" method="GET" action="{{ route('frontend.products.index') }}">
                                    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                                    <div class="input-group mb-2">
                                      <input type="text" name="min_price" class="form-control rounded-0" placeholder="Min" value="{{ request('min_price') }}">
                                      <span class="input-group-text bg-section-1 border-0">-</span>
                                      <input type="text" name="max_price" class="form-control rounded-0" placeholder="Max" value="{{ request('max_price') }}">
                                      <button type="submit" class="btn btn-outline-dark rounded-0 ms-2"><i class='bx bx-chevron-right me-0'></i></button>
                                    </div>
                                </form>
                               </div>
                             </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
              </nav>
            </div>
            <div class="col-12 col-xl-9">
                <div class="product-wrapper">
                    <div class="toolbox d-flex align-items-center mb-3 gap-2 border p-3">
                        <div class="d-flex flex-wrap flex-grow-1 gap-1">
                            <div class="d-flex align-items-center flex-nowrap">
                                <p class="mb-0 font-13 text-nowrap">{{ __t('Sort By:') }}</p>
                                <select class="form-select ms-3 rounded-0" onchange="if(this.value) window.location.href='{{ route('frontend.products.index') }}?sort='+this.value+'{{ request('category') ? '&category='.request('category') : '' }}'">
                                    <option value="" {{ !request('sort') ? 'selected' : '' }}>{{ __t('Default') }}</option>
                                    <option value="low_to_high" {{ request('sort') == 'low_to_high' ? 'selected' : '' }}>{{ __t('Price: Low to High') }}</option>
                                    <option value="high_to_low" {{ request('sort') == 'high_to_low' ? 'selected' : '' }}>{{ __t('Price: High to Low') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap">
                            <div class="d-flex align-items-center flex-nowrap">
                                <p class="mb-0 font-13 text-nowrap">{{ $products->total() }} {{ __t('Products found') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="product-grid">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-3 g-3 g-sm-4">
                            @forelse ($products as $product)
                            @php $slug = $product->slug ?? $product->id; $inWishlist = in_array($product->id, $wishlistProductIds ?? []); @endphp
                            <div class="col">
                                <div class="card">
                                    <div class="position-relative overflow-hidden">
                                      <div class="icon-wishlist position-absolute top-0 end-0 mt-3 me-3">
                                          <a href="javascript:;" class="add-to-wishlist {{ $inWishlist ? 'active' : '' }}" data-product-id="{{ $product->id }}"><i class="bx {{ $inWishlist ? 'bxs-heart' : 'bx-heart' }}"></i></a>
                                      </div>
                                      <a href="{{ route('frontend.products.show', $slug) }}">
                                        <img src="{{ $product->image }}" class="img-fluid" alt="{{ $product->name }}">
                                      </a>
                                      @if ($product->discount_percent > 0)
                                      <span class="discount-badge">{{ $product->discount_percent }}% {{ __t('OFF') }}</span>
                                      @endif
                                    </div>
                                    <div class="card-body px-0">
                                      <div class="d-flex align-items-center justify-content-between">
                                          <div class="">
                                              <p class="mb-1 product-short-name">{{ $product->category->name ?? '' }}</p>
                                              <h6 class="mb-0 fw-bold product-short-title"><a href="{{ route('frontend.products.show', $slug) }}" class="text-dark text-decoration-none">{{ $product->name }}</a></h6>
                                          </div>
                                      </div>
                                      <div class="cursor-pointer rating mt-2">
                                          @for ($i = 1; $i <= 5; $i++)
                                          <i class="bx {{ $i <= round($product->avg_rating) ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                          @endfor
                                          @if ($product->approvedReviews->count() > 0)
                                          <span class="rating-count">({{ $product->approvedReviews->count() }})</span>
                                          @endif
                                      </div>
                                      <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
                                          @if ($product->formatted_original_price)
                                          <div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">{{ $product->formatted_original_price }}</div>
                                          @endif
                                          <div class="h6 fw-bold">{{ $product->formatted_price }}</div>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                            @empty
                            <div class="col-12 text-center py-5">
                                <h5 class="text-muted">{{ __t('No products found') }}</h5>
                                <a href="{{ route('frontend.products.index') }}" class="btn btn-dark btn-ecomm mt-3">{{ __t('Clear Filters') }}</a>
                            </div>
                            @endforelse
                          </div><!--end row-->
                    </div>
                    <hr>
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
</section>
<!--end shop area-->
@endsection
