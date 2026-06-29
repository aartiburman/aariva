@extends('backend.layouts.app')
@section('content')

<div class="page-content">
  <div class="container-fluid">
    <div class="row align-items-center mb-3">
      <div class="col">
        <h4 class="mb-0">Update Product</h4>
      </div>
      <div class="col-auto d-flex gap-2">
        <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
          <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
          Back to List
        </a>
         <a href="{{ route('edit.variant',$product_data->id) }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:tuning-square-linear" class="fs-18"></iconify-icon> Edit Variants
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

                {{-- ================= STEP 1: DEFAULT CONFIGURATION ================= --}}
                <div class="card border-0 shadow-sm mb-4">
                  <div class="card-header bg-light-subtle py-2">
                    <h6 class="card-title mb-0 text-uppercase fw-bold fs-13 text-primary">Step 1: Default Configuration</h6>
                  </div>
                  <div class="card-body p-3">
                    <div class="row g-3">
                      <div class="col-md-4">
                        <label class="form-label fw-semibold fs-13 required">Category</label>
                        <select name="category_id" class="form-select category_id dynamic-create" data-allow-new="true" data-type="category" data-placeholder="Create or select Category" required>
                          <option value="">Create or select Category</option>
                          @foreach($categories as $cat)
                          <option value="{{ $cat->id }}" {{ $product_data->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label fw-semibold fs-13">Sub Category</label>
                        <select name="subcategory_id" class="form-select subcategory_id dynamic-create" data-allow-new="true" data-type="subcategory" data-placeholder="Create or select Subcategory" data-selected="{{ old('subcategory_id', $product_data->subcategory_id) }}">
                          <option value="">Create or select Subcategory</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label fw-semibold fs-13">Child Category</label>
                        <select name="child_category_id" class="form-select child_category_id dynamic-create" data-allow-new="true" data-type="childcategory" data-placeholder="Create or select Child category" data-selected="{{ old('child_category_id', $product_data->child_category_id) }}">
                          <option value="">Create or select Child category</option>
                        </select>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold fs-13">Brand</label>
                        <select name="brand_id" class="form-select categoryBrand brandSelect" data-allow-new="true" data-type="brand" data-placeholder="Create or select Brand" data-selected="{{ old('brand_id', $product_data->brand_id) }}">
                          <option value="">Create or select Brand</option>
                          @foreach($brand as $b)
                          <option value="{{ $b->id }}" {{ old('brand_id', $product_data->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- ================= BASIC INFORMATION ================= --}}
                <div class="card border-0 shadow-sm mb-4">
                  <div class="card-header bg-light-subtle py-2">
                    <h6 class="card-title mb-0 text-uppercase fw-bold fs-13 text-primary">Step 2: Basic Information</h6>
                  </div>
                  <div class="card-body p-3">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold fs-13 required">Product Name</label>
                        <div class="input-group">
                          <input type="text" name="name" class="form-control product-name" placeholder="Enter Product Name" value="{{ old('name', $product_data->name) }}" maxlength="255" required>
                          <button type="button" class="btn btn-outline-secondary" id="generateTitleBtn" title="Auto-generate title from Brand + Category + Main Feature + Size">
                            <iconify-icon icon="solar:magic-stick-3-linear" class="fs-16"></iconify-icon>
                          </button>
                        </div>
                        <small class="text-muted">Format: Brand + Product Type + Main Feature + Size/Variant</small>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold fs-13 required">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $product_data->slug) }}" maxlength="255" readonly>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold fs-13">Main Feature</label>
                        <input type="text" name="main_feature" id="main_feature" class="form-control" maxlength="255" placeholder="e.g. Wireless, Waterproof, Organic" value="{{ old('main_feature') }}">
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold fs-13 required">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2" placeholder="Brief description..." maxlength="1000" required>{{ old('short_description', $product_data->short_description) }}</textarea>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold fs-13 required">Description</label>
                        <div class="d-flex justify-content-end mb-1">
                          <button type="button" class="btn btn-sm btn-outline-secondary" id="generateDescBtn">
                            <iconify-icon icon="solar:magic-stick-3-linear" class="fs-14"></iconify-icon> Generate Description Template
                          </button>
                        </div>
                        <textarea name="description" class="form-control" rows="4" placeholder="Detailed description..." maxlength="5000" required>{{ old('description', $product_data->description) }}</textarea>
                      </div>
                    </div>
                  </div>
                </div>
                {{-- ================= MARKETING & PLACEMENT ================= --}}
                <div class="card border-0 shadow-sm mb-4">
                  <div class="card-header bg-light-subtle py-2">
                    <h6 class="card-title mb-0 text-uppercase fw-bold fs-13 text-primary">Step 3: Marketing & Placement</h6>
                  </div>
                  <div class="card-body p-3">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold fs-13">Offers</label>
                        <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light-subtle">
                          @foreach ($offers as $value)
                          <div class="form-check me-2">
                            <input class="form-check-input" type="checkbox" name="offers[]" @if(in_array($value->id, is_string($product_data->offer_id) ? json_decode($product_data->offer_id, true) : (is_array($product_data->offer_id) ? $product_data->offer_id : [$product_data->offer_id]) ?? [])) checked @endif value="{{ $value->id }}" id="offer_{{ $value->id }}">
                            <label class="form-check-label cursor-pointer fs-12" for="offer_{{ $value->id }}">{{ $value->code }}</label>
                          </div>
                          @endforeach
                        </div>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold fs-13">Product Placement</label>
                        @php
                          $prod_in = [];
                          $decoded = json_decode($product_data->product_in, true);
                          $prod_in = is_array($decoded) ? $decoded : [];
                        @endphp
                        <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light-subtle">
                          <div class="form-check me-2">
                            <input class="form-check-input" type="checkbox" name="product_in[]" value="1" id="in_1" {{ in_array(1, $prod_in) ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer fs-12" for="in_1">Best Seller</label>
                          </div>
                          <div class="form-check me-2">
                            <input class="form-check-input" type="checkbox" name="product_in[]" value="2" id="in_2" {{ in_array(2, $prod_in) ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer fs-12" for="in_2">Trending</label>
                          </div>
                          <div class="form-check me-2">
                            <input class="form-check-input" type="checkbox" name="product_in[]" value="3" id="in_3" {{ in_array(3, $prod_in) ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer fs-12" for="in_3">Popular</label>
                          </div>
                          <div class="form-check me-2">
                            <input class="form-check-input" type="checkbox" name="product_in[]" value="4" id="in_4" {{ in_array(4, $prod_in) ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer fs-12" for="in_4">Deal</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- ================= ADDITIONAL SETTINGS ================= --}}
                <div class="card border-0 shadow-sm mb-4">
                  <div class="card-header bg-light-subtle py-2">
                    <h6 class="card-title mb-0 text-uppercase fw-bold fs-13 text-primary">Step 4: Additional Settings</h6>
                  </div>
                  <div class="card-body p-3">
                    <div class="row g-3">
                      <div class="col-md-12">
                        <div class="form-check form-switch mb-3">
                          <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ $product_data->is_featured == 1 ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer fw-semibold fs-13" for="is_featured">Mark as Featured Product</label>
                        </div>
                      </div>

                      @if(Auth::user()->role == 1)
                      <div class="col-md-3">
                        <label class="form-label fw-semibold fs-13">Status</label>
                        <select name="status" class="form-select form-select-sm">
                          <option value="1" {{ $product_data->status == 1 ? 'selected' : '' }}>Active</option>
                          <option value="0" {{ $product_data->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                      </div>
                      @else
                        <input type="hidden" name="status" value="{{ $product_data->status }}">
                      @endif

                      <div class="col-md-3">
                        <label class="form-label fw-semibold fs-13">Warranty</label>
                        <select name="vendor_warranty" class="form-select form-select-sm" id="vendor_warranty">
                          <option value="" {{ $product_data->vendor_warranty == '' ? 'selected' : '' }}>No Warranty</option>
                          @foreach(['6 Months', '1 Year', '2 Years', '3 Years', '5 Years', 'Lifetime'] as $w)
                          <option value="{{ $w }}" {{ $product_data->vendor_warranty == $w ? 'selected' : '' }}>{{ $w }}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="col-md-2">
                        <div class="form-check mt-md-4">
                          <input class="form-check-input" type="checkbox" name="vendor_payment" checked="checked" value="1" id="vendor_payment" {{ $product_data->vendor_payment == 1 ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer fs-12" for="vendor_payment">Secure Payments</label>
                        </div>
                      </div>

                      <div class="col-md-2">
                        <div class="form-check mt-md-4">
                          <input class="form-check-input" type="checkbox" name="vendor_return" checked="checked" value="1" id="vendor_return" {{ $product_data->vendor_return == 1 ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer fs-12" for="vendor_return">Easy Returns</label>
                        </div>
                      </div>

                      <div class="col-md-2">
                        <div class="form-check mt-md-4">
                          <input class="form-check-input" type="checkbox" name="vendor_delivery" checked="checked" value="1" id="vendor_delivery" {{ $product_data->vendor_delivery == 1 ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer fs-12" for="vendor_delivery">Free Delivery</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button type="submit" class="btn btn-primary px-4">
                    <iconify-icon icon="solar:diskette-linear" class="align-middle me-1"></iconify-icon> Update Product
                  </button>
                </div>

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

@push('scripts')
<script>
  function generateTitle() {
    let brand = $('.brandSelect option:selected').text();
    if (brand === 'Create or select Brand' || brand === 'Select Brand' || !brand) brand = '';

    let category = $('.category_id option:selected').text();
    if (category === 'Create or select Category' || category === 'Select Category' || !category) category = '';

    let subcategory = $('.subcategory_id option:selected').text();
    if (subcategory === 'Create or select Subcategory' || subcategory === 'Select Category First' || !subcategory) subcategory = '';

    let childcategory = $('.child_category_id option:selected').text();
    if (childcategory === 'Create or select Child category' || childcategory === 'Select Sub Category First' || !childcategory) childcategory = '';

    let mainFeature = $('#main_feature').val() || '';

    let sizeVariant = '';
    let variantSelect = $('select[name="product_variant"]');
    if (variantSelect.length) {
      sizeVariant = variantSelect.find('option:selected').text();
      if (sizeVariant === 'Select product Variant' || !sizeVariant) sizeVariant = '';
    }

    let productType = childcategory || subcategory || category;

    let parts = [brand, productType, mainFeature, sizeVariant].filter(p => p.trim() !== '');

    let title = parts.join(' - ');

    $('input[name="name"]').val(title);

    let slug = title.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]+/g, '');
    $('input[name="slug"]').val(slug);
  }

  function generateDescription() {
    let color = $('input[name="color[]"]').val() || '';
    let material = $('input[name="material[]"]').val() || '';
    let sizeText = '';
    let sizeSelect = $('select[name="size[]"]');
    if (sizeSelect.length) {
      let selectedSizes = sizeSelect.val();
      if (selectedSizes && selectedSizes.length > 0) {
        sizeText = selectedSizes.join(', ');
      }
    }

    let desc = 'Description\n\nInclude:\n';
    desc += '☐ Material' + (material ? ': ' + material : '') + '\n';
    desc += '☐ Color' + (color ? ': ' + color : '') + '\n';
    desc += '☐ Size' + (sizeText ? ': ' + sizeText : '') + '\n';
    desc += '☐ Features\n';
    desc += '☐ Package details\n';
    desc += '☐ Care instructions';

    $('textarea[name="description"]').val(desc);
  }

  $(document).ready(function() {
    // 1. Generate Title
    $('#generateTitleBtn').on('click', generateTitle);

    // 2. Generate Description
    $('#generateDescBtn').on('click', generateDescription);

    // 3. Slug Generator
    $('.product-name').on('input', function() {
      let name = $(this).val();
      let slug = name.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]+/g, '');
      $('input[name="slug"]').val(slug);
    });

    // 4. Form Validation
    if (jQuery().validate) {
      $("form").validate({
        rules: {
          name: "required",
          category_id: "required",
          short_description: "required",
          description: "required"
        },
        messages: {
          name: "Please enter product name",
          category_id: "Please select a category",
          short_description: "Please enter a short description",
          description: "Please enter product description"
        },
        errorElement: "div",
        errorPlacement: function(error, element) {
          error.addClass("invalid-feedback d-block text-danger mt-1");
          if (element.hasClass("select2-hidden-accessible") || element.hasClass("category_id")) {
            element.parent().append(error);
          } else {
            element.after(error);
          }
        },
        highlight: function(element) {
          $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function(element) {
          $(element).removeClass("is-invalid").addClass("is-valid");
        }
      });
    }
  });
</script>
@endpush






