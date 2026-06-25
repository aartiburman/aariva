@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Add Product Size</h4>
                    <div class="page-title-right">
                        <a href="{{ route('add.product.size.category') }}" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                            Back to List
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Create New Size</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('store.product.size') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="size_cat_id" value="{{ $sizecategory->id }}">

                            <div class="row align-items-end">
                                <!-- Category Size (Readonly) -->
                                <div class="col-md-4">
                                    <div class="mb-0">
                                        <label class="form-label">Size Category</label>
                                        <input type="text" name="category_name" class="form-control bg-light" value="{{ old('name', $sizecategory->name) }}" readonly>
                                    </div>
                                </div>

                                <!-- Product Size -->
                                <div class="col-md-4">
                                    <div class="mb-0">
                                        <label class="form-label">New Size Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="e.g. XL, 42, 10-inch" value="{{ old('name') }}" required>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <iconify-icon icon="solar:add-circle-linear" class="me-1"></iconify-icon>
                                        Create Product Size
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> <!-- end col -->
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Existing Sizes in {{ $sizecategory->name }}</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                            </div>
                                        </th>
                                        <th>Size Name</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($allsize as $value)
                                    <tr id="row_{{ $value->id }}">
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium text-dark">{{ $value->name }}</span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input change-product-size-status" type="checkbox" role="switch" id="status_{{ $value->id }}" data-id="{{ $value->id }}" {{ $value->status == 1 ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <a href="{{ url('edit-product-size/'.$value->id) }}" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" title="Edit">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-18"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-sm btn-soft-danger delete-product-size" data-id="{{ $value->id }}" data-bs-toggle="tooltip" title="Delete">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-18"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($allsize->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center p-4">
                                            <div class="text-muted">
                                                <iconify-icon icon="solar:info-circle-linear" class="fs-24 mb-2"></iconify-icon>
                                                <p class="mb-0">No sizes found for this category.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
        </div>
    </div>
    <!-- End Container Fluid -->


</div>
@endsection
