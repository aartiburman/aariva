@extends('backend/layouts.app')
@section('content')
<!-- ==================================================== -->
<!-- Start right Content here -->
<!-- ==================================================== -->
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-xxl">

        <div class="row">
            <div class="col-xl-12 col-lg-8 ">

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Banner</h4>
                    </div>
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <form>
                                    <div class="mb-3">
                                        <label for="category-title" class="form-label">Category Title</label>
                                        <input type="text" id="category-title" class="form-control" placeholder="Enter Title">
                                    </div>
                                </form>
                            </div>

                            <div class="col-mg-12">
                                <form action="{{ route('store.banner') }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror">
                                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Position</label>
                                            <select name="position" class="form-select @error('position') is-invalid @enderror">
                                                <option value="top" {{ old('position') == 'top' ? 'selected' : '' }}>Top</option>
                                                <option value="deal" {{ old('position') == 'deal' ? 'selected' : '' }}>Deal</option>
                                                <option value="middle" {{ old('position') == 'middle' ? 'selected' : '' }}>Middle</option>
                                                <option value="bottom" {{ old('position') == 'bottom' ? 'selected' : '' }}>Bottom</option>
                                                <option value="wishlist" {{ old('position') == 'wishlist' ? 'selected' : '' }}>wishlist</option>
                                                <option value="cart" {{ old('position') == 'cart' ? 'selected' : '' }}>cart</option>
                                                <!-- <option value="contact" {{ old('position') == 'contact' ? 'selected' : '' }}>contact</option>\ -->
                                                <option value="product_detail" {{ old('position') == 'product_detail' ? 'selected' : '' }}>Product Detail</option>
                                            </select>
                                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- <div class="mb-3 col-md-6">
                                            <label class="form-label">Order</label>
                                            <input type="number" name="order_by" value="{{ old('order_by', 0) }}" class="form-control @error('order_by') is-invalid @enderror">
                                            @error('order_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div> -->

                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control @error('start_date') is-invalid @enderror">
                                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">End Date</label>
                                            <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control @error('end_date') is-invalid @enderror">
                                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Link Type</label>
                                            <select name="link_type" class="form-select @error('link_type') is-invalid @enderror">
                                                <option value="">None</option>
                                                <option value="product" {{ old('link_type') == 'product' ? 'selected' : '' }}>Product</option>
                                                <option value="category" {{ old('link_type') == 'category' ? 'selected' : '' }}>Category</option>
                                                <option value="brand" {{ old('link_type') == 'brand' ? 'selected' : '' }}>Brand</option>
                                                <option value="external" {{ old('link_type') == 'external' ? 'selected' : '' }}>External URL</option>
                                            </select>
                                            @error('link_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3 col-md-3">
                                            <label class="form-label">Link ID</label>
                                            <input type="number" name="link_id" value="{{ old('link_id') }}" class="form-control @error('link_id') is-invalid @enderror">
                                            @error('link_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3 col-md-3">
                                            <label class="form-label">External URL</label>
                                            <input type="url" name="link_url" value="{{ old('link_url') }}" class="form-control @error('link_url') is-invalid @enderror">
                                            @error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Banner Image</label>
                                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="p-3 bg-light mb-3 rounded">
                                        <div class="row justify-content-end g-2">

                                            <div class="col-lg-2">
                                                <a href="#!" class="btn btn-outline-secondary w-100">Cancel</a>
                                            </div>
                                            <div class="col-lg-2">
                                                <button type="submit" class="btn btn-primary w-100">Save Banner</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- End Container Fluid -->

   
<!-- ==================================================== -->
<!-- End Page Content -->
<!-- ==================================================== -->

@endsection
