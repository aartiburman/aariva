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
                        <form action="{{ route('update.product.size') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="size_id" value="{{ $sizes->id }}">
                            <!-- Product Name & Slug -->
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Product Size Category
                                    </label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name',$sizes->product_size_category_name) }}" readonly required>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Product Size
                                    </label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name',$sizes->name) }}" required>
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


        </div> <!-- end row -->
    </div>
    <!-- End Container Fluid -->


</div>
@endsection
