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
                <form action="{{ route('coupon.list') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Coupon Code</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search code..." value="{{ request('search') }}" onblur="this.form.submit()">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">All Type</option>
                            <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Percentage</option>
                            <option value="0" {{ request('type') == '0' ? 'selected' : '' }}>Fixed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('coupon.list') }}" class="btn btn-outline-secondary w-100">Reset</a>
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
                            <form id="exportForm" action="{{ route('coupon.export.multiple') }}" method="POST" class="d-inline">
                                @csrf
                                <div id="exportInputs"></div>
                                <button type="button" class="btn btn-sm btn-success" id="exportSelectedBtn" style="display: none;">Export Selected</button>
                            </form>
                            <a href="{{ route('coupon.create') }}" class="btn btn-sm btn-primary">Add Coupon</a>
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
                                    @foreach($coupons as $coupon)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input row-checkbox" type="checkbox" value="{{ $coupon->id }}">
                                            </div>
                                        </td>
                                        <td><strong>{{ $coupon->code }}</strong></td>
                                        <td>
                                            @if($coupon->type == 1)
                                                <span class="badge bg-soft-success text-success">{{ $coupon->value }}%</span>
                                            @else
                                                <span class="badge bg-soft-info text-info">Fixed {{ $coupon->value }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fs-13">
                                                From: {{ $coupon->valid_from ? $coupon->valid_from->format('d M, Y') : 'N/A' }}<br>
                                                To: {{ $coupon->valid_until ? $coupon->valid_until->format('d M, Y') : 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fs-12">
                                                @if($coupon->products->isEmpty() && $coupon->categories->isEmpty() && $coupon->vendors->isEmpty())
                                                    <span class="badge bg-soft-secondary text-secondary">Global (All)</span>
                                                @else
                                                    @if($coupon->products->isNotEmpty())
                                                        <div><strong>Products:</strong> 
                                                            {{ $coupon->products->pluck('name')->implode(', ') }}
                                                        </div>
                                                    @endif
                                                    @if($coupon->categories->isNotEmpty())
                                                        <div><strong>Categories:</strong> 
                                                            {{ $coupon->categories->pluck('name')->implode(', ') }}
                                                        </div>
                                                    @endif
                                                    @if($coupon->vendors->isNotEmpty())
                                                        <div><strong>Vendors:</strong> 
                                                            {{ $coupon->vendors->pluck('name')->implode(', ') }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input change-status" type="checkbox" data-id="{{ $coupon->id }}" {{ $coupon->status ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('coupon.edit', $coupon->id) }}" class="btn btn-sm btn-soft-primary"> <iconify-icon icon="solar:pen-linear"></iconify-icon></a>
                                                <button class="btn btn-sm btn-soft-danger delete-coupon" data-id="{{ $coupon->id }}"><iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon></button>

                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            {{ $coupons->links() }}
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
    $('.change-status').on('change', function() {
        var id = $(this).data('id');
        $.post("{{ route('coupon.status') }}", {id: id, _token: "{{ csrf_token() }}"}, function(res) {
            if(res.status) toastr.success(res.message);
        });
    });

    $('.delete-coupon').on('click', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to delete this coupon?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('coupon.delete') }}", {id: id, _token: "{{ csrf_token() }}"}, function(res) {
                    if(res.status) {
                        toastr.success(res.message);
                        location.reload();
                    }
                });
            }
        });
    });

    // Select All Checkbox
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
        toggleActionButtons();
    });

    $('.row-checkbox').on('change', function() {
        if ($('.row-checkbox:checked').length === $('.row-checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
        toggleActionButtons();
    });

    function toggleActionButtons() {
        if ($('.row-checkbox:checked').length > 0) {
            $('#deleteSelectedBtn').show();
            $('#exportSelectedBtn').show();
        } else {
            $('#deleteSelectedBtn').hide();
            $('#exportSelectedBtn').hide();
        }
    }

    // Delete Selected
    $('#deleteSelectedBtn').on('click', function() {
        let selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one coupon to delete.');
            return;
        }

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
                    url: "{{ route('coupon.delete.multiple') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: selectedIds
                    },
                    success: function(res) {
                        if (res.status) {
                            toastr.success(res.message);
                            location.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
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
