@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">Sign Up</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sign Up</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-0 py-lg-5">
    <div class="container">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5">
            <div class="row row-cols-1 row-cols-lg-1 row-cols-xl-2">
                <div class="col mx-auto">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3>Sign Up</h3>
                                    <p>Already have an account? <a href="{{ route('frontend.login') }}">Sign in here</a></p>
                                </div>
                                @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                                </div>
                                @endif
                                <div class="form-body">
                                    <form class="row g-3" method="POST" action="{{ route('frontend.register') }}">
                                        @csrf
                                        <div class="col-12">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Full Name" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="example@user.com" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Phone Number" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-dark"><i class="bx bx-user"></i> Sign Up</button>
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
