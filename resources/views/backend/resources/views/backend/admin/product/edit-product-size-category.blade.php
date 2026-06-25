@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1 anchor mb-3" id="basic">
                            Add Product Size Category
                        </h5>

                        <form action="{{ route('update.product.size.category') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="size_id" value="{{ $sizeType->id }}">
                            <!-- Product Name & Slug -->
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Product Size Type Name
                                    </label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name',$sizeType->name) }}" required>
                                </div>


                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status" id="">
                                        <option value="" seleted disabled>Select Status</option>
                                        <option @if($sizeType->status == 1) selected @endif value="1">Active</option>
                                        <option @if($sizeType->status == 0) selected @endif value="0">Inactive</option>

                                    </select>
                                </div>
                            </div>


                            <!-- Images & Status -->
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <button type="submit" class="btn btn-primary">
                                        <iconify-icon icon="solar:diskette-linear" class="me-1"></iconify-icon> Update Product Size
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
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 anchor" id="basic">
                                Product Size Category
                            </h5>
                            <a href="{{route('add.product.size', $sizeType->id) }}" class="btn btn-primary btn-sm">
                                <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon> Add Size
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-centered">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox">
                                        </th>
                                        <th>Sizes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allsize as $value)
                                    <tr id="row_{{ $value->id }}">
                                        <td>
                                            <input type="checkbox">
                                        </td>

                                        <td>
                                            <strong>{{ $value->name }}</strong>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="{{ url('edit-product-size/'.$value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Update Product Size">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-product-size" data-id="{{ $value->id }}" data-bs-toggle="tooltip" title="Delete Product Size">
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


            </div> <!-- end col -->

        </div>

    </div>
    <!-- End Container Fluid -->


</div>
@endsection