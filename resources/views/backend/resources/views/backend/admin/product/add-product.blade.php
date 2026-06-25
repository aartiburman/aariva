@extends('backend.layouts.app')
@section('content')
<div class="page-content">
  <!-- Start Container Fluid -->
  <div class="container-fluid">
       <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Add Product</h4>
                    <div class="page-title-right">
                        <div class="col-auto">
                        <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                            Back to List
                        </a>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="row">
      <div class="col-xl-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-2 anchor" id="basic">
              Add Product
            </h5>


            <form id="productForm"
              action="{{ route('store.product') }}"
              method="POST"
              enctype="multipart/form-data">
              @csrf

              {{-- ================= BASIC INFORMATION ================= --}}
              <div class="mb-4">
                <div class="card card-body">

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label>Product Name</label>
                      <input type="text" name="name" class="form-control product-name" maxlength="255" required>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label>Slug</label>
                      <input type="text" name="slug" class="form-control" maxlength="255" readonly>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label>Category</label>
                      <select name="category_id" class="form-select category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories_data as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-4 mb-3">
                      <label>Sub Category</label>
                      <select name="subcategory_id" class="form-select subcategory_id">
                        <option value="">Select Category First</option>
                      </select>
                    </div>

                    <div class="col-md-4 mb-3">
                      <label>Child Category</label>
                      <select name="child_category_id" class="form-select child_category_id">
                        <option value="">Select Sub Category First</option>
                      </select>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label>Brand</label>
                    <select name="brand_id" class="form-select categoryBrand">
                      <option value="">Select Brand</option>

                    </select>
                  </div>

                  <div class="mb-3">
                    <label>Short Description</label>
                    <textarea name="short_description" class="form-control" maxlength="1000"></textarea>
                  </div>

                  <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" rows="4" class="form-control" maxlength="5000"></textarea>
                  </div>

<!-- <div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent border-0">
    <h6 class="mb-0">Earnings Estimate</h6>
  </div>
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label">Selling Price</label>
        <input type="number" step="0.01" id="ee_price" class="form-control" placeholder="0.00">
      </div>
      <div class="col-md-2">
        <label class="form-label">Discount Type</label>
        <select id="ee_discount_type" class="form-select">
          <option value="">None</option>
          <option value="%">%</option>
          <option value="off">Flat</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Discount Value</label>
        <input type="number" step="0.01" id="ee_discount_value" class="form-control" placeholder="0">
      </div>
      <div class="col-md-2">
        <label class="form-label">Shipping</label>
        <input type="number" step="0.01" id="ee_shipping" class="form-control" placeholder="0">
      </div>
      <div class="col-md-2">
        <label class="form-label">Tax</label>
        <input type="number" step="0.01" id="ee_tax" class="form-control" placeholder="0">
      </div>
      <div class="col-md-2">
        <label class="form-label d-block">Net Payout</label>
        <div id="ee_net" class="fw-bold">0.00</div>
      </div>
    </div>
    <div class="row g-2 mt-2">
      <div class="col-md-3">
        <small>Commission: <span id="ee_commission">0.00</span> ({{ $commissionPercent ?? 0 }}%)</small>
      </div>
      <div class="col-md-3">
        <small>PG Fee: <span id="ee_pg">0.00</span> ({{ $pgFeePercent ?? 0 }}%)</small>
      </div>
      <div class="col-md-3">
        <small>Selling: <span id="ee_sp">0.00</span></small>
      </div>
    </div>
  </div>
</div> -->

                  <div class="row">
                    <div class="col-md-12 mb-3">
                      <label class="form-label fw-bold">Offers</label>
                      <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light-subtle">
                        @foreach ($offers as $value)
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="offers[]" value="{{ $value->id }}" id="offer_{{ $value->id }}">
                          <label class="form-check-label cursor-pointer" for="offer_{{ $value->id }}">{{ $value->code }}</label>
                        </div>
                        @endforeach
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12 mb-3">
                      <label class="form-label fw-bold">Product Placement</label>
                      <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light-subtle">
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="1" id="in_1">
                          <label class="form-check-label cursor-pointer" for="in_1">Best Seller</label>
                        </div>
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="2" id="in_2">
                          <label class="form-check-label cursor-pointer" for="in_2">Trending</label>
                        </div>
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="3" id="in_3">
                          <label class="form-check-label cursor-pointer" for="in_3">Popular</label>
                        </div>
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="4" id="in_4">
                          <label class="form-check-label cursor-pointer" for="in_4">Deal</label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12 mb-3">
                      <label class="form-label fw-bold">Additional Options</label>
                      <div class="p-3 border rounded bg-light-subtle">
                        <div class="form-check form-switch mb-2">
                          <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured">
                          <label class="form-check-label cursor-pointer" for="is_featured">Mark as Featured Product</label>
                        </div>
                        <hr>
                        <div class="row align-items-center">
                          <div class="col-md-3">
                            <label class="form-label">Warranty</label>
                            <select name="vendor_warranty" class="form-select" id="vendor_warranty">
                              <option value="">No Warranty</option>
                              <option value="6 Months">6 Months</option>
                              <option value="1 Year">1 Year</option>
                              <option value="2 Years">2 Years</option>
                              <option value="3 Years">3 Years</option>
                              <option value="5 Years">5 Years</option>
                              <option value="Lifetime">Lifetime</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <div class="form-check mt-3">
                              <input class="form-check-input" type="checkbox" name="vendor_payment" value="1" id="vendor_payment">
                              <label class="form-check-label cursor-pointer" for="vendor_payment">100% Secure Payments</label>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-check mt-3">
                              <input class="form-check-input" type="checkbox" name="vendor_return" value="1" id="vendor_return">
                              <label class="form-check-label cursor-pointer" for="vendor_return">Easy & Hassle-Free Returns</label>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-check mt-3">
                              <input class="form-check-input" type="checkbox" name="vendor_delivery" value="1" id="vendor_delivery">
                              <label class="form-check-label cursor-pointer" for="vendor_delivery">Free Delivery</label>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>


                </div>
              </div>

              {{-- ================= VARIANTS ================= --}}
              <div>
                <h5 class="mb-3">Color, Size & Stock</h5>

                <div class="variantWrapper">
                  <div class="variant-block card mb-3 text-start">
                    <div class="card-body">

                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Variant #1</h6>
                        <button type="button" class="btn btn-sm btn-danger removeVariant" style="display:none;">✕</button>
                      </div>
                      <input type="hidden" name="variant[]" value="1" class="form-control" multiple>

                      {{-- SKU / Color / Stock --}}
                      <div class="row mb-3">
                        <div class="col-md-4">
                          <label>SKU</label>
                          <input type="text" name="sku[]" class="form-control skugen" maxlength="255" required>
                        </div>

                        <div class="col-md-4">
                          <label>Color</label>
                          <input type="text" name="color[]" class="form-control" maxlength="100" placeholder="e.g. Red">
                        </div>

                        <div class="col-md-4">
                          <label>Stock</label>
                          <input type="text" name="stock[]" class="form-control" placeholder="Quantity" max="1000000">
                        </div>
                      </div>


                      {{-- Price / Discount --}}
                      <div class="row mb-3">
                        <div class="col-md-4">
                          <label>Price</label>
                          <input type="text" name="price[]" class="form-control ve-price" step="any">
                        </div>

                        <div class="col-md-4">
                          <label>Discount Type</label>
                          <select name="discount_type[]" class="form-control ve-discount-type">
                            <option value="">None</option>
                            <option value="off" {{ old('discount_type.0') == 'off' ? 'selected' : '' }}>Flat</option>
                            <option value="%" {{ old('discount_type.0') == '%' ? 'selected' : '' }}>Percentage (%)</option>
                          </select>
                        </div>

                        <div class="col-md-4">
                          <label>Discount Value</label>
                          <input type="text" name="discount_value[]" class="form-control ve-discount-value" step="any" max="100" disabled>
                        </div>
                      </div>



                      {{-- Size --}}
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label>Size Category</label>
                          <select name="size_category_id[]" class="form-control SelectSizeCategory">
                            <option value="">Select Size Category</option>
                            @foreach($sizecategory as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label>Select Sizes</label>
                          <select name="size[0][]" class="form-control js-size-select" multiple>
                            <option value="">Select category first</option>
                          </select>
                        </div>

                        <div class="col-md-12  mb-3">
                          <label>Material</label>
                          <input type="text" name="material[]" class="form-control" maxlength="100" placeholder="e.g. Cotton">
                        </div>

                        <div class="col-md-12">
                          <label>Variant Images</label>
                          <input type="file" name="product_image[0][]" class="form-control" multiple>
                        </div>
                      </div>

                    </div>
                  </div>

                </div>



                <div class="d-flex justify-content-end gap-2">
               <button type="button" class="btn btn-outline-primary mt-2 addVariant">
                  + Add Another Variant
                </button>
                  <button type="submit" class="btn btn-primary">Save Product</button>
                </div>

              </div>

            </form>

          </div>
        </div>


      </div> <!-- end col -->


    </div> <!-- end row -->
  </div>
  <!-- End Container Fluid -->


@endsection

@include('backend.admin.product.partials.discount-validation')




