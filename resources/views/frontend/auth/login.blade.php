@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">Sign In</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sign In</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="">
    <div class="container">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5">
            <div class="row row-cols-1 row-cols-xl-2">
                <div class="col mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3>Sign In</h3>
                                    <p>Don't have an account yet? <a href="{{ route('frontend.register') }}">Sign up here</a></p>
                                </div>
                                @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                                <div class="form-body">
                                    <form class="row g-3" method="POST" action="{{ route('frontend.login') }}">
                                        @csrf
                                        <div class="col-12">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Email Address">
                                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password">
                                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                                <label class="form-check-label" for="remember">Remember Me</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <a href="javascript:;">Forgot Password?</a>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-dark"><i class="bx bxs-lock-open"></i> Sign In</button>
                                            </div>
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
@endsection
