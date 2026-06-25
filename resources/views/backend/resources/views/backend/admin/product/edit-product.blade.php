@extends('backend.layouts.app')
@section('content')

<div class="page-content">
  <div class="container-fluid">
    <div class="row align-items-center mb-3">
      <div class="col">
        <h4 class="mb-0">Update Product</h4>
      </div>
      <div class="col-auto">
        <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
          <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
          Back to List
        </a>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-4 anchor" id="basic">
              Update Product
            </h5>
            <div class="">
              <form action="{{ route('update.product') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="product_id" value="{{ $product_data->id }}">

                <!-- Product Name & Slug -->
                <div class="row">
                  <div class="mb-3 col-md-6">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control"
                      value="{{ old('name', $product_data->name) }}" maxlength="255" required>
                  </div>

                  <div class="mb-3 col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control"
                      value="{{ old('slug', $product_data->slug) }}" maxlength="255" readonly>
                  </div>
                </div>

                <!-- Category & Brand -->
                <div class="row">
                  <div class="mb-3 col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select category_id" required>
                      <option value="">Select Category</option>
                      @foreach($categories as $cat)
                      <option value="{{ $cat->id }}"
                        {{ $product_data->category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3 col-md-6">
                    <label class="form-label">Brand</label>
                    <select name="brand_id" class="form-select categoryBrand">
                      <option value="">Select Brand</option>
                      @foreach($brand as $b)
                      <option value="{{ $b->id }}"
                        {{ $product_data->brand_id == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="row">
                   @if(Auth::user()->role == 1)
                  <div class="mb-3 col-md-12">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" @if(Auth::user()->role == 2) disabled @endif>
                      <option value="1" {{ $product_data->status == 1 ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ $product_data->status == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @elseif(Auth::user()->role == 2)
                        <input type="hidden" name="status" value="{{ $product_data->status }}">
                    @endif
                  </div>
                </div>




                <!-- Descriptions -->
                <div class="mb-3">
                  <label class="form-label">Short Description</label>
                  <textarea name="short_description" class="form-control" rows="2" maxlength="1000">
                  {{ old('short_description', $product_data->short_description) }}
                  </textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea name="description" class="form-control" rows="4" maxlength="5000">
                  {{ old('description', $product_data->description) }}
                  </textarea>
                </div>
                <div class="mb-3">
                  <label>Offers</label>
                  @foreach ($offers as $value)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="offers[]" @if(in_array($value->id, is_string($product_data->offer_id) ? json_decode($product_data->offer_id, true) : (is_array($product_data->offer_id) ? $product_data->offer_id : [$product_data->offer_id]) ?? [])) checked @endif value="{{ $value->id }}">
                    <label class="form-check-label">{{ $value->code }}</label>
                  </div>
                  @endforeach
                </div>

                <div class="mb-3">
                  <label>Product In</label>
                  @php
                  // Decode product_in safely as array
                  $prod_in = [];
                  $decoded = json_decode($product_data->product_in, true);
                  $prod_in = is_array($decoded) ? $decoded : [];

                  @endphp

                  <div class="form-check">
                    <input class="form-check-input"
                      type="checkbox"
                      name="product_in[]"
                      value="1"
                      {{ in_array(1, $prod_in) ? 'checked' : '' }}>
                    <label class="form-check-label">Best Seller</label>
                  </div>

                  <div class="form-check">
                    <input class="form-check-input"
                      type="checkbox"
                      name="product_in[]"
                      value="2"
                      {{ in_array(2, $prod_in) ? 'checked' : '' }}>
                    <label class="form-check-label">Trending</label>
                  </div>

                  <div class="form-check">
                    <input class="form-check-input"
                      type="checkbox"
                      name="product_in[]"
                      value="3"
                      {{ in_array(3, $prod_in) ? 'checked' : '' }}>
                    <label class="form-check-label">Popular</label>
                  </div>

                  <div class="form-check">
                    <input class="form-check-input"
                      type="checkbox"
                      name="product_in[]"
                      value="4"
                      {{ in_array(4, $prod_in) ? 'checked' : '' }}>
                    <label class="form-check-label">Deal</label>
                  </div>


                  <div class="mb-3">
                    <label class="form-label fw-bold">Additional Options</label>
                    <div class="p-3 border rounded bg-light-subtle">
                      <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ $product_data->is_featured == 1 ? 'checked' : '' }}>
                        <label class="form-check-label cursor-pointer" for="is_featured">Mark as Featured Product</label>
                      </div>
                      <hr>
                      <div class="row align-items-center">
                        <div class="col-md-3">
                          <label class="form-label">Warranty</label>
                          <select name="vendor_warranty" class="form-select" id="vendor_warranty">
                            <option value="" {{ $product_data->vendor_warranty == '' ? 'selected' : '' }}>No Warranty</option>
                            <option value="6 Months" {{ $product_data->vendor_warranty == '6 Months' ? 'selected' : '' }}>6 Months</option>
                            <option value="1 Year" {{ $product_data->vendor_warranty == '1 Year' ? 'selected' : '' }}>1 Year</option>
                            <option value="2 Years" {{ $product_data->vendor_warranty == '2 Years' ? 'selected' : '' }}>2 Years</option>
                            <option value="3 Years" {{ $product_data->vendor_warranty == '3 Years' ? 'selected' : '' }}>3 Years</option>
                            <option value="5 Years" {{ $product_data->vendor_warranty == '5 Years' ? 'selected' : '' }}>5 Years</option>
                            <option value="Lifetime" {{ $product_data->vendor_warranty == 'Lifetime' ? 'selected' : '' }}>Lifetime</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="vendor_payment" value="1" id="vendor_payment" {{ $product_data->vendor_payment == 1 ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer" for="vendor_payment">100% Secure Payments</label>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="vendor_return" value="1" id="vendor_return" {{ $product_data->vendor_return == 1 ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer" for="vendor_return">Easy & Hassle-Free Returns</label>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="vendor_delivery" value="1" id="vendor_delivery" {{ $product_data->vendor_delivery == 1 ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer" for="vendor_delivery">Free Delivery</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Images & Status -->
                  <div class="row">


                    <button type="submit" class="btn btn-primary">
                      <iconify-icon icon="solar:diskette-linear" class="me-1"></iconify-icon> Update Product
                    </button>

              </form>


            </div>
          </div>
        </div>
      </div> <!-- end row -->
    </div>
  </div>
</div>
<!-- End Container Fluid -->
@endsection




