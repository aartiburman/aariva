@extends('backend.layouts.app')
@section('content')
<!-- [ Main Content ] start -->
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container">

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <!-- <h5 class="card-title mb-1 anchor" id="basic">
                            Banner List 
                        </h5> -->
                        <div class="row">
                            <div class="col-xl-12">
                                @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                @endif

                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                @endif
                                <div class="card">
                                    <div class="card-header">
                                        <h5 style="display:inline-block;">{{ __('messages.banner_list') }}</h5>
                                        <div style="float:right;">
                                           
                                            <button type="button" id="banner_bulk_export_btn" class="btn btn-outline-primary" style="display: none; margin-right: 5px;">
                                                <iconify-icon icon="solar:export-linear" class="me-1"></iconify-icon> Export
                                            </button>
                                           
                                           
                                            <a href="{{ route('add.banner') }}"
                                                class="btn btn-primary d-inline-flex"
                                                style="color:white;">
                                                <iconify-icon icon="solar:plus-linear" class="me-1"></iconify-icon>{{ __('messages.add_banner') }}
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card-body table-border-style">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-centered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="form-check-input" id="bannerCheckAll"></th>
                                                        <th>{{ __('messages.title') }}</th>
                                                        <th>{{ __('messages.image') }}</th>
                                                        <th>{{ __('messages.position') }}</th>
                                                        <th>{{ __('messages.status') }}</th>
                                                        <th>{{ __('messages.schedule') }}</th>
                                                        <th>{{ __('messages.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($banners as $banner)
                                                    <tr id="row_{{ $banner->id }}">
                                                        <td><input type="checkbox" class="form-check-input banner-row-checkbox" data-id="{{ $banner->id }}"></td>
                                                        <td>{{ $banner->title }}</td>
                                                        <td>
                                                            @if(!empty($banner->image_data) && is_array($banner->image_data))
                                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                                @foreach($banner->image_data as $img)
                                                                <div class="position-relative banner-image-container" data-name="{{ $img['name'] }}">
                                                                    <img src="{{ $img['url'] }}" alt="Banner Image" width="50" class="me-1 mb-1 img-thumbnail">
                                                                    <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 remove-image-btn p-0"
                                                                        data-id="{{ $banner->id }}" data-name="{{ $img['name'] }}" data-url="{{ route('delete.banner.image') }}" style="width: 18px; height: 18px; font-size: 10px;">
                                                                        <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                                                                    </button>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                            @endif
                                                        </td>

                                                        <td>{{ ucfirst($banner->position) }}</td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input status-toggle" type="checkbox"
                                                                    data-id="{{ $banner->id }}"
                                                                    data-url="{{ route('change.banner.status') }}"
                                                                    {{ $banner->status == 1 ? 'checked' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($banner->start_date || $banner->end_date)
                                                            {{ $banner->start_date ? $banner->start_date->format('Y-m-d') : '-' }} to
                                                            {{ $banner->end_date ? $banner->end_date->format('Y-m-d') : '-' }}
                                                            @else
                                                            -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <a href="javascript:void(0);" class="text-purple hover-opacity-100 preview-banner"
                                                                    data-title="{{ $banner->title }}"
                                                                    data-images="{{ json_encode($banner->image_data) }}"
                                                                    data-bs-toggle="tooltip" title="View Detail">
                                                                    <iconify-icon icon="solar:eye-linear" class="align-middle fs-20"></iconify-icon>
                                                                </a>
                                                                <a href="{{ route('edit.banner', $banner->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit">
                                                                    <iconify-icon icon="solar:pen-linear" class="align-middle fs-20"></iconify-icon>
                                                                </a>
                                                                <!-- <a href="javascript:void(0);" class="text-danger hover-opacity-100 delete-banner" data-id="{{ $banner->id }}" data-bs-toggle="tooltip" title="Delete">
                                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle fs-20"></iconify-icon>
                                                                </a> -->
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer border-top-0 py-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <p class="text-muted mb-0 fs-13">Showing {{ $banners->firstItem() }} to {{ $banners->lastItem() }} of {{ $banners->total() }} {{ strtolower(__('messages.banner')) }}s</p>
                                            <nav>
                                                {{ $banners->links('pagination::bootstrap-5') }}
                                            </nav>
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


    @push('scripts')


    <!-- Banner Preview Modal (Bootstrap) -->
    <div class="modal fade" id="bannerPreviewModal" tabindex="-1" aria-labelledby="bannerPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bannerPreviewModalLabel">Banner Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="bannerCarouselInner">
                            <!-- Slides injected by JS -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @endpush



    @endsection
