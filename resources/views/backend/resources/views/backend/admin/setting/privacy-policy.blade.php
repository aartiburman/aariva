@extends('backend.layouts.app')

@section('content')


        <div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Privacy Policy List</h4>
                    </div>

                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">TITLE</th>
                                        <th>STATUS</th>
                                        <th>CREATED AT</th>
                                        <th class="pe-4 text-center">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($policies as $policy)
                                    <tr>
                                        <td class="ps-4">
                                            <h6 class="mb-0 fw-bold text-dark fs-14">{{ $policy->title }}</h6>
                                        </td>
                                        <td>
                                            @if($policy->status == 1)
                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-11 text-uppercase">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-11 text-uppercase">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="fs-13 text-muted">{{ $policy->created_at->format('M d, Y') }}</td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('privacy.policy.edit', $policy->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <iconify-icon icon="solar:folder-error-linear" width="64" class="text-muted mb-3"></iconify-icon>
                                                <h6 class="text-muted">No privacy policy records found</h6>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($policies instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer border-top">
                        {{ $policies->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deletePolicy(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#5d1a8f',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('privacy.policy.delete') }}",
                    type: "POST",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            }
        })
    }
</script>

@endsection

