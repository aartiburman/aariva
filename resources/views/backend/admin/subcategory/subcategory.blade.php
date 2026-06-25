@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 text-dark fw-bold">{{ __('messages.subcategory_list') }}</h4>
                </div>
            </div>
        </div>

        <!-- Featured Subcategories Stats Cards -->
        <div class="row mb-4">
            @foreach($featured_subcategories as $f_sub)
            @php
                $bg_subtle_colors = ['bg-primary-subtle', 'bg-success-subtle', 'bg-warning-subtle', 'bg-danger-subtle'];
                $text_colors = ['text-primary', 'text-success', 'text-warning', 'text-danger'];
                $idx = $loop->index % 4;
            @endphp
            <div class="col-md-3">
                <div class="card border-0 shadow-sm  overflow-hidden {{ $bg_subtle_colors[$idx] }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-lg  p-1">
                                <img src="{{ $f_sub->image }}" alt="{{ $f_sub->name }}" class="img-fluid " style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <div>
                                <h5 class="fs-14 mb-1 fw-bold {{ $text_colors[$idx] }} text-truncate" style="max-width: 120px;">{{ $f_sub->name }}</h5>
                                <p class="mb-0 fs-12 text-muted">{{ $f_sub->category_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filter and Search Row -->
        <div class="card border-0 shadow-sm  mb-4">
            <div class="card-body p-3">
                <form action="{{ route('subcategory.list') }}" method="POST" id="subcategory-filter-form" class="no-loader">
                    @csrf
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Subcategory Name</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" id="subcategory-search" class="form-control" placeholder="Search subcategory..." value="{{ request('search') }}">
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
                            <select name="is_active" class="form-select form-select-sm" onchange="$('#subcategory-filter-form').submit()">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('subcategory.list') }}" class="btn btn-sm btn-outline-secondary  w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Subcategories Table Card -->
        <div class="card border-0 shadow-sm ">
            <div class="card-header d-flex justify-content-between align-items-center gap-1">
                <h4 class="card-title flex-grow-1 mb-0">{{ __('messages.subcategory_list') }}</h4>
                <div class="d-flex align-items-center gap-2">
                      <a href="{{ route('export.subcategories', request()->all()) }}" id="export-subcategories-btn" class="btn btn-sm btn-outline-info d-none no-loader">
                        <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export
                    </a>
                    <button class="btn btn-sm btn-outline-danger d-none" id="bulk-delete-btn">
                        <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle me-1"></iconify-icon> Bulk Delete
                    </button>
                    <a href="{{ route('add.subcategory') }}" class="btn btn-sm btn-primary ">
                        <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon>
                        {{ __('messages.add_subcategory') }}
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
                                <th>{{ __('messages.subcategory') }}</th>
                                <th>Parent {{ __('messages.category') }}</th>
                                <th>ID</th>
                                <th>{{ __('messages.products') }}</th>
                                <th>Status</th>
                                <th class="text-end pe-4">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('backend.admin.subcategory.subcategory-table')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-top-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted mb-0 fs-13">Showing {{ $subcategories->firstItem() }} to {{ $subcategories->lastItem() }} of {{ $subcategories->total() }} subcategories</p>
                    <div class="pagination-container">
                        {{ $subcategories->appends(request()->query())->links() }}
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
            // Search Debounce
            let searchTimer;
            $('#subcategory-search').on('keyup', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    $('#subcategory-filter-form').submit();
                }, 3000);
            });

            // AJAX Filter
            $('#subcategory-filter-form').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                let url = $(this).attr('action');

                // AJAX call to get subcategories
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
                        $('.card-footer p').text('Showing all ' + rowCount + ' subcategories');
                        // Re-initialize tooltips if needed
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        toastr.error('Error filtering data');
                    }
                });
            });

            // Trigger filter on date range change
            $('.range-datepicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
                $('#subcategory-filter-form').submit();
            });

            $('.range-datepicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#subcategory-filter-form').submit();
            });

            // Bulk Delete Logic
        const bulkDeleteBtn = $('#bulk-delete-btn');
        const exportBtn = $('#export-subcategories-btn');
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
                            url: "{{ route('bulk.delete.subcategory') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: ids
                            },
                            success: function(res) {
                                if (res.status) {
                                    toastr.success(res.message);
                                    // Refresh the list
                                    $('#subcategory-filter-form').submit();
                                    $('#checkAll').prop('checked', false);
                                    bulkDeleteBtn.addClass('d-none');
                                    exportBtn.addClass('d-none');
                                } else {
                                    toastr.error(res.message);
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
