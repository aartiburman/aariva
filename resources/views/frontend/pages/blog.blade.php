@extends('frontend.layouts.app')

@section('title', 'Blog - ' . config('app.name'))

@section('meta_description', 'Explore the ' . config('app.name') . ' blog for expert shopping tips, style guides, product reviews, and the latest trends across fashion, electronics, beauty and more.')

@section('meta_keywords', 'blog, articles, shopping tips, style guide, ' . config('app.name') . ', product reviews, trends')

@section('og_title', 'Blog - ' . config('app.name'))
@section('og_description', 'Explore the ' . config('app.name') . ' blog for expert shopping tips, style guides, product reviews, and the latest trends across fashion, electronics, beauty and more.')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">Blog</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Blog</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">Blog</h2>
        @if($posts->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($posts as $post)
            @php
                $postImg = $post->image ? App\Helpers\ImageHelper::getBlogImage($post->image) : asset('frontend/assets/images/blogs/01.png');
            @endphp
            <div class="col">
                <div class="card rounded-0 product-card border h-100">
                    <a href="{{ route('frontend.blog.show', $post->slug) }}">
                        <img src="{{ $postImg }}" class="card-img-top" alt="{{ $post->title }}" style="height: 220px; object-fit: cover;">
                    </a>
                    <div class="card-body">
                        <p class="text-muted small mb-2">{{ $post->created_at ? date('d M Y', strtotime($post->created_at)) : '' }}</p>
                        <h5 class="card-title">
                            <a href="{{ route('frontend.blog.show', $post->slug) }}" class="text-dark text-decoration-none">{{ $post->title }}</a>
                        </h5>
                        <p class="card-text">{{ \Str::limit(strip_tags($post->description ?? $post->content ?? ''), 150) }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-top">
                        <a href="{{ route('frontend.blog.show', $post->slug) }}" class="btn btn-outline-dark btn-sm">Read More</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $posts->links() }}
        </div>
        @else
        <p class="text-muted text-center">No blog posts found.</p>
        @endif
    </div>
</section>
@endsection