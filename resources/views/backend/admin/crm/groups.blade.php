@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Customer Groups</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">+ Add Group</button>
                        <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr><th>Name</th><th>Description</th><th>Customers</th><th>Status</th><th>Created</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($groups as $g)
                            <tr>
                                <td class="fw-medium">{{ $g->name }}</td>
                                <td><small>{{ $g->description ?? '—' }}</small></td>
                                <td><span class="badge bg-info">{{ $g->customers_count }}</span></td>
                                <td>
                                    <span class="badge {{ $g->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $g->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ $g->created_at->format('d M Y') }}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-soft-primary" onclick="editGroup({{ $g->id }}, '{{ $g->name }}', '{{ $g->description }}', {{ $g->status ? 'true' : 'false' }})">Edit</button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No groups created</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($groups->hasPages())<div class="card-footer">{{ $groups->links() }}</div>@endif
        </div>
    </div>
</div>

<!-- Add Group Modal -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('crm.group.store') }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Group</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="1000"></textarea>
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

<!-- Edit Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editGroupForm">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Edit Group</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
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
function editGroup(id, name, description, status) {
    $('#edit_name').val(name);
    $('#edit_description').val(description);
    $('#edit_status').prop('checked', status);
    $('#editGroupForm').attr('action', '{{ url("crm/groups") }}/' + id);
    $('#editGroupModal').modal('show');
}
</script>
@endpush
