@extends('frontend.layouts.app')


@section('content')
<!--start breadcrumb-->
				<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
					<div class="container">
						<div class="page-breadcrumb d-flex align-items-center">
							<h3 class="breadcrumb-title pe-3">Sign Up</h3>
							<div class="ms-auto">
								<nav aria-label="breadcrumb">
									<ol class="breadcrumb mb-0 p-0">
										<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i> Home</a>
										</li>
										<li class="breadcrumb-item"><a href="javascript:;">Authentication</a>
										</li>
										<li class="breadcrumb-item active" aria-current="page">Sign Up</li>
									</ol>
								</nav>
							</div>
						</div>
					</div>
				</section>
				<!--end breadcrumb-->
				<!--start shop cart-->
				<section class="py-0 py-lg-5">
					<div class="container">
						<div class="section-authentication-signin d-flex align-items-center justify-content-center my-5">
							<div class="row row-cols-1 row-cols-lg-1 row-cols-xl-2">
								<div class="col mx-auto">
									<div class="card mb-0">
										<div class="card-body">
											<div class="border p-4 rounded">
												<div class="text-center">
													<h3 class="">Sign Up</h3>
													<p>Already have an account? <a href="{{ route('frontend.authentication-login') }}">Sign in here</a>
													</p>
												<div class="d-grid">
													<a class="btn my-4 shadow-sm btn-white" href="javascript:;"> <span class="d-flex justify-content-center align-items-center">
														<img class="me-2" src="{{ asset('frontend/assets/images/icons/search.svg') }}" width="16" alt="Image Description">
														<span>Sign Up with Google</span>
														</span>
													</a> <a href="javascript:;" class="btn btn-white"><i class="bx bxl-facebook"></i>Sign Up with Facebook</a>
												<div class="login-separater text-center mb-4"> <span>OR SIGN UP WITH EMAIL</span>
													<hr/>
												<div class="form-body">
													<form class="row g-3">
														<div class="col-sm-6">
															<label for="inputFirstName" class="form-label">First Name</label>
															<input type="email" class="form-control" id="inputFirstName" placeholder="Jhon">
														<div class="col-sm-6">
															<label for="inputLastName" class="form-label">Last Name</label>
															<input type="email" class="form-control" id="inputLastName" placeholder="Deo">
														<div class="col-12">
															<label for="inputEmailAddress" class="form-label">Email Address</label>
															<input type="email" class="form-control" id="inputEmailAddress" placeholder="example@user.com">
														<div class="col-12">
															<label for="inputChoosePassword" class="form-label">Password</label>
															<div class="input-group" id="show_hide_password">
																<input type="password" class="form-control border-end-0" id="inputChoosePassword" value="12345678" placeholder="Enter Password"> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
														<div class="col-12">
															<label for="inputSelectCountry" class="form-label">Country</label>
															<select class="form-select" id="inputSelectCountry" aria-label="Default select example">
																<option selected>India</option>
																<option value="1">United Kingdom</option>
																<option value="2">America</option>
																<option value="3">Dubai</option>
															</select>
														<div class="col-12">
															<div class="form-check form-switch">
																<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
																<label class="form-check-label" for="flexSwitchCheckChecked">I read and agree to Terms & Conditions</label>
														<div class="col-12">
															<div class="d-grid">
																<button type="submit" class="btn btn-dark"><i class='bx bx-user'></i>Sign up</button>
													</form>
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
				</section>
				<!--end shop cart-->
@endsection