@extends('frontend.layouts.app')

@section('content')
				<!--start breadcrumb-->
				<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
					<div class="container">
						<div class="page-breadcrumb d-flex align-items-center">
							<h3 class="breadcrumb-title pe-3">Shop List Filter On Top</h3>
							<div class="ms-auto">
								<nav aria-label="breadcrumb">
									<ol class="breadcrumb mb-0 p-0">
										<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i> Home</a>
										</li>
										<li class="breadcrumb-item"><a href="javascript:;">Shop</a>
										</li>
										<li class="breadcrumb-item active" aria-current="page">Shop Grid Filter Top</li>
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
						<div class="toolbox d-lg-flex align-items-center mb-3 gap-2 p-3 bg-white border">
							<div class="d-flex flex-wrap flex-grow-1 gap-1">
								<div class="">
									<select class="form-select rounded-0">
										<option selected="selected">Size</option>
										<option>Small</option>
										<option>Small</option>
										<option>Small</option>
										<option>Extra Large</option>
									</select>
								</div>
								<div class="">
									<select class="form-select rounded-0">
										<option selected="selected">Color</option>
										<option>Red</option>
										<option>Yellow</option>
										<option>Black</option>
										<option>White</option>
										<option>Green</option>
										<option>Blue</option>
									</select>
								</div>
								<div class="">
									<select class="form-select rounded-0">
										<option selected="selected">Price</option>
										<option>$5 to $49</option>
										<option>$49 to $99</option>
										<option>$99 to $149</option>
										<option>$149 to $300</option>
										<option>$300 to $500</option>
										<option>Above $1000</option>
									</select>
								</div>
								<div class="d-flex align-items-center flex-nowrap">
									<select class="form-select rounded-0">
										<option value="menu_order" selected="selected">Default sorting</option>
										<option value="popularity">Sort by popularity</option>
										<option value="rating">Sort by average rating</option>
										<option value="date">Sort by newness</option>
										<option value="price">Sort by price: low to high</option>
										<option value="price-desc">Sort by price: high to low</option>
									</select>
								</div>
								<div class="">
									<button type="button" class="btn btn-dark rounded-0 text-uppercase">Search</button>
								</div>
							</div>
							<div class="d-flex flex-wrap">
								<div class="d-flex align-items-center flex-nowrap">
									<p class="mb-0 font-13 text-nowrap text-white">Show:</p>
									<select class="form-select ms-3 rounded-0">
										<option>9</option>
										<option>12</option>
										<option>16</option>
										<option>20</option>
										<option>50</option>
										<option>100</option>
									</select>
								</div>
							</div>
							<div> <a href="{{ route('frontend.shop-grid-filter-on-top') }}" class="btn btn-white rounded-0"><i class='bx bxs-grid me-0'></i></a>
							</div>
							<div> <a href="{{ route('frontend.shop-list-filter-on-top') }}" class="btn btn-dark rounded-0"><i class='bx bx-list-ul me-0'></i></a>
							</div>
						</div>
						<div class="product-wrapper">
							<div class="row">
								<div class="col-12 col-xl-9">
									<div class="product-grid d-flex flex-column gap-4">
										<div class="card rounded-0 border">
											<div class="d-flex align-items-center justify-content-end gap-3 position-absolute end-0 top-0 p-3">
												<a href="javascript:;">
													<div class="product-compare"><span><i class="bx bx-git-compare"></i> Compare</span>
													</div>
												</a>
												<a href="javascript:;">
													<div class="product-wishlist"> <i class="bx bx-heart"></i>
													</div>
												</a>
											</div>
											<div class="row g-0">
												<div class="col-md-4">
													<img src="{{ asset('frontend/assets/images/products/01.png') }}" class="w-100 p-3" alt="...">
												</div>
												<div class="col-md-8">
													<div class="card-body">
														<div class="product-info">
															<a href="javascript:;">
																<p class="product-catergory font-13 mb-1">Catergory Name</p>
															</a>
															<a href="javascript:;">
																<h6 class="product-name mb-2">Product Short Name</h6>
															</a>
															<p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
															<div class="cursor-pointer">
															    <i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
															</div>
															<div class="d-flex align-items-center">
																<div class="mb-1 product-price">
																    <span class="me-1 text-decoration-line-through">$99.00</span>
																	<span class="fs-5">$49.00</span>
																</div>
															</div>
															<div class="product-action mt-2">
																<div class="d-flex gap-2">
																	<a href="javascript:;" class="btn btn-dark btn-ecomm"> <i class="bx bxs-cart-add"></i>Add to Cart</a> <a href="javascript:;" class="btn btn-light btn-ecomm" data-bs-toggle="modal" data-bs-target="#QuickViewProduct"><i class="bx bx-zoom-in"></i>Quick View</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="card rounded-0 border">
											<div class="d-flex align-items-center justify-content-end gap-3 position-absolute end-0 top-0 p-3">
												<a href="javascript:;">
													<div class="product-compare"><span><i class="bx bx-git-compare"></i> Compare</span>
													</div>
												</a>
												<a href="javascript:;">
													<div class="product-wishlist"> <i class="bx bx-heart"></i>
													</div>
												</a>
											</div>
											<div class="row g-0">
												<div class="col-md-4">
													<img src="{{ asset('frontend/assets/images/products/02.png') }}" class="w-100 p-3" alt="...">
												</div>
												<div class="col-md-8">
													<div class="card-body">
														<div class="product-info">
															<a href="javascript:;">
																<p class="product-catergory font-13 mb-1">Catergory Name</p>
															</a>
															<a href="javascript:;">
																<h6 class="product-name mb-2">Product Short Name</h6>
															</a>
															<p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
															<div class="cursor-pointer">
															    <i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
															</div>
															<div class="d-flex align-items-center">
																<div class="mb-1 product-price">
																    <span class="me-1 text-decoration-line-through">$99.00</span>
																	<span class="fs-5">$49.00</span>
																</div>
															</div>
															<div class="product-action mt-2">
																<div class="d-flex gap-2">
																	<a href="javascript:;" class="btn btn-dark btn-ecomm"> <i class="bx bxs-cart-add"></i>Add to Cart</a> <a href="javascript:;" class="btn btn-light btn-ecomm" data-bs-toggle="modal" data-bs-target="#QuickViewProduct"><i class="bx bx-zoom-in"></i>Quick View</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="card rounded-0 border">
											<div class="d-flex align-items-center justify-content-end gap-3 position-absolute end-0 top-0 p-3">
												<a href="javascript:;">
													<div class="product-compare"><span><i class="bx bx-git-compare"></i> Compare</span>
													</div>
												</a>
												<a href="javascript:;">
													<div class="product-wishlist"> <i class="bx bx-heart"></i>
													</div>
												</a>
											</div>
											<div class="row g-0">
												<div class="col-md-4">
													<img src="{{ asset('frontend/assets/images/products/03.png') }}" class="w-100 p-3" alt="...">
												</div>
												<div class="col-md-8">
													<div class="card-body">
														<div class="product-info">
															<a href="javascript:;">
																<p class="product-catergory font-13 mb-1">Catergory Name</p>
															</a>
															<a href="javascript:;">
																<h6 class="product-name mb-2">Product Short Name</h6>
															</a>
															<p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
															<div class="cursor-pointer">
															    <i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
															</div>
															<div class="d-flex align-items-center">
																<div class="mb-1 product-price">
																    <span class="me-1 text-decoration-line-through">$99.00</span>
																	<span class="fs-5">$49.00</span>
																</div>
															</div>
															<div class="product-action mt-2">
																<div class="d-flex gap-2">
																	<a href="javascript:;" class="btn btn-dark btn-ecomm"> <i class="bx bxs-cart-add"></i>Add to Cart</a> <a href="javascript:;" class="btn btn-light btn-ecomm" data-bs-toggle="modal" data-bs-target="#QuickViewProduct"><i class="bx bx-zoom-in"></i>Quick View</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="card rounded-0 border">
											<div class="d-flex align-items-center justify-content-end gap-3 position-absolute end-0 top-0 p-3">
												<a href="javascript:;">
													<div class="product-compare"><span><i class="bx bx-git-compare"></i> Compare</span>
													</div>
												</a>
												<a href="javascript:;">
													<div class="product-wishlist"> <i class="bx bx-heart"></i>
													</div>
												</a>
											</div>
											<div class="row g-0">
												<div class="col-md-4">
													<img src="{{ asset('frontend/assets/images/products/04.png') }}" class="w-100 p-3" alt="...">
												</div>
												<div class="col-md-8">
													<div class="card-body">
														<div class="product-info">
															<a href="javascript:;">
																<p class="product-catergory font-13 mb-1">Catergory Name</p>
															</a>
															<a href="javascript:;">
																<h6 class="product-name mb-2">Product Short Name</h6>
															</a>
															<p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
															<div class="cursor-pointer">
															    <i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
															</div>
															<div class="d-flex align-items-center">
																<div class="mb-1 product-price">
																    <span class="me-1 text-decoration-line-through">$99.00</span>
																	<span class="fs-5">$49.00</span>
																</div>
															</div>
															<div class="product-action mt-2">
																<div class="d-flex gap-2">
																	<a href="javascript:;" class="btn btn-dark btn-ecomm"> <i class="bx bxs-cart-add"></i>Add to Cart</a> <a href="javascript:;" class="btn btn-light btn-ecomm" data-bs-toggle="modal" data-bs-target="#QuickViewProduct"><i class="bx bx-zoom-in"></i>Quick View</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="card rounded-0 border">
											<div class="d-flex align-items-center justify-content-end gap-3 position-absolute end-0 top-0 p-3">
												<a href="javascript:;">
													<div class="product-compare"><span><i class="bx bx-git-compare"></i> Compare</span>
													</div>
												</a>
												<a href="javascript:;">
													<div class="product-wishlist"> <i class="bx bx-heart"></i>
													</div>
												</a>
											</div>
											<div class="row g-0">
												<div class="col-md-4">
													<img src="{{ asset('frontend/assets/images/products/05.png') }}" class="w-100 p-3" alt="...">
												</div>
												<div class="col-md-8">
													<div class="card-body">
														<div class="product-info">
															<a href="javascript:;">
																<p class="product-catergory font-13 mb-1">Catergory Name</p>
															</a>
															<a href="javascript:;">
																<h6 class="product-name mb-2">Product Short Name</h6>
															</a>
															<p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
															<div class="cursor-pointer">
															    <i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
															</div>
															<div class="d-flex align-items-center">
																<div class="mb-1 product-price">
																    <span class="me-1 text-decoration-line-through">$99.00</span>
																	<span class="fs-5">$49.00</span>
																</div>
															</div>
															<div class="product-action mt-2">
																<div class="d-flex gap-2">
																	<a href="javascript:;" class="btn btn-dark btn-ecomm"> <i class="bx bxs-cart-add"></i>Add to Cart</a> <a href="javascript:;" class="btn btn-light btn-ecomm" data-bs-toggle="modal" data-bs-target="#QuickViewProduct"><i class="bx bx-zoom-in"></i>Quick View</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="card rounded-0 border">
											<div class="d-flex align-items-center justify-content-end gap-3 position-absolute end-0 top-0 p-3">
												<a href="javascript:;">
													<div class="product-compare"><span><i class="bx bx-git-compare"></i> Compare</span>
													</div>
												</a>
												<a href="javascript:;">
													<div class="product-wishlist"> <i class="bx bx-heart"></i>
													</div>
												</a>
											</div>
											<div class="row g-0">
												<div class="col-md-4">
													<img src="{{ asset('frontend/assets/images/products/06.png') }}" class="w-100 p-3" alt="...">
												</div>
												<div class="col-md-8">
													<div class="card-body">
														<div class="product-info">
															<a href="javascript:;">
																<p class="product-catergory font-13 mb-1">Catergory Name</p>
															</a>
															<a href="javascript:;">
																<h6 class="product-name mb-2">Product Short Name</h6>
															</a>
															<p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
															<div class="cursor-pointer">
															    <i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
															</div>
															<div class="d-flex align-items-center">
																<div class="mb-1 product-price">
																    <span class="me-1 text-decoration-line-through">$99.00</span>
																	<span class="fs-5">$49.00</span>
																</div>
															</div>
															<div class="product-action mt-2">
																<div class="d-flex gap-2">
																	<a href="javascript:;" class="btn btn-dark btn-ecomm"> <i class="bx bxs-cart-add"></i>Add to Cart</a> <a href="javascript:;" class="btn btn-light btn-ecomm" data-bs-toggle="modal" data-bs-target="#QuickViewProduct"><i class="bx bx-zoom-in"></i>Quick View</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="card rounded-0 border">
											<div class="d-flex align-items-center justify-content-end gap-3 position-absolute end-0 top-0 p-3">
												<a href="javascript:;">
													<div class="product-compare"><span><i class="bx bx-git-compare"></i> Compare</span>
													</div>
												</a>
												<a href="javascript:;">
													<div class="product-wishlist"> <i class="bx bx-heart"></i>
													</div>
												</a>
											</div>
											<div class="row g-0">
												<div class="col-md-4">
													<img src="{{ asset('frontend/assets/images/products/07.png') }}" class="w-100 p-3" alt="...">
												</div>
												<div class="col-md-8">
													<div class="card-body">
														<div class="product-info">
															<a href="javascript:;">
																<p class="product-catergory font-13 mb-1">Catergory Name</p>
															</a>
															<a href="javascript:;">
																<h6 class="product-name mb-2">Product Short Name</h6>
															</a>
															<p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
															<div class="cursor-pointer">
															    <i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
																<i class="bx bxs-star text-warning"></i>
															</div>
															<div class="d-flex align-items-center">
																<div class="mb-1 product-price">
																    <span class="me-1 text-decoration-line-through">$99.00</span>
																	<span class="fs-5">$49.00</span>
																</div>
															</div>
															<div class="product-action mt-2">
																<div class="d-flex gap-2">
																	<a href="javascript:;" class="btn btn-dark btn-ecomm"> <i class="bx bxs-cart-add"></i>Add to Cart</a> <a href="javascript:;" class="btn btn-light btn-ecomm" data-bs-toggle="modal" data-bs-target="#QuickViewProduct"><i class="bx bx-zoom-in"></i>Quick View</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										

									</div>
								</div>
								<div class="col-12 col-xl-3">
									<div class="bestseller-list mb-3 border p-3">
										<h6 class="mb-0 text-uppercase">Popular Products</h6>
										<hr>
										<div class="d-flex align-items-start gap-3">
											<div class="bottom-product-img">
												<a href="javascript:;">
													<img src="{{ asset('frontend/assets/images/products/20.png') }}" width="90" alt="">
												</a>
											</div>
											<div class="">
												<h6 class="mb-0 mb-1">Product Name</h6>
												<div class="rating font-12">
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
												</div>
												<h6 class="mb-0"><strong>$59.00</strong></h6>
											</div>
										</div>
										<hr>
										<div class="d-flex align-items-start gap-3">
											<div class="bottom-product-img">
												<a href="javascript:;">
													<img src="{{ asset('frontend/assets/images/products/21.png') }}" width="90" alt="">
												</a>
											</div>
											<div class="">
												<h6 class="mb-0 mb-1">Product Name</h6>
												<div class="rating font-12">
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
												</div>
												<h6 class="mb-0"><strong>$59.00</strong></h6>
											</div>
										</div>
										<hr>
										<div class="d-flex align-items-start gap-3">
											<div class="bottom-product-img">
												<a href="javascript:;">
													<img src="{{ asset('frontend/assets/images/products/18.png') }}" width="90" alt="">
												</a>
											</div>
											<div class="">
												<h6 class="mb-0 mb-1">Product Name</h6>
												<div class="rating font-12">
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
												</div>
												<h6 class="mb-0"><strong>$59.00</strong></h6>
											</div>
										</div>
										<hr>
										<div class="d-flex align-items-start gap-3">
											<div class="bottom-product-img">
												<a href="javascript:;">
													<img src="{{ asset('frontend/assets/images/products/17.png') }}" width="90" alt="">
												</a>
											</div>
											<div class="">
												<h6 class="mb-0 mb-1">Product Name</h6>
												<div class="rating font-12">
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
												</div>
												<h6 class="mb-0"><strong>$59.00</strong></h6>
											</div>
										</div>
										<hr>
										<div class="d-flex align-items-start gap-3">
											<div class="bottom-product-img">
												<a href="javascript:;">
													<img src="{{ asset('frontend/assets/images/products/12.png') }}" width="90" alt="">
												</a>
											</div>
											<div class="">
												<h6 class="mb-0 mb-1">Product Name</h6>
												<div class="rating font-12">
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
													<i class="bx bxs-star text-warning"></i>
												</div>
												<h6 class="mb-0"><strong>$59.00</strong></h6>
											</div>
										</div>
									</div>
									<div class="card rounded-0 w-100">
										<img src="{{ asset('frontend/assets/images/promo/06.png') }}" class="ing-fluid h-100" alt="...">
										<div class="card-img-overlay text-center top-20">
											<div class="border border-white border-3 py-3 bg-dark-3">
												<h5 class="card-title text-white">Fashion Summer Sale</h5>
												<p class="card-text text-uppercase fs-1 text-white lh-1 mt-3 mb-2">Up to 80% off</p>
												<p class="card-text fs-5 text-white">On top Fashion Brands</p>
												<a href="javascript:;" class="btn btn-outline-light btn-ecomm">SHOP BY FASHION</a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!--end-->
						</div>
						<hr>
						<nav class="d-flex justify-content-between" aria-label="Page navigation">
							<ul class="pagination">
								<li class="page-item"><a class="page-link" href="javascript:;"><i class='bx bx-chevron-left'></i> Prev</a>
								</li>
							</ul>
							<ul class="pagination">
								<li class="page-item active d-none d-sm-block" aria-current="page"><span class="page-link">1<span class="visually-hidden">(current)</span></span>
								</li>
								<li class="page-item d-none d-sm-block"><a class="page-link" href="javascript:;">2</a>
								</li>
								<li class="page-item d-none d-sm-block"><a class="page-link" href="javascript:;">3</a>
								</li>
								<li class="page-item d-none d-sm-block"><a class="page-link" href="javascript:;">4</a>
								</li>
								<li class="page-item d-none d-sm-block"><a class="page-link" href="javascript:;">5</a>
								</li>
							</ul>
							<ul class="pagination">
								<li class="page-item"><a class="page-link" href="javascript:;" aria-label="Next">Next <i class='bx bx-chevron-right'></i></a>
								</li>
							</ul>
						</nav>
					</div>
				</section>
				<!--end shop area-->
@endsection