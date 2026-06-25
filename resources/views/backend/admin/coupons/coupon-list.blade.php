@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Coupon Management</h4>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('coupons.list') }}" method="POST" id="coupon-filter-form" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Coupon Code</label>
                        <div class="input-group">
                            <input type="text" name="search" id="coupon-search" class="form-control" placeholder="Search code..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Type</option>
                            <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Percentage</option>
                            <option value="0" {{ request('type') == '0' ? 'selected' : '' }}>Fixed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="reset-filter" class="btn btn-outline-secondary w-100">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Coupon List</h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-danger" id="deleteSelectedBtn" style="display: none;">Delete Selected</button>
                            <form id="exportForm" action="{{ route('coupons.export.multiple') }}" method="POST" class="d-inline">
                                @csrf
                                <div id="exportInputs"></div>
                                <button type="button" class="btn btn-sm btn-success" id="exportSelectedBtn" style="display: none;">Export Selected</button>
                            </form>
                            <a href="{{ route('coupons.create') }}" class="btn btn-sm btn-primary">Add Coupon</a>
                        </div>
                    </div>

                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-centered">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th>Code</th>
                                        <th>Value</th>
                                        <th>Validity</th>
                                        <th>Applicable To</th>
                                        <th>Uses</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('backend.admin.coupons.partials.coupon-table')
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <p class="text-muted mb-0 fs-13" id="pagination-info">Showing {{ $coupons->firstItem() ?? 0 }} to {{ $coupons->lastItem() ?? 0 }} of {{ $coupons->total() }} entries</p>
                            <div class="pagination-container" id="pagination-links">
                                {{ $coupons->links() }}
                            </div>
                        </div>
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
    const filterForm = $('#coupon-filter-form');
    const tableBody = $('tbody');
    const searchInput = $('#coupon-search');
    let debounceTimer;

    // Debounce function
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    function fetchCoupons(url = null) {
        let formData = filterForm.serialize();
        let fetchUrl = url || filterForm.attr('action');

        $.ajax({
            url: fetchUrl,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                tableBody.html('<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
            },
            success: function(response) {
                if (response.table !== undefined) {
                    tableBody.html(response.table);
                    $('#pagination-links').html(response.pagination);
                    $('#pagination-info').text(response.info);
                } else {
                    location.reload();
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    }

    filterForm.on('submit', function(e) {
        e.preventDefault();
        fetchCoupons();
    });

    filterForm.find('select').on('change', function() {
        fetchCoupons();
    });

    searchInput.on('keyup', debounce(function() {
        fetchCoupons();
    }, 3000));

    $('#reset-filter').on('click', function() {
        filterForm[0].reset();
        fetchCoupons();
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        fetchCoupons($(this).attr('href'));
    });

    $(document).on('change', '.change-status', function() {
        var id = $(this).data('id');
        $.post("{{ route('change.coupons.status') }}", {id: id, _token: "{{ csrf_token() }}"}, function(res) {
            if(res.status) toastr.success(res.message);
        });
    });

    // Centralized Bulk Delete
    initBulkDelete('.row-checkbox', '#deleteSelectedBtn', "{{ route('coupons.delete.multiple') }}");

    // Select All Checkbox
    $(document).on('change', '#selectAll', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked')).trigger('change');
    });

    $(document).on('change', '.row-checkbox', function() {
        if ($('.row-checkbox:checked').length === $('.row-checkbox').length && $('.row-checkbox').length > 0) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });

    // Export Selected
    $('#exportSelectedBtn').on('click', function() {
        let selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one coupon to export.');
            return;
        }

        let inputsHtml = '';
        selectedIds.forEach(function(id) {
            inputsHtml += `<input type="hidden" name="ids[]" value="${id}">`;
        });
        
        $('#exportInputs').html(inputsHtml);
        $('#exportForm').submit();
    });
});
</script>
@endpush
