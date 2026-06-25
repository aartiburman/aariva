@extends('frontend.layouts.app')


@section('content')
<!--start breadcrumb-->
				<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
					<div class="container">
						<div class="page-breadcrumb d-flex align-items-center">
							<h3 class="breadcrumb-title pe-3">Checkout</h3>
							<div class="ms-auto">
								<nav aria-label="breadcrumb">
									<ol class="breadcrumb mb-0 p-0">
										<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i> Home</a>
										</li>
										<li class="breadcrumb-item"><a href="javascript:;">Shop</a>
										</li>
										<li class="breadcrumb-item active" aria-current="page">Checkout</li>
									</ol>
								</nav>
							</div>
						</div>
					</div>
				</section>
				<!--end breadcrumb-->
				<!--start shop cart-->
				<section class="py-4">
					<div class="container">
						<div class="shop-cart">
							<div class="row">
								<div class="col-12 col-xl-8">
									<div class="checkout-details">
										<div class="card bg-transparent rounded-0 shadow-none">
											<div class="card-body">
												<div class="steps steps-light">
													<a class="step-item active" href="{{ route('frontend.shop-cart') }}">
														<div class="step-progress"><span class="step-count">1</span>
														<div class="step-label"><i class='bx bx-cart'></i>Cart</div>
													</a>
													<a class="step-item active current" href="{{ route('frontend.checkout-details') }}">
														<div class="step-progress"><span class="step-count">2</span>
														<div class="step-label"><i class='bx bx-user-circle'></i>Details</div>
													</a>
													<a class="step-item" href="{{ route('frontend.checkout-shipping') }}">
														<div class="step-progress"><span class="step-count">3</span>
														<div class="step-label"><i class='bx bx-cube'></i>Shipping</div>
													</a>
													<a class="step-item" href="{{ route('frontend.checkout-payment') }}">
														<div class="step-progress"><span class="step-count">4</span>
														<div class="step-label"><i class='bx bx-credit-card'></i>Payment</div>
													</a>
													<a class="step-item" href="{{ route('frontend.checkout-review') }}">
														<div class="step-progress"><span class="step-count">5</span>
														<div class="step-label"><i class='bx bx-check-circle'></i>Review</div>
													</a>
										<div class="card rounded-0">
											<div class="card-body">
												<div class="d-flex align-items-center mb-3">
													<div class="">
														<img src="{{ asset('frontend/assets/images/avatars/avatar-1.png') }}" width="90" alt="" class="rounded-circle p-1 border">
													<div class="ms-2">
														<h6 class="mb-0">Jhon Michle</h6>
														<p class="mb-0">michle@example.com</p>
													<div class="ms-auto">	<a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-edit'></i> Edit Profile</a>
												<div class="border p-3">
													<h2 class="h5 mb-0">Shipping Address</h2>
													<div class="my-3 border-bottom"></div>
													<div class="form-body">
														<form class="row g-3">
															<div class="col-md-6">
																<label class="form-label">First Name</label>
																<input type="text" class="form-control rounded-0">
															<div class="col-md-6">
																<label class="form-label">Last Name</label>
																<input type="text" class="form-control rounded-0">
															<div class="col-md-6">
																<label class="form-label">E-mail id</label>
																<input type="text" class="form-control rounded-0">
															<div class="col-md-6">
																<label class="form-label">Phone Number</label>
																<input type="text" class="form-control rounded-0">
															<div class="col-md-6">
																<label class="form-label">Company</label>
																<input type="text" class="form-control rounded-0">
															<div class="col-md-6">
																<label class="form-label">State/Province</label>
																<select class="form-select rounded-0">
																	<option>United Kingdom</option>
																	<option>California</option>
																</select>
															<div class="col-md-6">
																<label class="form-label">Zip/Postal Code</label>
																<input type="text" class="form-control rounded-0">
															<div class="col-md-6">
																<label class="form-label">Country</label>
																<select class="form-select rounded-0">
																	<option>United States</option>
																	<option>India</option>
																	<option>China</option>
																	<option>Turkey</option>
																</select>
															<div class="col-md-6">
																<label class="form-label">Address 1</label>
																<textarea class="form-control rounded-0"></textarea>
															<div class="col-md-6">
																<label class="form-label">Address 2</label>
																<textarea class="form-control rounded-0"></textarea>
															<div class="col-md-12">
																<h6 class="mb-0 h5">Billing Address</h6>
																<div class="my-3 border-bottom"></div>
																<div class="form-check">
																	<input class="form-check-input" type="checkbox" id="gridCheck" checked>
																	<label class="form-check-label" for="gridCheck">Same as shipping address</label>
															<div class="col-md-6">
																<div class="d-grid">	<a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-chevron-left'></i>Back to Cart</a>
															<div class="col-md-6">
																<div class="d-grid">	<a href="javascript:;" class="btn btn-dark btn-ecomm">Proceed to Checkout<i class='bx bx-chevron-right'></i></a>
														</form>
								<div class="col-12 col-xl-4">
									<div class="order-summary">
										<div class="card rounded-0">
											<div class="card-body">
												<div class="card rounded-0 border bg-transparent shadow-none mb-3">
													<div class="card-body">
														<p class="fs-5">Apply Discount Code</p>
														<div class="input-group">
															<input type="text" class="form-control rounded-0" placeholder="Enter discount code">
															<button class="btn btn-dark btn-ecomm" type="button">Apply</button>
												<div class="card rounded-0 border bg-transparent shadow-none">
													<div class="card-body">
														<p class="fs-5">Order summary</p>
														<div class="my-3 border-top"></div>
														<div class="d-flex align-items-center">
															<a class="d-block flex-shrink-0" href="javascript:;">
																<img src="{{ asset('frontend/assets/images/products/01.png') }}" width="75" alt="Product">
															</a>
															<div class="ps-2">
																<h6 class="mb-1"><a href="javascript:;" class="text-dark">White Polo T-Shirt</a></h6>
																<div class="widget-product-meta"><span class="me-2">$19.<small>00</small></span><span class="">x 1</span>
														<div class="my-3 border-top"></div>
														<div class="d-flex align-items-center">
															<a class="d-block flex-shrink-0" href="javascript:;">
																<img src="{{ asset('frontend/assets/images/products/17.png') }}" width="75" alt="Product">
															</a>
															<div class="ps-2">
																<h6 class="mb-1"><a href="javascript:;" class="text-dark">Fancy Red Sneakers</a></h6>
																<div class="widget-product-meta"><span class="me-2">$16.<small>00</small></span><span class="">x 2</span>
														<div class="my-3 border-top"></div>
														<div class="d-flex align-items-center">
															<a class="d-block flex-shrink-0" href="javascript:;">
																<img src="{{ asset('frontend/assets/images/products/04.png') }}" width="75" alt="Product">
															</a>
															<div class="ps-2">
																<h6 class="mb-1"><a href="javascript:;" class="text-dark">Yellow Shine Blazer</a></h6>
																<div class="widget-product-meta"><span class="me-2">$22.<small>00</small></span><span class="">x 1</span>
														<div class="my-3 border-top"></div>
														<div class="d-flex align-items-center">
															<a class="d-block flex-shrink-0" href="javascript:;">
																<img src="{{ asset('frontend/assets/images/products/09.png') }}" width="75" alt="Product">
															</a>
															<div class="ps-2">
																<h6 class="mb-1"><a href="javascript:;" class="text-dark">Men Black Hat Cap</a></h6>
																<div class="widget-product-meta"><span class="me-2">$14.<small>00</small></span><span class="">x 1</span>
												<div class="card rounded-0 border bg-transparent mb-0 shadow-none">
													<div class="card-body">
														<p class="mb-2">Subtotal: <span class="float-end">$198.00</span>
														</p>
														<p class="mb-2">Shipping: <span class="float-end">--</span>
														</p>
														<p class="mb-2">Taxes: <span class="float-end">$14.00</span>
														</p>
														<p class="mb-0">Discount: <span class="float-end">--</span>
														</p>
														<div class="my-3 border-top"></div>
														<h5 class="mb-0">Order Total: <span class="float-end">212.00</span></h5>
							<!--end row-->
													</div>
												</div>
																</div>
															</div>
														</div>
																</div>
															</div>
														</div>
																</div>
															</div>
														</div>
																</div>
															</div>
														</div>
													</div>
												</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
																</div>
															</div>
																</div>
															</div>
																</div>
															</div>
															</div>
															</div>
															</div>
															</div>
															</div>
															</div>
															</div>
															</div>
															</div>
															</div>
													</div>
												</div>
													</div>
													</div>
													</div>
												</div>
											</div>
										</div>
														</div>
														</div>
														</div>
														</div>
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
				<!--end shop cart-->
@endsection