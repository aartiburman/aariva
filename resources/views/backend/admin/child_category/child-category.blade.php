@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 text-dark fw-bold">{{ __('messages.child_category_list') }}</h4>
                </div>
            </div>
        </div>

        <!-- Featured Child Categories Stats Cards -->
        <div class="row mb-4">
            @foreach($featured_child_categories as $f_child)
            @php
                $bg_subtle_colors = ['bg-primary-subtle', 'bg-success-subtle', 'bg-warning-subtle', 'bg-danger-subtle'];
                $text_colors = ['text-primary', 'text-success', 'text-warning', 'text-danger'];
                $idx = $loop->index % 4;
            @endphp
            <div class="col-md-3">
                <div class="card border-0 shadow-sm overflow-hidden {{ $bg_subtle_colors[$idx] }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-lg p-1">
                                <img src="{{ $f_child->image }}" alt="{{ $f_child->name }}" class="img-fluid" style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <div>
                                <h5 class="fs-14 mb-1 fw-bold {{ $text_colors[$idx] }} text-truncate" style="max-width: 120px;">{{ $f_child->name }}</h5>
                                <p class="mb-0 fs-12 text-muted">{{ $f_child->sub_categories_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filter and Search Row -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header p-4">
                <form action="{{ route('child.category.list') }}" method="POST" id="child-category-filter-form" class="no-loader">
                    @csrf
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Child Category Name</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" id="child-category-search" class="form-control" placeholder="Search child category..." value="{{ request('search') }}">
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
                            <select name="is_active" class="form-select form-select-sm" onchange="$('#child-category-filter-form').submit()">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('child.category.list') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Child Categories Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h4 class="card-title mb-0">{{ __('messages.child_category_list') }}</h4>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-danger d-none" id="bulk-delete-btn">
                        <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle me-1"></iconify-icon> Bulk Delete
                  </button>
                    <a href="{{ route('export.child.categories', request()->all()) }}" id="export-child-categories-btn" class="btn btn-sm btn-outline-info d-none no-loader">
                        <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export
                    </a>

                    <a href="{{ route('add.child.category') }}" class="btn btn-sm btn-primary">
                        <iconify-icon icon="solar:plus-circle-linear" class="fs-18"></iconify-icon>
                        Add Child Category
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
                                <th>{{ __('messages.child_category') }}</th>
                                <th>{{ __('messages.subcategory') }}</th>
                                <th>{{ __('messages.category') }}</th>
                                <th>ID</th>
                                <th>{{ __('messages.products') }}</th>
                                <th>Status</th>
                                <th class="text-end pe-4">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('backend.admin.child_category.child-category-table')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-top-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted mb-0 fs-13">Showing {{ $childCategories->firstItem() }} to {{ $childCategories->lastItem() }} of {{ $childCategories->total() }} child categories</p>
                    <div class="pagination-container">
                        {{ $childCategories->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // AJAX Filter
        let searchTimer;
        $('#child-category-filter-form').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let url = $(this).attr('action');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                
                success: function(response) {
                    $('tbody').html(response);
                    // Update count
                    let rowCount = $('<div>').html(response).find('tr').length;
                     if($('<div>').html(response).find('td[colspan]').length > 0) {
                          rowCount = 0;
                     }
                    $('.card-footer p').text('Showing all ' + rowCount + ' child categories');
                    // Re-initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    toastr.error('Error filtering data');
                }
            });
        });

        // Search Debounce
        $('#child-category-search').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                $('#child-category-filter-form').submit();
            }, 3000);
        });

        // Trigger filter on date range change
        $('.range-datepicker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            $('#child-category-filter-form').submit();
        });

        // Date Range Picker - Cancel
        $('.range-datepicker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#child-category-filter-form').submit();
        });

        // Bulk Delete Logic
        const bulkDeleteBtn = $('#bulk-delete-btn');
        const exportBtn = $('#export-child-categories-btn');
        const checkAll = $('#checkAll');
        
        // Event delegation for dynamically loaded rows
        $(document).on('change', '.row-checkbox', function() {
            toggleBulkDeleteBtn();
            const totalRows = $('.row-checkbox').length;
            const checkedRows = $('.row-checkbox:checked').length;
            checkAll.prop('checked', totalRows > 0 && totalRows === checkedRows);
        });

        checkAll.on('change', function() {
            $('.row-checkbox').prop('checked', $(this).prop('checked'));
            toggleBulkDeleteBtn();
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
                handleDelete(ids);
            }
        });

        $(document).on('click', '.delete-child-category', function() {
            const id = $(this).data('id');
            handleDelete([id]);
        });

        function handleDelete(ids) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete selected!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('bulk.delete.child.category') }}",
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
</script>
@endpush





