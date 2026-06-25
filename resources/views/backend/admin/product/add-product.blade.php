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
                      <label class="required">Product Name</label>
                      <input type="text" name="name" class="form-control product-name @error('name') is-invalid @enderror" maxlength="255" value="{{ old('name') }}" required>
                      @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="required">Slug</label>
                      <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" maxlength="255" value="{{ old('slug') }}" readonly>
                      @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label class="required">Category</label>
                      <select name="category_id" class="form-select category_id dynamic-create @error('category_id') is-invalid @enderror" data-allow-new="true" data-type="category" data-placeholder="Create or select Category" required>
                        <option value="">Select Category</option>
                        @foreach($categories_data as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                      </select>
                      @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                      <label>Sub Category</label>
                      <select name="subcategory_id" class="form-select subcategory_id dynamic-create @error('subcategory_id') is-invalid @enderror" data-allow-new="true" data-type="subcategory" data-placeholder="Create or select Subcategory">
                        <option value="">Select Category First</option>
                      </select>
                      @error('subcategory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                      <label>Child Category</label>
                      <select name="child_category_id" class="form-select child_category_id dynamic-create @error('child_category_id') is-invalid @enderror" data-allow-new="true" data-type="childcategory" data-placeholder="Create or select Child category">
                        <option value="">Select Sub Category First</option>
                      </select>
                      @error('child_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                  </div>

                  <div class="mb-3">
                    <label>Brand</label>
                    <select name="brand_id" class="form-select categoryBrand brandSelect @error('brand_id') is-invalid @enderror" data-allow-new="true">
                      <option value="">Select Brand</option>
                    </select>
                    @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>

                  <div class="mb-3">
                    <label class="required">Short Description</label>
                    <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" maxlength="1000" required>{{ old('short_description') }}</textarea>
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>

                  <div class="mb-3">
                    <label class="required">Description</label>
                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" maxlength="5000" required>{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>

                

                  <div class="row">
                    <div class="col-md-12 mb-3">
                      <label class="form-label fw-bold">Offers</label>
                      <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light-subtle">
                        @foreach ($offers as $value)
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="offers[]" value="{{ $value->id }}" id="offer_{{ $value->id }}" {{ is_array(old('offers')) && in_array($value->id, old('offers')) ? 'checked' : '' }}>
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
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="1" id="in_1" {{ is_array(old('product_in')) && in_array(1, old('product_in')) ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer" for="in_1">Best Seller</label>
                        </div>
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="2" id="in_2" {{ is_array(old('product_in')) && in_array(2, old('product_in')) ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer" for="in_2">Trending</label>
                        </div>
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="3" id="in_3" {{ is_array(old('product_in')) && in_array(3, old('product_in')) ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer" for="in_3">Popular</label>
                        </div>
                        <div class="form-check me-2">
                          <input class="form-check-input" type="checkbox" name="product_in[]" value="4" id="in_4" {{ is_array(old('product_in')) && in_array(4, old('product_in')) ? 'checked' : '' }}>
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
                          <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer" for="is_featured">Mark as Featured Product</label>
                        </div>
                        <hr>
                        <div class="row align-items-center">
                          <div class="col-md-3">
                            <label class="form-label">Warranty</label>
                            <select name="vendor_warranty" class="form-select" id="vendor_warranty">
                              <option value="">No Warranty</option>
                              @foreach(['6 Months', '1 Year', '2 Years', '3 Years', '5 Years', 'Lifetime'] as $w)
                              <option value="{{ $w }}" {{ old('vendor_warranty') == $w ? 'selected' : '' }}>{{ $w }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-3">
                            <div class="form-check mt-3">
                              <input class="form-check-input" type="checkbox" name="vendor_payment" checked="checked" value="1" id="vendor_payment" {{ old('vendor_payment') ? 'checked' : '' }}>
                              <label class="form-check-label cursor-pointer" for="vendor_payment">100% Secure Payments</label>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-check mt-3">
                              <input class="form-check-input" type="checkbox" name="vendor_return" checked="checked" value="1" id="vendor_return" {{ old('vendor_return') ? 'checked' : '' }}>
                              <label class="form-check-label cursor-pointer" for="vendor_return">Easy & Hassle-Free Returns</label>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-check mt-3">
                              <input class="form-check-input" type="checkbox" name="vendor_delivery" checked="checked" value="1" id="vendor_delivery" {{ old('vendor_delivery') ? 'checked' : '' }}>
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
                          <label class="required">SKU</label>
                          <input type="text" name="sku[]" class="form-control skugen @error('sku.*') is-invalid @enderror" maxlength="255" value="{{ old('sku.0') }}" required>
                          @error('sku.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                          <label>Color</label>
                          <input type="text" name="color[]" class="form-control @error('color.*') is-invalid @enderror" maxlength="100" placeholder="e.g. Red" value="{{ old('color.0') }}">
                          @error('color.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                          <label class="required">Stock</label>
                          <input type="text" name="stock[]" class="form-control @error('stock.*') is-invalid @enderror" placeholder="Quantity" max="1000000" value="{{ old('stock.0') }}" required>
                          @error('stock.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                      </div>


                      {{-- Price / Discount --}}
                      <div class="row mb-3">
                        <div class="col-md-4">
                          <label class="required">Price</label>
                          <input type="text" name="price[]" class="form-control ve-price @error('price.*') is-invalid @enderror" step="any" value="{{ old('price.0') }}" required>
                          @error('price.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                          <label>Discount Type</label>
                          <select name="discount_type[]" class="form-control ve-discount-type @error('discount_type.*') is-invalid @enderror">
                            <option value="">None</option>
                            <option value="off" {{ old('discount_type.0') == 'off' ? 'selected' : '' }}>Flat</option>
                            <option value="%" {{ old('discount_type.0') == '%' ? 'selected' : '' }}>Percentage (%)</option>
                          </select>
                          @error('discount_type.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                          <label>Discount Value</label>
                          <input type="text" name="discount_value[]" class="form-control ve-discount-value @error('discount_value.*') is-invalid @enderror" step="any" value="{{ old('discount_value.0') }}" {{ old('discount_type.0') ? '' : 'disabled' }}>
                          @error('discount_value.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                      </div>



                      {{-- Size --}}
                      <div class="row">
                        <div class="col-md-4 mb-3">
                          <label>Size Category</label>
                          <select name="size_category_id[]" class="form-control SelectSizeCategory @error('size_category_id.*') is-invalid @enderror">
                            <option value="">Select Size Category</option>
                            @foreach($sizecategory as $cat)
                            <option value="{{ $cat->id }}" {{ old('size_category_id.0') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                          </select>
                          @error('size_category_id.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                          <label>Product Variant </label>
                          <select name="product_variant" class="form-control SelectSizeCategory @error('product_variant') is-invalid @enderror">
                            <option value="">Select product Variant </option>
                            
                            <option value="1">Size</option>
                            <option value="2">Quantity</option>
                            <option value="3">Age Group</option>
                            <option value="4">Pack of</option>
                            <option value="5">Capacity</option>

                          </select>
                          @error('product_.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                          <label>Select Sizes</label>
                          <select name="size[0][]" class="form-control js-size-select @error('size.0') is-invalid @enderror" multiple>
                            <option value="">Select category first</option>
                          </select>
                          @error('size.0') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12  mb-3">
                          <label>Material</label>
                          <input type="text" name="material[]" class="form-control @error('material.*') is-invalid @enderror" maxlength="100" placeholder="e.g. Cotton" value="{{ old('material.0') }}">
                          @error('material.*')<div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12">
                          <label class="required">Variant Images</label>
                          <input type="file" name="product_image[0][]" class="form-control" multiple required>
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

  @push('scripts')
  <script>
    $(document).ready(function() {
      // Custom validation for percentage discount
      $.validator.addMethod("maxPercentage", function(value, element) {
        let type = $(element).closest('.row').find('.ve-discount-type').val();
        if (type === '%') {
          return parseFloat(value) <= 100;
        }
        return true;
      }, "Percentage discount cannot exceed 100%");

      // Initialize jQuery Validation
      if (jQuery().validate) {
        $("#productForm").validate({
          rules: {
            name: "required",
            category_id: "required",
            short_description: "required",
            description: "required",
            "sku[]": "required",
            "stock[]": {
              required: true,
              number: true
            },
            "price[]": {
              required: true,
              number: true
            },
            "discount_value[]": {
              number: true,
              maxPercentage: true
            },
            "product_image[0][]": "required"
          },
          messages: {
            name: "Please enter product name",
            category_id: "Please select a category",
            short_description: "Please enter a short description",
            description: "Please enter product description",
            "sku[]": "SKU is required",
            "stock[]": {
              required: "Stock is required",
              number: "Please enter a valid number"
            },
            "price[]": {
              required: "Price is required",
              number: "Please enter a valid number"
            },
            "discount_value[]": {
              number: "Please enter a valid number"
            },
            "product_image[0][]": "Please upload at least one image"
          },
          errorElement: "div",
          errorPlacement: function(error, element) {
            error.addClass("invalid-feedback d-block text-danger mt-1");
            if (element.hasClass("select2-hidden-accessible") || element.hasClass("category_id")) {
              element.parent().append(error);
            } else if (element.parent().hasClass('input-group')) {
              element.parent().after(error);
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

      // 1. AJAX Form Submission with Validation
      $('#productForm').on('submit', function(e) {
        e.preventDefault();
        
        // Check jQuery validation first
        if (jQuery().validate && !$(this).valid()) {
            return false;
        }

        const form = $(this);
        const url = form.attr('action');
        const formData = new FormData(this);

        // Clear previous errors and styling
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();
        form.find('.border-danger').removeClass('border-danger');

        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Saving...');

        $.ajax({
          url: url,
          method: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            if (response.status) {
              toastr.success(response.message || 'Product saved successfully!');
              window.location.href = "{{ route('product.list') }}";
            } else {
              toastr.error(response.message || 'Something went wrong.');
              submitBtn.prop('disabled', false).text(originalText);
            }
          },
          error: function(xhr) {
            submitBtn.prop('disabled', false).text(originalText);

            if (xhr.status === 422) {
              const errors = xhr.responseJSON.errors;

              Object.keys(errors).forEach(key => {
                // Convert Laravel's "sku.0" to "sku[]" or find by specific index
                // We create a selector that works for both "name" and "name[]"
                let fieldName = key;
                if (key.includes('.')) {
                  let parts = key.split('.');
                  // Transforms 'sku.0' into 'sku[]' to find the specific input in the list
                  fieldName = parts[0] + '[]';
                }

                // Find the specific input. 
                // If it's an array field (like sku.0), we target the n-th occurrence
                let input;
                if (key.includes('.')) {
                  let index = key.split('.')[1];
                  input = form.find(`[name="${fieldName}"]`).eq(index);

                  // Special case for nested arrays like size[0][]
                  if (input.length === 0) {
                    input = form.find(`[name^="${key.split('.')[0]}[${index}]"]`);
                  }
                } else {
                  input = form.find(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                }

                // Add the Red Border
                input.addClass('is-invalid');

                // Create the Error Message Element
                let errorMsg = `<div class="invalid-feedback d-block text-danger mt-1">${errors[key][0]}</div>`;

                // Smart Placement
                if (input.closest('.choices').length) {
                  // For Choices.js / Select2
                  input.closest('.choices').after(errorMsg);
                  input.closest('.choices').find('.choices__inner').addClass('border-danger');
                } else if (input.parent().hasClass('input-group')) {
                  input.parent().after(errorMsg);
                } else if (input.is(':checkbox') || input.is(':radio')) {
                  input.closest('.form-check').append(errorMsg);
                } else {
                  input.after(errorMsg);
                }
              });

              toastr.error('Please fix the errors highlighted in red.');
            } else {
              toastr.error('An error occurred. Please try again.');
            }
          }
        });
      });

    });
  </script>
  @endpush
  @endsection

  @include('backend.admin.product.partials.discount-validation')