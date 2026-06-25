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
                                            <button type="button" id="banner_bulk_delete_btn" class="btn btn-danger" style="display: none; margin-right: 5px;">
                                                <iconify-icon icon="solar:trash-bin-trash-linear" class="me-1"></iconify-icon> Bulk Delete
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
                                                                    data-banner-id="{{ $banner->id }}"
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

    <script>
        $(document).ready(function() {
            // Open Popup
            $('.preview-banner').on('click', function() {
                var title = $(this).data('title');
                var images = $(this).data('images'); // Array of objects {name:..., url:...}

                $('#bannerPreviewModalLabel').text(title);
                $('#bannerCarouselInner').empty();

                if (images && images.length > 0) {
                    $.each(images, function(index, imgObj) {
                        var activeClass = index === 0 ? 'active' : '';
                        var itemHtml = `
                        <div class="carousel-item ${activeClass}">
                            <img src="${imgObj.url}" class="d-block w-100" alt="${imgObj.name}" style="object-fit: contain; max-height: 500px;">
                        </div>
                    `;
                        $('#bannerCarouselInner').append(itemHtml);
                    });
                } else {
                    $('#bannerCarouselInner').html('<div class="text-center p-3">No images available</div>');
                }

                // Show Bootstrap Modal
                var modalEl = document.getElementById('bannerPreviewModal');
                var myModal = bootstrap.Modal.getInstance(modalEl);
                if (!myModal) {
                    myModal = new bootstrap.Modal(modalEl);
                }
                myModal.show();
            });

            // Check All
            $('#bannerCheckAll').on('change', function() {
                $('.banner-row-checkbox').prop('checked', $(this).prop('checked'));
                toggleBulkButtons();
            });

            // Individual Check
            $(document).on('change', '.banner-row-checkbox', function() {
                if ($('.banner-row-checkbox:checked').length === $('.banner-row-checkbox').length) {
                    $('#bannerCheckAll').prop('checked', true);
                } else {
                    $('#bannerCheckAll').prop('checked', false);
                }
                toggleBulkButtons();
            });

            // Toggle Bulk Buttons
            function toggleBulkButtons() {
                if ($('.banner-row-checkbox:checked').length > 0) {
                    $('#banner_bulk_delete_btn, #banner_bulk_active_btn, #banner_bulk_deactive_btn').show();
                    $('#banner_bulk_export_btn').show();
                } else {
                    $('#banner_bulk_delete_btn, #banner_bulk_active_btn, #banner_bulk_deactive_btn').hide();
                    $('#banner_bulk_export_btn').hide();
                }
            }

            // Status Toggle (Single)
            $(document).on('change', '.status-toggle', function() {
                var $el = $(this);
                var id = $el.data('banner-id');
                var status = $el.prop('checked') ? 1 : 0;
                var url = $el.data('url');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        id: id,
                        status: status,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                            $el.prop('checked', !status);
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong!');
                        $el.prop('checked', !status);
                    }
                });
            });

            // Bulk Status (Active/Deactive)
            $('#banner_bulk_active_btn, #banner_bulk_deactive_btn').on('click', function() {
                var status = $(this).attr('id') === 'banner_bulk_active_btn' ? 1 : 0;
                var ids = $('.banner-row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Update status for " + ids.length + " banners?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, update it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('bulk.banner.status') }}",
                                type: "POST",
                                data: {
                                    ids: ids,
                                    status: status,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.status) {
                                        toastr.success(response.message);
                                        location.reload();
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function() {
                                    toastr.error('Something went wrong!');
                                }
                            });
                        }
                    });
                }
            });

            // Bulk Export
            $('#banner_bulk_export_btn').on('click', function() {
                var ids = $('.banner-row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                var form = $('<form>', {
                    action: "{{ route('export.banners') }}",
                    method: "POST"
                });

                form.append($('<input>', {
                    type: "hidden",
                    name: "_token",
                    value: "{{ csrf_token() }}"
                }));

                $.each(ids, function(index, id) {
                    form.append($('<input>', {
                        type: "hidden",
                        name: "ids[]",
                        value: id
                    }));
                });

                $('body').append(form);
                form.submit();
                form.remove();
            });

            // Bulk Delete
            $('#banner_bulk_delete_btn').on('click', function() {
                var ids = $('.banner-row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('bulk.delete.banner') }}",
                                type: "POST",
                                data: {
                                    ids: ids,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.status) {
                                        toastr.success(response.message);
                                        location.reload();
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function() {
                                    toastr.error('Something went wrong!');
                                }
                            });
                        }
                    });
                }
            });
        });
    </script>
    @endpush



    @endsection
