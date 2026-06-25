@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 text-dark fw-bold">{{ __('messages.category_list') }}</h4>
                </div>
            </div>
        </div>

        <!-- Categories Stats Cards Slider -->
        <div class="row mb-4 flex-nowrap overflow-auto pb-2 g-2">
            @foreach($all_categories as $f_cat)
            @php
                $bg_subtle_colors = ['bg-primary-subtle', 'bg-success-subtle', 'bg-warning-subtle', 'bg-danger-subtle'];
                $text_colors = ['text-primary', 'text-success', 'text-warning', 'text-danger'];
                $idx = $loop->index % 4;
            @endphp
            <div class="col-md-3" style="min-width: 280px;">
                <div class="card shadow-sm overflow-hidden {{ $bg_subtle_colors[$idx] }} mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-lg p-1">
                                <img src="{{ $f_cat->image }}" alt="{{ $f_cat->name }}" class="img-fluid" style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <div>
                                <h5 class="fs-14 mb-1 fw-bold {{ $text_colors[$idx] }}">{{ $f_cat->name }}</h5>
                                <p class="mb-0 fs-12 text-muted"> <strong>{{ $f_cat->products_count }} Products</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filter and Search Row -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('category.list') }}" method="POST" id="category-filter-form" class="no-loader">
                    @csrf
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Category Name</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" id="category-search" class="form-control" placeholder="Search category..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                            <input type="text" name="date_range" class="form-control form-control-sm range-datepicker" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="reset-filter" class="btn btn-sm btn-outline-secondary w-100">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom-0 d-flex justify-content-between align-items-center gap-1">
                <h4 class="card-title flex-grow-1">{{ __('messages.category_list') }}</h4>
                <div class="d-flex align-items-center gap-2">
                  

                   <button class="btn btn-sm btn-outline-danger d-none" id="bulk-delete-btn">
                        <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle me-1"></iconify-icon> Bulk Delete
                    </button>
                    <a href="{{ route('export.categories', request()->all()) }}" id="export-categories-btn" class="btn btn-sm btn-outline-info d-none no-loader">
                        <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export
                    </a>

                      <a href="{{ route('add.category') }}" class="btn btn-sm btn-primary">
                        <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon>
                        {{ __('messages.add_category') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-4" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </div>
                                </th>
                                <th>{{ __('messages.category') }}</th>
                                <th>ID</th>
                                <th>{{ __('messages.products') }}</th>
                                <th>Status</th>
                                <th class="text-end pe-4">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('backend.admin.category.category-table')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-top-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted mb-0 fs-13" id="pagination-info">Showing {{ $cat_data->firstItem() }} to {{ $cat_data->lastItem() }} of {{ $cat_data->total() }} {{ strtolower(__('messages.category')) }}</p>
                    <div class="pagination-container" id="pagination-links">
                        {{ $cat_data->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const filterForm = $('#category-filter-form');
        const exportBtn = $('#export-categories-btn');
        const tableBody = $('tbody');
        const footerInfo = $('.card-footer');

        // Debounce function
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }

        // Function to update Export URL
        function updateExportUrl() {
            let formData = filterForm.serialize();
            let baseUrl = "{{ route('export.categories') }}";
            exportBtn.attr('href', baseUrl + '?' + formData);
        }

        // AJAX Filtering for Category List
        function fetchCategories(url = null) {
            let formData = filterForm.serialize();
            let fetchUrl = url || filterForm.attr('action');

            $.ajax({
                url: fetchUrl,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    tableBody.html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
                },
                success: function(response) {
                    if (response.table !== undefined) {
                        tableBody.html(response.table);
                        $('#pagination-links').html(response.pagination);
                        $('#pagination-info').text(response.info);
                    } else {
                        // Fallback if the controller still returns a string
                        tableBody.html(response);
                    }
                    
                    // Update Export URL
                    updateExportUrl();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        filterForm.on('submit', function(e) {
            e.preventDefault();
            fetchCategories();
        });

        // Trigger submit on change of select or date
        $('select[name="is_active"]').on('change', function() {
             filterForm.trigger('submit');
        });

        // Debounced search for category name and date range
        $('#category-search, .range-datepicker').on('keyup change', debounce(function() {
            filterForm.trigger('submit');
        }, 3000));

        // Reset Filter
        $('#reset-filter').on('click', function() {
            filterForm[0].reset();
            $('.range-datepicker').val(''); // Clear flatpickr manually if needed
            filterForm.trigger('submit');
        });

        // AJAX Pagination
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            fetchCategories(url);
        });


        // Bulk Delete Logic
        const bulkDeleteBtn = $('#bulk-delete-btn');
        const checkAll = $('#checkAll');

        checkAll.on('change', function() {
            $('.row-checkbox').prop('checked', $(this).prop('checked'));
            toggleBulkDeleteBtn();
        });

        $(document).on('change', '.row-checkbox', function() {
            toggleBulkDeleteBtn();
            let allRows = $('.row-checkbox');
            checkAll.prop('checked', allRows.length > 0 && allRows.length === allRows.filter(':checked').length);
        });

        function toggleBulkDeleteBtn() {
            if ($('.row-checkbox:checked').length > 0) {
                bulkDeleteBtn.removeClass('d-none');
                exportBtn.removeClass('d-none');
            } else {
                bulkDeleteBtn.addClass('d-none');
                exportBtn.addClass('d-none');
            }
        }

        bulkDeleteBtn.on('click', function() {
            const ids = $('.row-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (ids.length > 0) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "All related subcategories and child categories will also be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete selected!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('bulk.delete.category') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: ids
                            },
                            success: function(response) {
                                if (response.status) {
                                    toastr.success(response.message);
                                    $('#checkAll').prop('checked', false);
                                    bulkDeleteBtn.addClass('d-none');
                                    exportBtn.addClass('d-none');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1000);
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function() {
                                toastr.error('Something went wrong');
                            }
                        });
                    }
                });
            }
        });
    });
</script>
@endpush


