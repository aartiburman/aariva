@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">FAQ List</h4>

                        <div class="search-bar me-3">
                            <form action="{{ route('faq.list') }}" method="GET" class="d-flex align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm me-1" placeholder="Search FAQs..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                                @if(request('search'))
                                <a href="{{ route('faq.list') }}" class="btn btn-sm btn-secondary ms-1">Clear</a>
                                @endif
                            </form>
                        </div>

                        <a href="{{ route('faq.add') }}" class="btn btn-sm btn-primary">
                            Add New FAQ
                        </a>
                    </div>

                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">SNO</th>
                                        <th>QUESTION</th>
                                        <th>STATUS</th>
                                        <th>CREATED AT</th>
                                        <th class="pe-4 text-center">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($faqs as $key => $faq)

                                    <tr id="row_{{ $faq->id }}">
                                        <td class="ps-4">{{ $key + 1 }}</td>
                                        <td >
                                            <h6 class="mb-0 fw-bold text-dark fs-14">{{ $faq->question }}</h6>
                                        </td>
                                        <td>
                                            @if($faq->status == 1)
                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-11 text-uppercase">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-11 text-uppercase">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="fs-13 text-muted">{{ $faq->created_at->format('M d, Y') }}</td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('faq.edit', $faq->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-faq" data-id="{{ $faq->id }}" data-bs-toggle="tooltip" title="Delete">    
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <iconify-icon icon="solar:folder-error-linear" width="64" class="text-muted mb-3"></iconify-icon>
                                                <h6 class="text-muted">No FAQs found</h6>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($faqs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer border-top">
                        {{ $faqs->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-purple { color: var(--theme-primary-color) !important; }
    .hover-opacity-100:hover { opacity: 1 !important; }
    .bg-soft-success { background-color: rgba(40, 167, 69, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .fs-13 { font-size: 0.8125rem; }
    .fs-11 { font-size: 0.6875rem; }
    .fs-14 { font-size: 0.875rem; }
</style>
@endsection

