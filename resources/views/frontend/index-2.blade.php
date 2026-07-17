@extends('frontend.layouts.app')

@section('before-page-wrapper')
<!--start slider section-->
		<section class="slider-section mb-4">
			<div class="first-slider p-0">

				<div class="banner-slider owl-carousel owl-theme">
					<div class="item">
						<div class="position-relative">
							<div class="position-absolute top-50 slider-content translate-middle">
								<h3 class="h3 fw-bold d-none d-md-block">New Trending</h3>
								<h1 class="h1 fw-bold">Women Fashion</h1>
								<p class="fw-bold text-dark d-none d-md-block"><i>Last call for upto 15%</i></p>
								<div class=""><a class="btn btn-dark btn-ecomm px-4" href="{{ route('frontend.products.index') }}">Shop Now</a>
								</div>
							  </div>
							<a href="javascript:;">
								<img src="{{ asset('frontend/assets/images/banners/01.png') }}" class="img-fluid" alt="...">
							</a>
						</div>
					</div>
					<div class="item">
						<div class="position-relative">
							<div class="position-absolute top-50 slider-content translate-middle">
								<h3 class="h3 fw-bold d-none d-md-block">New Trending</h3>
								<h1 class="h1 fw-bold">Men Fashion</h1>
								<p class="fw-bold text-dark d-none d-md-block"><i>Last call for upto 15%</i></p>
								<div class=""><a class="btn btn-dark btn-ecomm px-4" href="{{ route('frontend.products.index') }}">Shop Now</a>
								</div>
							  </div>
							<a href="javascript:;">
								<img src="{{ asset('frontend/assets/images/banners/02.png') }}" class="img-fluid" alt="...">
							</a>
						</div>
					</div>
					<div class="item">
						<div class="position-relative">
							<div class="position-absolute top-50 slider-content translate-middle">
								<h3 class="h3 fw-bold d-none d-md-block">New Trending</h3>
								<h1 class="h1 fw-bold">Kids Fashion</h1>
								<p class="fw-bold text-dark d-none d-md-block"><i>Last call for upto 15%</i></p>
								<div class=""><a class="btn btn-dark btn-ecomm px-4" href="{{ route('frontend.products.index') }}">Shop Now</a>
								</div>
							  </div>
							<a href="javascript:;">
								<img src="{{ asset('frontend/assets/images/banners/04.png') }}" class="img-fluid" alt="...">
							</a>
						</div>
					</div>
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
										<h6 class="mb-0 fw-bold">FREE SHIPPING &amp; RETURN</h6>
										<p class="mb-0">Free shipping on all orders over $49</p>
									</div>
								</div>
							</div>
	
							<div class="col">
								<div class="d-flex align-items-center justify-content-center p-3 border">
									<div class="fs-1 text-content"><i class='bx bx-dollar-circle'></i>
									</div>
									<div class="info-box-content ps-3">
										<h6 class="mb-0 fw-bold">MONEY BACK GUARANTEE</h6>
										<p class="mb-0">100% money back guarantee</p>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="d-flex align-items-center justify-content-center p-3 border">
									<div class="fs-1 text-content"><i class='bx bx-support'></i>
									</div>
									<div class="info-box-content ps-3">
										<h6 class="mb-0 fw-bold">ONLINE SUPPORT 24/7</h6>
										<p class="mb-0">Awesome Support for 24/7 Days</p>
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
												<h5 class="card-title text-uppercase fw-bold">Men Wear</h5>
												<p class="card-text text-uppercase">Starting at $9</p>
												<a href="{{ route('frontend.products.index') }}" class="btn btn-outline-dark btn-ecomm">SHOP NOW</a>
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
												<h5 class="card-title text-uppercase fw-bold">Women Wear</h5>
												<p class="card-text text-uppercase">Starting at $9</p>	<a href="{{ route('frontend.products.index') }}" class="btn btn-outline-dark btn-ecomm">SHOP NOW</a>
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
												<h5 class="card-title text-uppercase fw-bold">Kids Wear</h5>
												<p class="card-text text-uppercase">Starting at $9</p><a href="{{ route('frontend.products.index') }}" class="btn btn-outline-dark btn-ecomm">SHOP NOW</a>
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
				<!--start Featured product-->
				<section class="py-4">
					<div class="container">
						<div class="separator pb-4">
							<div class="line"></div>
							<h5 class="mb-0 fw-bold separator-title">FEATURED PRODUCTS</h5>
							<div class="line"></div>
						  </div>
						<div class="product-grid">
							<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-5 g-3 g-sm-4">
								<div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/01.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/02.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/03.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/04.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/05.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/06.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/07.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/08.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/09.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
								  <div class="col">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/10.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								  </div>
							  </div><!--end row-->

						</div>
					</div>
				</section>
				<!--end Featured product-->
				<!--start New Arrivals-->
				<section class="py-4">
					<div class="container">
						 <div class="separator pb-4">
							<div class="line"></div>
							<h5 class="mb-0 fw-bold separator-title">New Arrivals</h5>
							<div class="line"></div>
						 </div>
						<div class="product-grid">
							<div class="new-arrivals owl-carousel owl-theme position-relative">
								 <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/11.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
								   <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/12.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
								   <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/13.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
								   <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/14.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
								   <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/15.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
								   <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/16.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
								   <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/17.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
								   <div class="item">
									<div class="card">
										<div class="position-relative overflow-hidden">
											<div class="add-cart position-absolute top-0 end-0 mt-3 me-3">
												<a href="javascript:;"><i class='bx bx-cart-add' ></i></a>
											  </div>
										  <div class="quick-view position-absolute start-0 bottom-0 end-0">
											<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#QuickViewProduct">Quick View</a>
										  </div>
										  <a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/products/18.png') }}" class="img-fluid" alt="...">
										  </a>
										</div>
										<div class="card-body px-0">
										  <div class="d-flex align-items-center justify-content-between">
											  <div class="">
												  <p class="mb-1 product-short-name">Topwear</p>
												  <h6 class="mb-0 fw-bold product-short-title">White Polo Shirt</h6>
											  </div>
											  <div class="icon-wishlist">
												  <a href="javascript:;"><i class="bx bx-heart"></i></a>
											  </div>
										  </div>
										  <div class="cursor-pointer rating mt-2">
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
											  <i class="bx bxs-star text-warning"></i>
										  </div>
										  <div class="product-price d-flex align-items-center justify-content-start gap-2 mt-2">
											<div class="h6 fw-light fw-bold text-secondary text-decoration-line-through">$59.00</div>
											<div class="h6 fw-bold">$48.00</div>
										  </div>
										</div>
									  </div>
								   </div>
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
								@forelse ($promoBanners ?? [] as $promo)
								@php
									$promoImages = $promo->image ?? [];
									$promoImages = is_array($promoImages) ? $promoImages : (array)$promoImages;
									$promoLink = $promo->link_url ?? 'javascript:;';
									$promoBtnText = __('Shop Now');
									$imgCount = count($promoImages);
									$promoTitle = $promo->title ?? __('Sale');
									$promoDesc = $promo->slug ?? __('Explore the collection');
									$promoDiscount = $promo->title ?? '-10%';
								@endphp
								@if($imgCount >= 5)
								<div class="col-12 col-md-6 col-lg-3 d-flex">
									<div class="card rounded-0 w-100 border-0 shadow-none">
										<div id="promoCarousel2{{ $loop->index }}" class="carousel slide" data-bs-ride="carousel">
											<div class="carousel-inner">
												@foreach($promoImages as $pi => $promoImg)
												<div class="carousel-item {{ $pi === 0 ? 'active' : '' }}">
													<img src="{{ $promoImg }}" class="d-block w-100" alt="{{ $promo->title ?? '' }}">
												</div>
												@endforeach
											</div>
											<button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel2{{ $loop->index }}" data-bs-slide="prev">
												<span class="carousel-control-prev-icon" aria-hidden="true"></span>
												<span class="visually-hidden">Previous</span>
											</button>
											<button class="carousel-control-next" type="button" data-bs-target="#promoCarousel2{{ $loop->index }}" data-bs-slide="next">
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
													<img src="{{ $promoImg }}" class="img-fluid w-100" style="object-fit: cover;" alt="{{ $promo->title ?? '' }}">
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
													<img src="{{ asset('frontend/assets/images/promo/0' . ($loop->index + 1) . '.png') }}" class="img-fluid w-100" style="object-fit: cover;" alt="{{ $promo->title ?? '' }}">
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
											<h5 class="card-title" style="font-size: 18px; margin-bottom: 8px;">Sunglasses Sale</h5>
											<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 12px;">See all Sunglasses and get 10% off at all Sunglasses</p>
											<a href="javascript:;" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">SHOP BY GLASSES</a>
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
											<h5 class="card-title" style="font-size: 18px; margin-bottom: 8px;">Cosmetics Sales</h5>
											<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 12px;">Buy Cosmetics products and get 30% off at all Cosmetics</p>
											<a href="javascript:;" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">SHOP BY COSMETICS</a>
										</div>
									</div>
								</div>
								<div class="col-12 col-md-6 col-lg-3 d-flex">
									<div class="card rounded-0 w-100 border-0 shadow-none">
										<div class="position-relative">
											<img src="{{ asset('frontend/assets/images/promo/06.png') }}" class="img-fluid w-100" style="object-fit: cover;" alt="...">
											<div class="card-img-overlay d-flex flex-column justify-content-center" style="padding: 20px;">
												<h5 class="card-title text-white mb-2" style="font-size: 20px;">Fashion Summer Sale</h5>
												<p class="card-text text-white text-uppercase" style="font-size: 36px; font-weight: bold; line-height: 1.2; margin-bottom: 10px;">Up to 80% off</p>
												<p class="card-text text-white mb-3" style="font-size: 16px;">On Top Fashion Brands</p>
												<div class="position-absolute bottom-0 left-0 w-100 text-center" style="background: rgba(255,255,255,0.9); padding: 10px;">
													<a href="javascript:;" class="btn btn-dark btn-sm" style="padding: 5px 20px; border-radius: 0;">SHOP BY FASHION</a>
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
											<h5 class="card-title fw-bold" style="font-size: 28px; margin-bottom: 5px;">SUPER SALE</h5>
											<p class="card-text text-uppercase mb-2" style="font-size: 18px;">Up to 50% off</p>
											<p class="card-text" style="font-size: 13px; color: #666; margin-bottom: 15px;">On All Electronic</p>
											<a href="javascript:;" class="btn btn-dark btn-sm align-self-center" style="padding: 5px 20px; border-radius: 0;">HURRY UP!</a>
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
							<h5 class="mb-0 fw-bold separator-title">Browse Catergory</h5>
							<div class="line"></div>
						 </div>

						<div class="product-grid">
							<div class="browse-category owl-carousel owl-theme">
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/01.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-0 text-uppercase fw-bold">Fashion</h6>
											<p class="mb-0 font-12 text-uppercase">10 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/02.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Watches</h6>
											<p class="mb-0 font-12 text-uppercase">8 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/03.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Shoes</h6>
											<p class="mb-0 font-12 text-uppercase">14 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/04.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Bags</h6>
											<p class="mb-0 font-12 text-uppercase">6 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/05.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Electronis</h6>
											<p class="mb-0 font-12 text-uppercase">6 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/06.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Headphones</h6>
											<p class="mb-0 font-12 text-uppercase">5 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/07.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Furniture</h6>
											<p class="mb-0 font-12 text-uppercase">20 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/08.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Jewelry</h6>
											<p class="mb-0 font-12 text-uppercase">16 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/09.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Sports</h6>
											<p class="mb-0 font-12 text-uppercase">28 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/10.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Vegetable</h6>
											<p class="mb-0 font-12 text-uppercase">15 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/11.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Medical</h6>
											<p class="mb-0 font-12 text-uppercase">24 Products</p>
										</div>
									</div>
								</div>
								<div class="item">
									<div class="card rounded-0">
										<div class="card-body p-0">
											<img src="{{ asset('frontend/assets/images/categories/12.png') }}" class="img-fluid" alt="...">
										</div>
										<div class="card-footer text-center bg-transparent border">
											<h6 class="mb-1 text-uppercase fw-bold">Sunglasses</h6>
											<p class="mb-0 font-12 text-uppercase">18 Products</p>
										</div>
									</div>
								</div>
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
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">Free delivery</h5>
									<p class="text-capitalize">Free delivery over $199</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.</p>
								</div>
							</div>
							<div class="col">
								<div class="text-center border p-3 bg-white">
									<div class="font-50 text-dark"><i class='bx bx-credit-card'></i>
									</div>
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">Secure payment</h5>
									<p class="text-capitalize">We possess SSL / Secure сertificate</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.</p>
								</div>
							</div>
							<div class="col">
								<div class="text-center border p-3 bg-white">
									<div class="font-50 text-dark">	<i class='bx bx-dollar-circle'></i>
									</div>
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">Free returns</h5>
									<p class="text-capitalize">We return money within 30 days</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.</p>
								</div>
							</div>
							<div class="col">
								<div class="text-center border p-3 bg-white">
									<div class="font-50 text-dark">	<i class='bx bx-support'></i>
									</div>
									<h5 class="fs-5 text-uppercase mb-0 fw-bold">Customer Support</h5>
									<p class="text-capitalize">Friendly 24/7 customer support</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna, et dapib.</p>
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
							<h5 class="mb-0 fw-bold text-uppercase">Latest News</h5>
						 </div>
						<div class="product-grid">
							<div class="latest-news owl-carousel owl-theme">
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
													<h5 class="mb-3 text-capitalize">Blog Short Title</h5>
												</a>
											</div>
											<p class="news-content mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">0 Comments</a>
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
													<h5 class="mb-3 text-capitalize">Blog Short Title</h5>
												</a>
											</div>
											<p class="news-content mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">0 Comments</a>
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
													<h5 class="mb-3 text-capitalize">Blog Short Title</h5>
												</a>
											</div>
											<p class="news-content mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">0 Comments</a>
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
											<img src="{{ asset('frontend/assets/images/blogs/04.png') }}" class="card-img-top border-bottom" alt="...">
										</a>
										<div class="card-body">
											<div class="news-title">
												<a href="javascript:;">
													<h5 class="mb-3 text-capitalize">Blog Short Title</h5>
												</a>
											</div>
											<p class="news-content mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">0 Comments</a>
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
											<img src="{{ asset('frontend/assets/images/blogs/05.png') }}" class="card-img-top border-bottom" alt="...">
										</a>
										<div class="card-body">
											<div class="news-title">
												<a href="javascript:;">
													<h5 class="mb-3 text-capitalize">Blog Short Title</h5>
												</a>
											</div>
											<p class="news-content mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">0 Comments</a>
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
											<img src="{{ asset('frontend/assets/images/blogs/06.png') }}" class="card-img-top border-bottom" alt="...">
										</a>
										<div class="card-body">
											<div class="news-title">
												<a href="javascript:;">
													<h5 class="mb-3 text-capitalize">Blog Short Title</h5>
												</a>
											</div>
											<p class="news-content mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras non placerat mi. Etiam non tellus sem. Aenean...</p>
										</div>
										<div class="card-footer border-top bg-transparent">
											<a href="javascript:;" class="link-dark">0 Comments</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
				<!--end News-->
				<!--start brands-->
				<section class="py-4">
					<div class="container">
						<h3 class="d-none">Brands</h3>
						<div class="brand-grid">
							<div class="brands-shops owl-carousel owl-theme border">
								<div class="item border-end">
									<div class="p-4">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/01.png') }}" class="img-fluid" alt="...">
										</a>
									</div>
								</div>
								<div class="item border-end">
									<div class="p-4">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/02.png') }}" class="img-fluid" alt="...">
										</a>
									</div>
								</div>
								<div class="item border-end">
									<div class="p-4">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/03.png') }}" class="img-fluid" alt="...">
										</a>
									</div>
								</div>
								<div class="item border-end">
									<div class="p-4">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/04.png') }}" class="img-fluid" alt="...">
										</a>
									</div>
								</div>
								<div class="item border-end">
									<div class="p-4">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/05.png') }}" class="img-fluid" alt="...">
										</a>
									</div>
								</div>
								<div class="item border-end">
									<div class="p-4">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/06.png') }}" class="img-fluid" alt="...">
										</a>
									</div>
								</div>
								<div class="item border-end">
									<div class="p-4">
										<a href="javascript:;">
											<img src="{{ asset('frontend/assets/images/brands/07.png') }}" class="img-fluid" alt="...">
										</a>
									</div>
								</div>
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
									<h6 class="mb-3 text-uppercase fw-bold">Best Selling Products</h6>
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
									<h6 class="mb-3 text-uppercase fw-bold">Featured Products</h6>
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
									<h6 class="mb-3 text-uppercase fw-bold">New arrivals</h6>
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
									<h6 class="mb-3 text-uppercase fw-bold">Top rated Products</h6>
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
		
@endsection