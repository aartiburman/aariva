@extends('backend.layouts.app')
@section('content')
<!-- [ Main Content ] start -->
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <!-- Page Title & Header -->
        <div class="row align-items-center mb-3">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Offer Management</h4>
                </div>
            </div>
        </div>

        <!-- Filter and Search Row -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('offer.list') }}" method="POST" id="filter-form">
                    @csrf
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Offer Code</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search code..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-13 mb-1">Type</label>
                            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Type</option>
                                <option value="percent" {{ request('type') === 'percent' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('offer.list') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Offer List</h4>

                        <div class="d-flex gap-2">
                             <button type="button" class="btn btn-sm btn-danger d-none" id="bulk-offer-delete-btn">
                                <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle me-1"></iconify-icon> Delete Selected
                             </button>
                             <button type="button" class="btn btn-sm btn-success d-none" id="bulk-offer-export-btn">
                                <iconify-icon icon="solar:export-linear" class="align-middle me-1"></iconify-icon> Export Selected
                             </button>
                            <a href="{{ route('add.offer') }}" class="btn btn-sm btn-primary">
                                Add Offer
                            </a>
                        </div>
                    </div>

                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-centered">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input" id="checkAlloffer"></th>
                                        <th>Code</th>
                                        <th>Value</th>
                                        <th>Validity</th>
                                        <th>Uses</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($offers as $offer)
                                    <tr id="row_{{ $offer->id }}">
                                        <td><input type="checkbox" class="form-check-input offer-checkbox" value="{{ $offer->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 bg-soft-purple rounded-circle d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="solar:ticket-sale-linear" class="text-purple" width="20"></iconify-icon>
                                                </div>
                                                <div>
                                                    <h5 class="fs-14 mb-0">{{ $offer->code }}</h5>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($offer->type == 1)
                                                <span class="badge bg-soft-success text-success">{{ $offer->value }}%</span>
                                            @else
                                                <span class="badge bg-soft-info text-info">Fixed {{ $offer->value }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fs-13 text-muted">
                                                <div>{{ $offer->valid_from ? \Carbon\Carbon::parse($offer->valid_from)->format('d M, Y') : 'N/A' }}</div>
                                                <div class="text-danger">To: {{ $offer->valid_until ? \Carbon\Carbon::parse($offer->valid_until)->format('d M, Y') : 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fs-13">
                                                <span class="fw-medium text-dark">{{ $offer->used_count }}</span>
                                                <span class="text-muted">/ {{ $offer->max_uses ?? '∞' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input change-offer-status" type="checkbox" 
                                                    data-id="{{ $offer->id }}" {{ $offer->status == 1 ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('edit.offer', $offer->id) }}" class="btn btn-sm btn-soft-primary">
                                                    <iconify-icon icon="solar:pen-linear"></iconify-icon>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-soft-danger delete-offer" data-id="{{ $offer->id }}">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $offers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const $checkAll = $('#checkAlloffer');
    const $rowCheckbox = $('.offer-checkbox');
    const $deleteBtn = $('#bulk-offer-delete-btn');
    const $exportBtn = $('#bulk-offer-export-btn');
    const $filterForm = $('#filter-form');
    const $searchInput = $filterForm.find('input[name="search"]');
    let lastSearchValue = $searchInput.val();

    function toggleBulkButtons() {
        let checkedCount = $('.offer-checkbox:checked').length;

        if (checkedCount > 0) {
            $deleteBtn.removeClass('d-none').prop('disabled', false);
            $exportBtn.removeClass('d-none').prop('disabled', false);
        } else {
            $deleteBtn.addClass('d-none').prop('disabled', true);
            $exportBtn.addClass('d-none').prop('disabled', true);
        }
    }

    // ✅ Check All
    $checkAll.on('change', function () {
        $('.offer-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkButtons();
    });

    // ✅ Individual Checkbox
    $(document).on('change', '.offer-checkbox', function () {

        let total = $('.offer-checkbox').length;
        let checked = $('.offer-checkbox:checked').length;

        $checkAll.prop('checked', total > 0 && total === checked);

        toggleBulkButtons();
    });

    // Initialize on page load
    toggleBulkButtons();

    // ✅ Bulk Delete
    $deleteBtn.on('click', function () {
        const ids = $('.offer-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to delete the selected coupons?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete selected!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('bulk.delete.offer') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: ids
                        },
                        success: function (response) {
                            if (response.status) {
                                toastr.success(response.message);
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function () {
                            toastr.error('Something went wrong');
                        }
                    });
                }
            });
        }
    });

    // ✅ Bulk Export
    $exportBtn.on('click', function () {
        const ids = $('.offer-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length > 0) {
            var form = $('<form>', {
                action: "{{ route('export.offers') }}",
                method: "GET"
            });

            $.each(ids, function (index, id) {
                form.append($('<input>', {
                    type: "hidden",
                    name: "ids[]",
                    value: id
                }));
            });

            $('body').append(form);
            form.submit();
            form.remove();
        }
    });

    // ✅ Change Status Handler
    $(document).on('change', '.change-offer-status', function() {
        var status = $(this).prop('checked') ? 1 : 0;
        var offer_id = $(this).data('id');
        var url = "{{ route('change.offer.status') }}";
        
        $.ajax({
            type: "POST",
            url: url,
            data: {
                _token: '{{ csrf_token() }}',
                status: status,
                id: offer_id
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                } else {
                    toastr.error('Something went wrong');
                }
            },
            error: function() {
                toastr.error('Something went wrong');
            }
        });
    });

    $searchInput.on('blur', function () {
        const current = $(this).val();
        if (current !== lastSearchValue) {
            $filterForm.trigger('submit');
        }
    });

});
</script>
@endpush
