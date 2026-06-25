@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">Contact Us</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h4 class="fw-bold">Get In Touch</h4>
                <form>
                    <div class="mb-3"><input type="text" class="form-control" placeholder="Your Name"></div>
                    <div class="mb-3"><input type="email" class="form-control" placeholder="Your Email"></div>
                    <div class="mb-3"><textarea class="form-control" rows="4" placeholder="Message"></textarea></div>
                    <button class="btn btn-dark btn-ecomm">Send Message</button>
                </form>
            </div>
            <div class="col-md-6">
                <h4 class="fw-bold">Contact Info</h4>
                <p><strong>Address:</strong> 123 Street Name, City</p>
                <p><strong>Phone:</strong> +1 234 567 890</p>
                <p><strong>Email:</strong> info@example.com</p>
            </div>
        </div>
    </div>
</section>
@endsection
