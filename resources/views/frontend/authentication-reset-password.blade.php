@extends('frontend.layouts.app')


@section('content')
<!--start breadcrumb-->
			   <section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
					<div class="container">
					 <div class="page-breadcrumb d-flex align-items-center">
						<h3 class="breadcrumb-title pe-3">Reset Password</h3>
						<div class="ms-auto">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i> Home</a>
									</li>
									<li class="breadcrumb-item"><a href="javascript:;">Authentication</a>
									</li>
									<li class="breadcrumb-item active" aria-current="page">Reset Password</li>
								</ol>
							</nav>
						</div>
						</div>
					</div>
				</section>
               <!--end breadcrumb-->  

			   <!--start shop cart-->
				<section class="">
					<div class="container">
						<div class="authentication-reset-password d-flex align-items-center justify-content-center my-5">
							<div class="row">
								<div class="col-12 col-lg-10 mx-auto">
									<div class="card">
										<div class="row g-0">
											<div class="col-lg-5 border-end">
												<div class="card-body">
													<div class="p-5">
														<h4 class="font-weight-bold">Genrate New Password</h4>
														<p class="">We received your reset password request. Please enter your new password!</p>
														<div class="mb-3 mt-5">
															<label class="form-label">New Password</label>
															<input type="text" class="form-control" placeholder="Enter new password" />
														<div class="mb-3">
															<label class="form-label">Confirm Password</label>
															<input type="text" class="form-control" placeholder="Confirm password" />
														<div class="d-grid gap-2">
															<button type="button" class="btn btn-dark">Change Password</button> <a href="{{ route('frontend.authentication-login') }}" class="btn btn-light"><i class='bx bx-arrow-back mr-1'></i>Back to Login</a>
											<div class="col-lg-7">
												<img src="{{ asset('frontend/assets/images/login-images/forgot-password-frent-img.jpg') }}" class="card-img login-img h-100" alt="...">
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