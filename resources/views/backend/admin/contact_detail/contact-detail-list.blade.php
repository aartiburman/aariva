@extends('backend.layouts.app')
@section('content')
<section class="pc-container">
    <div class="pc-content">
        <div class="card">
            <div class="card-header">
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="page-header-title">
                                    <h5 class="mb-0">Contact Details List</h5>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <ul class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="javascript: void(0)">List</a></li>
                                    <li class="breadcrumb-item" aria-current="page">Contact Details</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5 style="display:inline-block;">Contact Details</h5>
                        <a href="{{ route('add.contact.detail') }}" class="btn btn-success d-inline-flex" style="float:right;color:white;">
                            <iconify-icon icon="solar:plus-linear" class="me-1"></iconify-icon>Add Contact Detail
                        </a>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatables">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contacts as $value)
                                    <tr id="row_{{ $value->id }}">
                                        <td>{{ $value->title }}</td>
                                        <td>{{ $value->email }}</td>
                                        <td>{{ $value->phone }}</td>
                                        <td>{{ $value->address }}</td>
                                        <td>
                                            <select class="form-select form-select-sm contact-status" data-id="{{ $value->id }}">
                                                <option value="1" {{ $value->status == 1 ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ $value->status == 0 ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('edit.contact.detail', $value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Contact Detail">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-contact" data-id="{{ $value->id }}" data-bs-toggle="tooltip" title="Delete">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
$(function(){
    $('.contact-status').change(function(){
        var id = $(this).data('id');
        var status = $(this).val();
        $.post("{{ route('change.contact.detail.status') }}", {
            id: id,
            status: status,
            _token: "{{ csrf_token() }}"
        }, function(resp){
            if(resp.status){
                toastr.success(resp.message);
            }
        });
    });
    $('.delete-contact').click(function(){
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "Delete this contact detail?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('delete.contact.detail') }}", {
                    id: id,
                    _token: "{{ csrf_token() }}"
                }, function(resp){
                    if(resp.status){
                        row.remove();
                        toastr.success(resp.message);
                        Swal.fire(
                            'Deleted!',
                            resp.message,
                            'success'
                        )
                    } else {
                        Swal.fire(
                            'Error!',
                            'Something went wrong.',
                            'error'
                        )
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection

