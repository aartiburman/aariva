@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1 anchor" id="basic">
                            Basic Example
                        </h5>
                        <form action="{{ route('store.product.size') }}" method="POST" enctype="multipart/form-data"
                            class="row g-2 align-items-end">
                            @csrf

                            <input type="hidden" name="size_cat_id" value="{{ $sizecategory->id }}">

                            <!-- Category Size (Readonly) -->
                            <div class="col-md-4">
                                <label class="form-label mb-1">Product New Category Size</label>
                                <input type="text"
                                    name="category_name"
                                    class="form-control"
                                    value="{{ old('name', $sizecategory->name) }}"
                                    readonly>
                            </div>

                            <!-- Product Size -->
                            <div class="col-md-4">
                                <label class="form-label mb-1">Product Size</label>
                                <input type="text"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter size"
                                    value="{{ old('name') }}"
                                    required>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <iconify-icon icon="solar:diskette-linear" class="me-1"></iconify-icon>
                                    Create Product Size
                                </button>
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
                        <h5 class="card-title mb-1 anchor" id="basic">
                            Basic Example 
                        </h5>
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
