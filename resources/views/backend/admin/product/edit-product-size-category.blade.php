@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Product Size Category</h4>
                    <div class="page-title-right">
                        <a href="{{ route('add.product.size.category') }}" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                            Back to List
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">General Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('update.product.size.category') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="size_id" value="{{ $sizeType->id }}">
                            
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <div class="mb-3">
                                        <label class="form-label">Product Size Type Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name',$sizeType->name) }}" required placeholder="Enter Size Type Name">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input change-product-size-category-status" type="checkbox" role="switch" id="category_status_{{ $sizeType->id }}" data-id="{{ $sizeType->id }}" {{ $sizeType->status == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="category_status_{{ $sizeType->id }}">Active / Inactive</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <iconify-icon icon="solar:diskette-linear" class="me-1"></iconify-icon> Update Category
                                        </button>
                                    </div>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Manage Sizes</h4>
                        <a href="{{route('add.product.size', $sizeType->id) }}" class="btn btn-primary btn-sm">
                            <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon> Add New Size
                        </a>
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