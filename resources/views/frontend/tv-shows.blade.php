@extends('frontend.layouts.app')

@section('before-page-wrapper')
<section class="py-5 text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="py-5 my-5">
                    <h1 class="display-1 fw-bold text-muted">Coming Soon</h1>
                    <p class="lead text-muted">This page is under construction. Check back soon!</p>
                    <a href="{{ route('frontend.home') }}" class="btn btn-dark btn-ecomm px-4 mt-3">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection