@extends('frontend.layouts.app')

@section('content')
<!--start breadcrumb-->
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">Wishlist Grid</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i> Home</a>
                        </li>
                        <li class="breadcrumb-item"><a href="javascript:;">Wishlist</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
                    </ol>
                </nav>
												</div>
								</div>
				</div>
</section>
<!--end breadcrumb-->
<!--start Featured product-->
<section class="py-4">
    <div class="container">
        <div class="product-grid">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <div class="col">
                    <div class="card rounded-0 border">
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/01.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price">	<span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto">	<i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
                <div class="col">
                    <div class="card rounded-0 border">
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/02.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price"> <span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto"> <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-light-4"></i>
                                        <i class="bx bxs-star text-light-4"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
                <div class="col">
                    <div class="card rounded-0 border">
                        
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/03.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price"> <span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto"> <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-light-4"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
                <div class="col">
                    <div class="card rounded-0 border">
                        
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/04.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price"> <span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto"> <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
                <div class="col">
                    <div class="card rounded-0 border">
                        
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/05.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price"> <span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto"> <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-light-4"></i>
                                        <i class="bx bxs-star text-light-4"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
                <div class="col">
                    <div class="card rounded-0 border">
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/06.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price"> <span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto"> <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
                <div class="col">
                    <div class="card rounded-0 border">
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/07.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price"> <span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto"> <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-light-4"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
                <div class="col">
                    <div class="card rounded-0 border">
                        <a href="{{ route('frontend.product-details') }}">
                            <img src="{{ asset('frontend/assets/images/products/08.png') }}" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div class="product-info">
                                <a href="javascript:;">
                                    <p class="product-catergory font-13 mb-1">Catergory Name</p>
                                </a>
                                <a href="javascript:;">
                                    <h6 class="product-name mb-2">Product Short Name</h6>
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 product-price"> <span class="me-1 text-decoration-line-through">$99.00</span>
                                        <span class="text-white fs-5">$49.00</span>
                                    <div class="cursor-pointer ms-auto">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                <div class="product-action mt-2">
                                    <div class="d-grid gap-2">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">	<i class='bx bxs-cart-add'></i>Add to Cart</a>	
                                        <a href="javascript:;" class="btn btn-light btn-ecomm"><i class='bx bx-zoom-in'></i>Remove From List</a>
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
<!--end Featured product-->
@endsection