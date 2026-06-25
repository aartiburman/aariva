@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 text-dark fw-bold">Blog List</h4>
                    <a href="{{ route('admin.blog.add') }}" class="btn btn-primary">
                        <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon>
                        Add New Blog
                    </a>
                </div>
            </div>
        </div>

        <!-- Blog Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-4" style="width: 50px;">ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Created At</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blogs as $blog)
                            <tr>
                                <td class="ps-4">{{ $blog->id }}</td>
                                <td>
                                    <div class="avatar-md bg-light overflow-hidden">
                                        @if($blog->image)
                                            <img src="{{ asset('uploads/blog/' . $blog->image) }}" alt="" class="img-fluid h-100 w-100 object-fit-cover">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center h-100 w-100 text-muted">
                                                <iconify-icon icon="solar:gallery-linear" class="fs-24"></iconify-icon>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-0 fw-bold">{{ Str::limit($blog->title, 50) }}</h6>
                                    <small class="text-muted">{{ $blog->slug }}</small>
                                </td>
                                <td>{{ $blog->author->name ?? 'Admin' }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input status-switch" type="checkbox" role="switch" 
                                            data-id="{{ $blog->id }}" {{ $blog->status ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>{{ number_format($blog->views) }}</td>
                                <td>{{ $blog->created_at->format('d M, Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <a href="{{ route('admin.blog.edit', $blog->id) }}" class="text-purple hover-opacity-100" title="Edit">
                                            <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                        </a>
                                        <a href="{{ route('admin.blog.delete', $blog->id) }}" class="text-purple hover-opacity-100 delete-blog-btn" 
                                           title="Delete">
                                            <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    No blogs found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-top bg-transparent">
                {{ $blogs->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete confirmation
        const deleteBtns = document.querySelectorAll('.delete-blog-btn');
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });

        const switches = document.querySelectorAll('.status-switch');
        switches.forEach(sw => {
            sw.addEventListener('change', function() {
                const id = this.dataset.id;
                const status = this.checked ? 1 : 0;
                
                fetch("{{ route('admin.blog.update.status') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id, status })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status) {
                        toastr.success(data.message);
                    } else {
                        toastr.error('Failed to update status');
                        this.checked = !this.checked;
                    }
                })
                .catch(error => {
                    toastr.error('An error occurred');
                    this.checked = !this.checked;
                });
            });
        });
    });
</script>
@endpush
@endsection
