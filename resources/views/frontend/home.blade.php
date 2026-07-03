@extends('frontend.layouts.app')

@section('title', config('app.name'))

@section('meta_description', 'Welcome to ' . config('app.name') . ' - Explore endless collection of fashion, electronics, beauty, home essentials & more. Shop now for exclusive deals with fast delivery.')

@section('meta_keywords', 'Aariva, one store endless collection, online shopping, fashion, electronics, beauty, home decor, best deals, ecommerce store')

@section('og_title', config('app.name'))
@section('og_description', 'Welcome to ' . config('app.name') . ' - Explore endless collection of fashion, electronics, beauty, home essentials & more. Shop now for exclusive deals with fast delivery.')

@section('before-page-wrapper')
<!--start slider section-->
		<section class="slider-section mb-4">
			<div class="first-slider p-0">

				<div class="banner-slider owl-carousel owl-theme">
					@forelse ($heroBanners as $banner)
					<div class="item">
						<div class="position-relative">
							<div class="position-absolute top-50 slider-content translate-middle">
								<h3 class="h3 fw-bold d-none d-md-block">{{ $banner->title }}</h3>
								<div class=""><a class="btn btn-dark btn-ecomm px-4" href="{{ $banner->link_url ?? route('frontend.products.index') }}">{{ __t('Shop Now') }}</a>
								</div>
							  </div>
							<a href="{{ $banner->link_url ?? route('frontend.products.index') }}">
								<img src="{{ is_array($banner->image) ? ($banner->image[0] ?? '') : $banner->image }}" class="img-fluid" alt="{{ $banner->title }}">
							</a>
						</div>
					</div>
					@empty
					<div class="item">
						<div class="position-relative">
							<img src="{{ asset('frontend/assets/images/banners/01.png') }}" class="img-fluid" alt="...">
						</div>
					</div>
					@endforelse
				</div>

			</div>
		</section>
		<!--end slider section-->
@endsection

@section('content')
<!--start information-->
				<section class="py-4">
					<div class="container">

						<div class="row row-cols-1 row-cols-lg-3 g-4">
							<div class="col">
								<div class="d-flex align-items-center justify-content-center p-3 border">
									<div class="fs-1 text-content"><i class='bx bx-taxi'></i>
									</div>
									<div class="info-box-content ps-3">
										<h6 class="mb-0 fw-bold">{{ __t('FREE SHIPPING &amp; RETURN') }}</h6>
										<p class="mb-0">{{ __t('Free shipping on all orders over $49') }}</p>
									</div>
								</div>
							</div>
	
							<div class="col">
								<div class="d-flex align-items-center justify-content-center p-3 border">
									<div class="fs-1 text-content"><i class='bx bx-dollar-circle'></i>
									</div>
									<div class="info-box-content ps-3">
										<h6 class="mb-0 fw-bold">{{ __t('MONEY BACK GUARANTEE') }}</h6>
										<p class="mb-0">{{ __t('100% money back guarantee') }}</p>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="d-flex align-items-center justify-content-center p-3 border">
									<div class="fs-1 text-content"><i class='bx bx-support'></i>
									</div>
									<div class="info-box-content ps-3">
										<h6 class="mb-0 fw-bold">{{ __t('ONLINE SUPPORT 24/7') }}</h6>
										<p class="mb-0">{{ __t('Awesome Support for 24/7 Days') }}</p>
									</div>
								</div>
							</div>
						</div>
						<!--end row-->
					</div>
				</section>
				<!--end information-->
				<!--start pramotion-->
				<section class="py-4">
					<div class="container">
						<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3 g-4">
							<div class="col">
								<div class="card rounded-0 shadow-none bg-info bg-opacity-25">
									<div class="row g-0 align-items-center">
										<div class="col">
											<img src="{{ asset('frontend/assets/images/promo/01.png') }}" class="img-fluid" alt="" />
										</div>
										<div class="col">
											<div class="card-body">
												<h5 class="card-title text-uppercase fw-bold">{{ __t('Men Wear') }}</h5>
												<p class="card-text text-uppercase">{{ __t('Starting at $9') }}</p>
												<a href="{{ route('frontend.products.index') }}" class="btn btn-outline-dark btn-ecomm">{{ __t('SHOP NOW') }}</a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="card rounded-0 shadow-none bg-danger bg-opacity-25">
									<div class="row g-0 align-items-center">
										<div class="col">
											<img src="{{ asset('frontend/assets/images/promo/02.png') }}" class="img-fluid" alt="" />
										</div>
										<div class="col">
											<div class="card-body">
												<h5 class="card-title text-uppercase fw-bold">{{ __t('Women Wear') }}</h5>
												<p class="card-text text-uppercase">{{ __t('Starting at $9') }}</p>	<a href="{{ route('frontend.products.index') }}" class="btn btn-outline-dark btn-ecomm">{{ __t('SHOP NOW') }}</a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="card rounded-0 shadow-none bg-warning bg-opacity-25">
									<div class="row g-0 align-items-center">
										<div class="col">
											<img src="{{ asset('frontend/assets/images/promo/03.png') }}" class="img-fluid" alt="" />
										</div>
										<div class="col">
											<div class="card-body">
												<h5 class="card-title text-uppercase fw-bold">{{ __t('Kids Wear') }}</h5>
												<p class="card-text text-uppercase">{{ __t('Starting at $9') }}</p><a href="{{ route('frontend.products.index') }}" class="btn btn-outline-dark btn-ecomm">{{ __t('SHOP NOW') }}</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!--end row-->
					</div>
				</section>
				<!--end pramotion-->
				<!--start Category Products-->
				@foreach ($categoryProducts as $catId => $products)
				@php $category = $products->first()->category; @endphp
				<section class="py-4">
					<div class="container">
						<div class="separator pb-4">
							<div class="line"></div>
							<h5 class="mb-0 fw-bold separator-title">{{ $category->name ?? __t('Products') }}</h5>
							<div class="line"></div>
						  </div>
						<div class="product-grid">
							<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-5 g-3 g-sm-4">
								@foreach ($products as $product)
								@php $inWishlist = in_array($product->id, $wishlistProductIds ?? []); $slug = $product->slug ?? $product->id; @endphp
								<div class="col">
									<div class="product-card-modern h-100" data-url="{{ route('frontend.products.show', $slug) }}">
										<div class="product-image-wrap">
											<a href="{{ route('frontend.products.show', $slug) }}">
												<img src="{{ $product->image }}" alt="{{ $product->name }}">
											</a>
											<div class="product-overlay"></div>
											<div class="product-actions">
									<a href="javascript:;" class="action-btn add-to-wishlist {{ $inWishlist ? 'active' : '' }}" data-product-id="{{ $product->id }}" title="Add to Wishlist">
										<i class="bx {{ $inWishlist ? 'bxs-heart' : 'bx-heart' }}"></i>
									</a>
								</div>
											<div class="quick-view-overlay">
												<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct" data-product-id="{{ $product->id }}">
													<i class='bx bx-show-alt'></i> Quick View
												</a>
											</div>
											@if ($product->discount_percent > 0)
											<span class="discount-badge">{{ $product->discount_percent }}% OFF</span>
											@endif
										</div>
										<div class="product-body">
											<a href="{{ route('frontend.products.index', ['category' => $category->slug ?? '']) }}" class="product-category">{{ $category->name ?? '' }}</a>
											<a href="{{ route('frontend.products.show', $slug) }}" class="product-title">{{ $product->name }}</a>
											<div class="product-rating">
												@for ($i = 1; $i <= 5; $i++)
												<i class="bx {{ $i <= round($product->avg_rating) ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
												@endfor
												@if ($product->approvedReviews->count() > 0)
												<span class="rating-count">({{ $product->approvedReviews->count() }})</span>
												@endif
											</div>
											<div class="product-pricing">
												<span class="current-price">{{ $product->formatted_price }}</span>
												@if ($product->formatted_original_price)
												<span class="old-price">{{ $product->formatted_original_price }}</span>
												@endif
											</div>
										</div>
									</div>
								</div>
								@endforeach
							  </div><!--end row-->
						</div>
					</div>
				</section>
				@endforeach
				<!--end Category Products-->
				<!--start New Arrivals-->
				<section class="py-4">
					<div class="container">
						 <div class="separator pb-4">
							<div class="line"></div>
							<h5 class="mb-0 fw-bold separator-title">{{ __t('New Arrivals') }}</h5>
							<div class="line"></div>
						 </div>
						<div class="product-grid">
							<div class="new-arrivals owl-carousel owl-theme position-relative">
								@foreach ($newArrivals as $product)
								@php $inWishlist = in_array($product->id, $wishlistProductIds ?? []); $slug = $product->slug ?? $product->id; @endphp
								 <div class="item">
									<div class="product-card-modern h-100" data-url="{{ route('frontend.products.show', $slug) }}">
										<div class="product-image-wrap">
											<a href="{{ route('frontend.products.show', $slug) }}">
												<img src="{{ $product->image }}" alt="{{ $product->name }}">
											</a>
											<div class="product-overlay"></div>
											<div class="product-actions">
									<a href="javascript:;" class="action-btn add-to-wishlist {{ $inWishlist ? 'active' : '' }}" data-product-id="{{ $product->id }}" title="Add to Wishlist">
										<i class="bx {{ $inWishlist ? 'bxs-heart' : 'bx-heart' }}"></i>
									</a>
								</div>
											<div class="quick-view-overlay">
												<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct" data-product-id="{{ $product->id }}">
													<i class='bx bx-show-alt'></i> {{ __t('Quick View') }}
												</a>
											</div>
											@if ($product->discount_percent > 0)
											<span class="discount-badge">{{ $product->discount_percent }}% {{ __t('OFF') }}</span>
											@endif
										</div>
										<div class="product-body">
											<a href="{{ route('frontend.products.index', ['category' => $product->category->slug ?? '']) }}" class="product-category">{{ $product->category->name ?? '' }}</a>
											<a href="{{ route('frontend.products.show', $slug) }}" class="product-title">{{ $product->name }}</a>
											<div class="product-rating">
												@for ($i = 1; $i <= 5; $i++)
												<i class="bx {{ $i <= round($product->avg_rating) ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
												@endfor
												@if ($product->approvedReviews->count() > 0)
												<span class="rating-count">({{ $product->approvedReviews->count() }})</span>
												@endif
											</div>
											<div class="product-pricing">
												<span class="current-price">{{ $product->formatted_price }}</span>
												@if ($product->formatted_original_price)
												<span class="old-price">{{ $product->formatted_original_price }}</span>
												@endif
											</div>
										</div>
									</div>
								</div>
								@endforeach
							</div>
						</div>
					</div>
				</section>
				<!--end New Arrivals-->
				<!--start Advertise banners-->
				<section class="py-4" style="background: #282828;">
					<div class="container">
						<div class="add-banner">
							<div class="row g-3">
								@forelse ($promoBanners as $promo)
								@php
									$promoImages = $promo->image ?? [];
									$promoImages = is_array($promoImages) ? $promoImages : (array)$promoImages;
									$promoLink = $promo->link_url ?? 'javascript:;';
									$promoBtnText = __('Shop Now');
									$imgCount = count($promoImages);
									$promoTitle = $promo->title ?? __t('Sale');
									$promoDesc = $promo->slug ?? __t('Explore the collection');
									$promoDiscount = $promo->title_ar ?? '-10%';
								@endphp
								@if($imgCount >= 5)
								<div class="col-12 col-md-6 col-lg-3 d-flex">
									<div class="card rounded-0 w-100 border-0 shadow-none">
										<div id="promoCarousel{{ $loop->index }}" class="carousel slide" data-bs-ride="carousel">
											<div class="carousel-inner">
												@foreach($promoImages as $pi => $promoImg)
												<div class="carousel-item {{ $pi === 0 ? 'active' : '' }}">
													<img src="{{ $promoImg }}" class="d-block w-100" alt="{{ $promo->title }}">
												</div>
												@endforeach
											</div>
											<button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel{{ $loop->index }}" data-bs-slide="prev">
												<span class="carousel-control-prev-icon" aria-hidden="true"></span>
												<span class="visually-hidden">Previous</span>
											</button>
											<button class="carousel-control-next" type="button" data-bs-target="#promoCarousel{{ $loop->index }}" data-bs-slide="next">
												<span class="carousel-control-next-icon" aria-hidden="true"></span>
												<span class="visually-hidden">Next</span>
											</button>
										</div>
										<div class="card-body text-center" style="background: #f5f5f5;">
											<h5 class="card-title" style="font-size: 18px; margin-bottom: 8px;">{{ $promoTitle }}</h5>
											<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 12px;">{{ $promoDesc }}</p>
											<a href="{{ $promoLink }}" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">{{ $promoBtnText }}</a>
										</div>
									</div>
								</div>
								@else
									@if(!empty($promoImages))
										@foreach($promoImages as $promoImg)
										<div class="col-12 col-md-6 col-lg-3 d-flex">
											<div class="card rounded-0 w-100 border-0 shadow-none">
												<div class="position-relative">
													<img src="{{ $promoImg }}" class="img-fluid w-100" style="object-fit: cover;" alt="{{ $promo->title }}">
													<div class="position-absolute" style="top: 15px; right: 15px; background: #ff4757; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
														{{ $promoDiscount }}
													</div>
												</div>
												<div class="card-body text-center" style="background: #f5f5f5;">
													<h5 class="card-title" style="font-size: 18px; margin-bottom: 8px;">{{ $promoTitle }}</h5>
													<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 12px;">{{ $promoDesc }}</p>
													<a href="{{ $promoLink }}" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">{{ $promoBtnText }}</a>
												</div>
											</div>
										</div>
										@endforeach
									@else
										<div class="col-12 col-md-6 col-lg-3 d-flex">
											<div class="card rounded-0 w-100 border-0 shadow-none">
												<div class="position-relative">
													<img src="{{ asset('frontend/assets/images/promo/0' . ($loop->index + 1) . '.png') }}" class="img-fluid w-100" style="object-fit: cover;" alt="{{ $promo->title }}">
													<div class="position-absolute" style="top: 15px; right: 15px; background: #ff4757; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
														{{ $promoDiscount }}
													</div>
												</div>
												<div class="card-body text-center" style="background: #f5f5f5;">
													<h5 class="card-title" style="font-size: 18px; margin-bottom: 8px;">{{ $promoTitle }}</h5>
													<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 12px;">{{ $promoDesc }}</p>
													<a href="{{ $promoLink }}" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">{{ $promoBtnText }}</a>
												</div>
											</div>
										</div>
									@endif
								@endif
								@empty
								<div class="col-12 col-md-6 col-lg-3 d-flex">
									<div class="card rounded-0 w-100 border-0 shadow-none">
										<div class="position-relative">
											<img src="{{ asset('frontend/assets/images/promo/04.png') }}" class="img-fluid w-100" style="object-fit: cover;" alt="...">
											<div class="position-absolute" style="top: 15px; right: 15px; background: #ff4757; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">-10%</div>
										</div>
										<div class="card-body text-center" style="background: #f5f5f5;">
											<h5 class="card-title" style="font-size: 18px; margin-bottom: 8px;">{{ __t('Sunglasses Sale') }}</h5>
											<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 12px;">{{ __t('See all Sunglasses and get 10% off at all Sunglasses') }}</p>
											<a href="javascript:;" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">{{ __t('SHOP BY GLASSES') }}</a>
										</div>
									</div>
								</div>
								<div class="col-12 col-md-6 col-lg-3 d-flex">
									<div class="card rounded-0 w-100 border-0 shadow-none">
										<div class="position-relative">
											<img src="{{ asset('frontend/assets/images/promo/08.png') }}" class="img-fluid w-100" style="object-fit: cover;" alt="...">
											<div class="position-absolute" style="top: 15px; right: 15px; background: #ff4757; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">-80%</div>
										</div>
										<div class="card-body text-center" style="background: #f5f5f5;">
											<h5 class="card-title" style="font-size: 18px; margin-bottom: 8px;">{{ __t('Cosmetics Sales') }}</h5>
											<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 12px;">{{ __t('Buy Cosmetics products and get 30% off at all Cosmetics') }}</p>
											<a href="javascript:;" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">{{ __t('SHOP BY COSMETICS') }}</a>
										</div>
									</div>
								</div>
								<div class="col-12 col-md-6 col-lg-3 d-flex">
									<div class="card rounded-0 w-100 border-0 shadow-none">
										<div class="position-relative">
											<img src="{{ asset('frontend/assets/images/promo/06.png') }}" class="img-fluid w-100" style="object-fit: cover;" alt="...">
											<div class="card-img-overlay d-flex flex-column justify-content-center" style="padding: 20px;">
												<h5 class="card-title text-white mb-2" style="font-size: 20px;">{{ __t('Fashion Summer Sale') }}</h5>
												<p class="card-text text-white text-uppercase" style="font-size: 36px; font-weight: bold; line-height: 1.2; margin-bottom: 10px;">{{ __t('Up to 80% off') }}</p>
												<p class="card-text text-white mb-3" style="font-size: 16px;">{{ __t('On Top Fashion Brands') }}</p>
												<div class="position-absolute bottom-0 left-0 w-100 text-center" style="background: rgba(255,255,255,0.9); padding: 10px;">
													<a href="javascript:;" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">{{ __t('SHOP BY FASHION') }}</a>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-12 col-md-6 col-lg-3 d-flex">
									<div class="card rounded-0 w-100 border-0 shadow-none" style="background: #f5f5f5;">
										<div class="position-relative">
											<div class="position-absolute" style="top: 15px; right: 15px; background: #ff4757; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">-50%</div>
										</div>
										<div class="card-body d-flex flex-column justify-content-center" style="padding: 40px 20px;">
											<h5 class="card-title fw-bold" style="font-size: 28px; margin-bottom: 5px;">{{ __t('SUPER SALE') }}</h5>
											<p class="card-text text-uppercase mb-2" style="font-size: 18px;">{{ __t('Up to 50% off') }}</p>
											<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 15px;">{{ __t('On All Electronic') }}</p>
											<a href="javascript:;" class="btn btn-dark btn-sm align-self-center" style="padding: 5px 20px; border-radius: 0;">{{ __t('HURRY UP!') }}</a>
										</div>
									</div>
								</div>
								@endforelse
							</div>
							<!--end row-->
						</div>
					</div>
				</section>
				<!--end Advertise banners-->
				<!--start categories-->
				<section class="py-4">
					<div class="container">
						<div class="separator pb-4">
							<div class="line"></div>
							<h5 class="mb-0 fw-bold separator-title">{{ __t('Browse Category') }}</h5>
							<div class="line"></div>
						 </div>

						<div class="product-grid">
							<div class="browse-category owl-carousel owl-theme">
								@foreach ($categories as $cat)
								@php
									$catImg = $cat->image ?? asset('frontend/assets/images/categories/01.png');
									$productCount = $cat->products->count();
								@endphp
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<a href="{{ route('frontend.products.index', ['category' => $cat->slug]) }}">
												<img src="{{ $catImg }}" class="img-fluid" alt="{{ $cat->name }}">
											</a>
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">{{ $cat->name }}</h6>
											<p class="mb-0 font-12 text-uppercase">{{ $productCount }} {{ __t('Products') }}</p>
										</div>
									</div>
								</div>
								@endforeach
							</div>
						</div>
					</div>
				</section>
				<!--end categories-->
				<!--start support info-->
				<section class="py-5 bg-light">
					<div class="container">
						<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">
							<div class="col">
								<div class="text-center border p-3 bg-white">
									<div class="font-50 text-dark"><i class='bx bx-cart-add' ></i>
									</div>
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">{{ __t('Free delivery') }}</h5>
									<p class="text-capitalize">{{ __t('Free delivery over $199') }}</p>
									<p>{{ __t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.') }}</p>
								</div>
							</div>
							<div class="col">
								<div class="text-center border p-3 bg-white">
									<div class="font-50 text-dark"><i class='bx bx-credit-card'></i>
									</div>
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">{{ __t('Secure payment') }}</h5>
									<p class="text-capitalize">{{ __t('We possess SSL / Secure сertificate') }}</p>
									<p>{{ __t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.') }}</p>
								</div>
							</div>
							<div class="col">
								<div class="text-center border p-3 bg-white">
									<div class="font-50 text-dark">	<i class='bx bx-dollar-circle'></i>
									</div>
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">{{ __t('Free returns') }}</h5>
									<p class="text-capitalize">{{ __t('We return money within 30 days') }}</p>
									<p>{{ __t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.') }}</p>
								</div>
							</div>
							<div class="col">
								<div class="text-center border p-3 bg-white">
									<div class="font-50 text-dark">	<i class='bx bx-support'></i>
									</div>
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">{{ __t('Customer Support') }}</h5>
									<p class="text-capitalize">{{ __t('Friendly 24/7 customer support') }}</p>
									<p>{{ __t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.') }}</p>
								</div>
							</div>
						</div>
						<!--end row-->
					</div>
				</section>
				<!--end support info-->
				<!--start News-->
				<section class="py-4">
					<div class="container">
						<div class="pb-4 text-center">
							<h5 class="mb-0 fw-bold text-uppercase">{{ __t('Latest News') }}</h5>
						 </div>
						<div class="product-grid">
							<div class="latest-news owl-carousel owl-theme">
								@forelse ($blogPosts as $post)
								@php
									$postImg = $post->image ? App\Helpers\ImageHelper::getBlogImage($post->image) : asset('frontend/assets/images/blogs/0' . ($loop->index + 1) . '.png');
									$postDate = $post->created_at ? date('d', strtotime($post->created_at)) : '24';
									$postMonth = $post->created_at ? date('M', strtotime($post->created_at)) : 'FEB';
									$postTitle = \Str::limit($post->title, 30);
									$postDesc = \Str::limit(strip_tags($post->description ?? $post->content ?? ''), 100);
								@endphp
								<div class="item">
									<div class="card rounded-0 product-card border">
										<div class="news-date">
											<div class="date-number">{{ $postDate }}</div>
											<div class="date-month">{{ $postMonth }}</div>
										</div>
										<a href="{{ route('frontend.blog.show', $post->slug) }}">
											<img src="{{ $postImg }}" class="card-img-top border-bottom" alt="{{ $post->title }}">
										</a>
										<div class="card-body">
											<div class="news-title">
												<a href="{{ route('frontend.blog.show', $post->slug) }}">
													<h5 class="mb-3 text-capitalize">{{ $postTitle }}</h5>
												</a>
											</div>
											<p class="news-content mb-0">{{ $postDesc }}</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="{{ route('frontend.blog.show', $post->slug) }}" class="link-dark">{{ __t('Read More') }}</a>
										</div>
									</div>
								</div>
								@empty
								<div class="item">
									<div class="card rounded-0 product-card border">
										<div class="news-date">
											<div class="date-number">24</div>
											<div class="date-month">FEB</div>
										</div>
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/blogs/01.png') }}" class="card-img-top border-bottom" alt="...">
										</a>
										<div class="card-body">
											<div class="news-title">
												<a href="javascript:;">
													<h5 class="mb-3 text-capitalize">{{ __t('Blog Short Title') }}</h5>
												</a>
											</div>
											<p class="news-content mb-0">{{ __t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...') }}</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">{{ __t('0 Comments') }}</a>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0 product-card border">
										<div class="news-date">
											<div class="date-number">24</div>
											<div class="date-month">FEB</div>
										</div>
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/blogs/02.png') }}" class="card-img-top border-bottom" alt="...">
										</a>
										<div class="card-body">
											<div class="news-title">
												<a href="javascript:;">
													<h5 class="mb-3 text-capitalize">{{ __t('Blog Short Title') }}</h5>
												</a>
											</div>
											<p class="news-content mb-0">{{ __t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...') }}</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">{{ __t('0 Comments') }}</a>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0 product-card border">
										<div class="news-date">
											<div class="date-number">24</div>
											<div class="date-month">FEB</div>
										</div>
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/blogs/03.png') }}" class="card-img-top border-bottom" alt="...">
										</a>
										<div class="card-body">
											<div class="news-title">
												<a href="javascript:;">
													<h5 class="mb-3 text-capitalize">{{ __t('Blog Short Title') }}</h5>
												</a>
											</div>
											<p class="news-content mb-0">{{ __t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...') }}</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">{{ __t('0 Comments') }}</a>
										</div>
									</div>
								</div>
								@endforelse
							</div>
						</div>
					</div>
				</section>
				<!--end News-->
				<!--start brands-->
				<section class="py-4">
					<div class="container">
						<h3 class="d-none">{{ __t('Brands') }}</h3>
						<div class="brand-grid">
							<div class="brands-shops owl-carousel owl-theme border">
								@forelse ($brands as $brand)
								@php
									$brandLogo = $brand->logo ? App\Helpers\ImageHelper::getBrandImage($brand->logo) : asset('frontend/assets/images/brands/0' . ($loop->index + 1) . '.png');
								@endphp
								<div class="item border-end">
									<div class="p-4 d-flex align-items-center justify-content-center" style="height: 120px;">
										<a href="{{ route('frontend.products.index') }}?search={{ $brand->name }}">
											<img src="{{ $brandLogo }}" class="img-fluid brand-logo" alt="{{ $brand->name }}" style="max-height: 80px; width: auto; object-fit: contain;">
										</a>
									</div>
								</div>
								@empty
								<div class="item border-end">
									<div class="p-4 d-flex align-items-center justify-content-center" style="height: 120px;">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/01.png') }}" class="img-fluid brand-logo" alt="..." style="max-height: 80px; width: auto; object-fit: contain;">
										</a>
									</div>
								</div>
								<div class="item border-end">
									<div class="p-4 d-flex align-items-center justify-content-center" style="height: 120px;">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/04.png') }}" class="img-fluid brand-logo" alt="..." style="max-height: 80px; width: auto; object-fit: contain;">
										</a>
									</div>
								</div>
								@endforelse
							</div>
						</div>
					</div>
				</section>
				<!--end brands-->
				
				<!--start bottom products section-->
				<section class="py-4 border-top">
					<div class="container">
						<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
							<div class="col">
								<div class="bestseller-list mb-3">
									<h6 class="mb-3 text-uppercase fw-bold">{{ __t('Best Selling Products') }}</h6>
									<hr>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/01.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Men Casual Shirts</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/02.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Formal Coat Pant</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/03.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Women Blue Jeans</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/04.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Yellow Track Suit</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="featured-list mb-3">
									<h6 class="mb-3 text-uppercase fw-bold">{{ __t('Featured Products') }}</h6>
									<hr>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/05.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Men Sports Shoes</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/06.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Black Sofa Set</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/07.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Sports Watch</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/08.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Women Blue Heels</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="new-arrivals-list mb-3">
									<h6 class="mb-3 text-uppercase fw-bold">{{ __t('New arrivals') }}</h6>
									<hr>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.jproduct-details') }}">
												<img src="{{ asset('frontend/assets/images/products/09.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Men Black Cap</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/10.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Orange Headphone</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/11.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Samsung Mobile</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/12.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Apple Notebook</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="top-rated-products-list mb-3">
									<h6 class="mb-3 text-uppercase fw-bold">{{ __t('Top rated Products') }}</h6>
									<hr>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/13.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Ronaldo Football</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/14.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Red Fancy Sofa</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/15.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Sports Cycle</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
									<hr/>
									<div class="d-flex align-items-center gap-3">
										<div class="bottom-product-img">
											<a href="{{ route('frontend.product-details') }}">
												<img src="{{ asset('frontend/assets/images/products/16.png') }}" width="80" alt="">
											</a>
										</div>
										<div class="ms-0">
											<h6 class="mb-0 fw-light mb-1 fw-bold">Circular Table</h6>
											<div class="rating"> <i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
												<i class="bx bxs-star text-warning"></i>
											</div>
											<p class="mb-0 pro-price"><strong>$59.00</strong>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!--end row-->
					</div>
				</section>
				<!--end bottom products section-->

@push('scripts')
<script>
$(document).on('click', '.product-card-modern', function(e) {
    if ($(e.target).closest('.action-btn, .quick-view-overlay').length) return;
    var url = $(this).data('url');
    if (url) window.location.href = url;
});
</script>
<script src="{{ asset('frontend/assets/js/index.js') }}"></script>
@endpush
@endsection