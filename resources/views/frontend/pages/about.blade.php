@extends('frontend.layouts.app')

@section('title', 'About Us - ' . config('app.name'))

@section('meta_description', 'Learn more about ' . config('app.name') . ' - your trusted one-store destination for endless collection. Discover our story, mission, and commitment to quality.')

@section('meta_keywords', 'about ' . config('app.name') . ', our story, online store, ecommerce')

@section('og_title', 'About Us - ' . config('app.name'))
@section('og_description', 'Learn more about ' . config('app.name') . ' - your trusted one-store destination for endless collection. Discover our story, mission, and commitment to quality.')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">About Us</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">About Us</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="text-center">
            <h2 class="fw-bold">About Our Store</h2>
            <p class="text-muted mt-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        </div>
    </div>
</section>
@endsection
