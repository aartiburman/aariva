@php
    $postMetaTitle = $post->meta_title ?: ($post->title . ' - ' . config('app.name'));
    $postMetaDescription = $post->meta_description ?: \Str::limit(strip_tags($post->description ?? $post->content ?? ''), 160);
    $postImage = $post->image ? App\Helpers\ImageHelper::getBlogImage($post->image) : asset('frontend/assets/images/favicon-32x32.png');
@endphp

@extends('frontend.layouts.app')

@section('title', $postMetaTitle)

@section('meta_description', $postMetaDescription)

@section('meta_keywords', ($post->title ?? 'blog') . ', ' . config('app.name') . ', article, shopping tips, lifestyle')

@section('robots', 'index, follow')

@section('og_type', 'article')
@section('og_title', $postMetaTitle)
@section('og_description', $postMetaDescription)
@section('og_image', $postImage)
@section('og_url', url()->current())

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">{{ $post->title ?? 'Blog Post' }}</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.blog') }}">{{ __t('Blog') }}</a></li>
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
                <img src="{{ $postImage }}" class="img-fluid rounded mb-4" alt="{{ $post->title ?? '' }}" loading="lazy">
                @endif
                <div>{!! $post->description ?? $post->content ?? '' !!}</div>
            </div>
        </div>
    </div>
</section>

<script type="application/ld+json">
{
    "<?php echo '@'; ?>context": "https://schema.org",
    "<?php echo '@'; ?>type": "Article",
    "headline": "{{ $post->title ?? '' }}",
    "description": "{{ $postMetaDescription }}",
    "image": "{{ $postImage }}",
    "datePublished": "{{ $post->created_at ? $post->created_at->toIso8601String() : '' }}",
    "dateModified": "{{ $post->updated_at ? $post->updated_at->toIso8601String() : $post->created_at->toIso8601String() }}",
    "author": {
        "<?php echo '@'; ?>type": "Person",
        "name": "{{ $post->author->name ?? 'Admin' }}"
    },
    "publisher": {
        "<?php echo '@'; ?>type": "Organization",
        "name": "{{ config('app.name') }}"
    }
}
</script>

@endsection
