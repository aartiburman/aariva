@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Warehouses</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                            <iconify-icon icon="solar:add-circle-linear" class="fs-18"></iconify-icon> Add Warehouse
                        </button>
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse($warehouses as $w)
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ $w->name }}</h5>
                                <span class="badge {{ $w->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $w->status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-soft-secondary" data-bs-toggle="dropdown">
                                    <iconify-icon icon="solar:menu-dots-linear"></iconify-icon>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="editWarehouse({{ $w->id }}, '{{ $w->name }}', '{{ $w->location }}', '{{ $w->phone }}', '{{ $w->email }}', '{{ $w->manager_name }}', {{ $w->status ? 'true' : 'false' }})">
                                        <iconify-icon icon="solar:pen-linear" class="me-1"></iconify-icon> Edit
                                    </a></li>
                                    <li>
                                        <form action="{{ route('inventory.warehouse.delete', $w->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <iconify-icon icon="solar:trash-bin-trash-linear" class="me-1"></iconify-icon> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <div class="small">
                            @if($w->location)<p class="mb-1"><iconify-icon icon="solar:map-point-linear" class="me-1"></iconify-icon> {{ $w->location }}</p>@endif
                            @if($w->phone)<p class="mb-1"><iconify-icon icon="solar:phone-linear" class="me-1"></iconify-icon> {{ $w->phone }}</p>@endif
                            @if($w->email)<p class="mb-1"><iconify-icon icon="solar:letter-linear" class="me-1"></iconify-icon> {{ $w->email }}</p>@endif
                            @if($w->manager_name)<p class="mb-0"><iconify-icon icon="solar:user-linear" class="me-1"></iconify-icon> {{ $w->manager_name }}</p>@endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12"><div class="alert alert-info text-center">No warehouses yet. Add your first warehouse.</div></div>
            @endforelse
        </div>
    </div>
</div>

<!-- Add Warehouse Modal -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.warehouse.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" maxlength="255">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" maxlength="20">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="255">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager_name" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Warehouse Modal -->
<div class="modal fade" id="editWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editWarehouseForm">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="edit_location" class="form-control">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager_name" id="edit_manager" class="form-control">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="edit_status" checked>
                            <label class="form-check-label" for="edit_status">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editWarehouse(id, name, location, phone, email, manager, status) {
    $('#edit_name').val(name);
    $('#edit_location').val(location);
    $('#edit_phone').val(phone);
    $('#edit_email').val(email);
    $('#edit_manager').val(manager);
    $('#edit_status').prop('checked', status);
    $('#editWarehouseForm').attr('action', '{{ url("inventory/warehouses") }}/' + id);
    $('#editWarehouseModal').modal('show');
}

$(document).ready(function() {
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Delete warehouse?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
