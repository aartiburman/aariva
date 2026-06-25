@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">{{ $post->title ?? 'Blog Post' }}</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.blog') }}">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $post->title ?? '' }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="fw-bold">{{ $post->title ?? '' }}</h2>
                <p class="text-muted">{{ $post->created_at ? date('d M Y', strtotime($post->created_at)) : '' }}</p>
                @if ($post->image ?? false)
                <img src="{{ App\Helpers\ImageHelper::getBlogImage($post->image) }}" class="img-fluid rounded mb-4" alt="{{ $post->title ?? '' }}">
                @endif
                <div>{!! $post->description ?? $post->content ?? '' !!}</div>
            </div>
        </div>
    </div>
</section>
@endsection
