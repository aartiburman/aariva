@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0">About Us List</h4>
                    
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($about_us as $about)
            <div class="col-md-12 col-xl-12 mb-4">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title fw-bold text-dark mb-0 text-truncate pe-2" style="max-width: 80%;">
                                {!! strip_tags($about->title) !!}
                            </h5>
                            @if($about->status == 1)
                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fs-11 text-uppercase">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fs-11 text-uppercase">Inactive</span>
                            @endif
                        </div>
                        
                        <div class="card-text text-muted mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {!! strip_tags($about->content) !!}
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <small class="text-muted fs-12">
                                <iconify-icon icon="solar:calendar-linear" class="align-middle me-1"></iconify-icon>
                                {{ $about->created_at->format('M d, Y') }}
                            </small>
                            <div class="d-flex gap-2">
                                <a href="{{ route('about.us.edit', $about->id) }}" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" title="Edit">
                                    <iconify-icon icon="solar:pen-linear" class="fs-16"></iconify-icon> Edit
                                </a>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <iconify-icon icon="solar:folder-error-linear" width="64" class="text-muted mb-3"></iconify-icon>
                        <h6 class="text-muted">No about us records found</h6>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

      
    </div>
</div>


@endsection
