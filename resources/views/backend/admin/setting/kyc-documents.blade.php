@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">KYC Documents</h4>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKycModal">
                        <i class="bx bx-plus me-1"></i> Add Document
                    </button>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;">S.No</th>
                                        <th>Document Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $key => $doc)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $doc->name }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-status" type="checkbox" 
                                                    data-id="{{ $doc->id }}" {{ $doc->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $doc->is_active ? 'Active' : 'Inactive' }}</label>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-soft-info edit-doc" 
                                                data-id="{{ $doc->id }}" data-name="{{ $doc->name }}">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-soft-danger delete-doc" 
                                                data-id="{{ $doc->id }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No documents found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addKycModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add KYC Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('kyc.documents.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="doc_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Name</label>
                        <input type="text" name="name" id="doc_name" class="form-control" placeholder="e.g. Citizenship, PAN Card" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Edit Document
        $('.edit-doc').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#doc_id').val(id);
            $('#doc_name').val(name);
            $('#modalTitle').text('Edit KYC Document');
            $('#addKycModal').modal('show');
        });

        // Reset Modal on Close
        $('#addKycModal').on('hidden.bs.modal', function() {
            $('#doc_id').val('');
            $('#doc_name').val('');
            $('#modalTitle').text('Add KYC Document');
        });

        // Toggle Status
        $('.toggle-status').on('change', function() {
            const id = $(this).data('id');
            const status = $(this).prop('checked') ? 1 : 0;
            const label = $(this).siblings('.form-check-label');

            $.ajax({
                url: "{{ route('kyc.documents.status') }}",
                type: "POST",
                data: {
                    id: id,
                    status: status,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        label.text(status ? 'Active' : 'Inactive');
                        toastr.success(response.message);
                    } else {
                        toastr.error('Failed to update status');
                    }
                }
            });
        });

        // Delete Document
        $('.delete-doc').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this document type!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('kyc.documents.delete') }}",
                        type: "POST",
                        data: {
                            id: id,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status) {
                                toastr.success(response.message);
                                location.reload();
                            } else {
                                toastr.error('Failed to delete');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
