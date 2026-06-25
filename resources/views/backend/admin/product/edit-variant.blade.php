 @extends('backend.layouts.app')
 @section('content')

 <div class="page-content">
      <div class="container-fluid">


           <div class="row">
                <div class="col-xl-12">
                     <div class="card">
                          <div class="card-header d-flex justify-content-between align-items-center">
                               <h4 class="card-title">Edit Variants</h4>
                               <div class="d-flex gap-2">
                                    <a href="{{ route('product.list') }}" class="btn btn-sm btn-secondary">
                                         <iconify-icon icon="solar:arrow-left-linear" class="me-1"></iconify-icon> Back
                                    </a>
                                    <a href="{{ route('edit.product',$variant->first()->product_id ?? '') }}" class="btn btn-sm btn-primary">
                                         <iconify-icon icon="solar:pen-linear" class="align-middle me-1"></iconify-icon> Edit Product
                                    </a>
                               </div>
                          </div>
                          <div class="card-body">
                               <div class="">
                                    <form id="editVariantsForm"
                                         action="{{ route('update.variant') }}"
                                         method="POST"
                                         enctype="multipart/form-data">

                                         @csrf

                                         <input type="hidden" name="product_id" value="{{ $variant->first()->product_id ?? '' }}">

                                         <div class="variantWrapper">

                                              @foreach($variant as $index => $value)
                                              <div class="variant-block border rounded p-3 mb-4 bg-light-subtle position-relative text-start">

                                                   <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fs-14 fw-bold">Variant #{{ $index + 1 }}</h6>
                                                        <button type="button" class="btn btn-sm btn-soft-danger removeVariant">
                                                             <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                                                        </button>
                                                   </div>

                                                   {{-- EXISTING VARIANT ID --}}
                                                   <input type="hidden" class="variant_id_input" name="variant_id[{{ $index }}]" value="{{ $value->id }}">

                                                   <div class="row g-3">

                                                        {{-- SKU --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">SKU</label>
                                                             <input type="text" name="sku[{{ $index }}]" class="form-control form-control-sm skugen" value="{{ $value->sku }}" maxlength="255" required>
                                                        </div>

                                                        {{-- COLOR --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Color</label>
                                                             <input type="text" name="color[{{ $index }}]" class="form-control form-control-sm" value="{{ $value->color }}" maxlength="100" required>
                                                        </div>

                                                        {{-- STOCK --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Stock</label>
                                                             <input type="text" name="stock[{{ $index }}]" class="form-control form-control-sm" value="{{ $value->stock }}" max="1000000" required>
                                                        </div>

                                                        {{-- PRICE --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Price</label>
                                                             <input type="text" name="price[{{ $index }}]" class="form-control form-control-sm ve-price" value="{{ $value->price }}" step="any">
                                                        </div>

                                                        {{-- DISCOUNT TYPE --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Discount Type</label>
                                                             <select name="discount_type[{{ $index }}]" class="form-select form-select-sm ve-discount-type">
                                                                  <option value="">None</option>
                                                                  <option value="off" {{ in_array($value->discount_type, ['off', 'flat']) ? 'selected' : '' }}>Flat</option>
                                                                  <option value="%" {{ in_array($value->discount_type, ['%', 'percent']) ? 'selected' : '' }}>Percentage (%)</option>
                                                             </select>
                                                        </div>

                                                        {{-- DISCOUNT VALUE --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Discount Value</label>
                                                             <input type="text" name="discount_value[{{ $index }}]" class="form-control form-control-sm ve-discount-value" value="{{ $value->discount_value }}" step="any">
                                                        </div>

                                                        {{-- PRODUCT VARIANT --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Variant Type</label>
                                                             <select name="product_variant[{{ $index }}]" class="form-select form-select-sm dynamic-create @error('product_variant.*') is-invalid @enderror" data-allow-new="true" data-type="product_variant" data-placeholder="Create or select Type">
                                                                  <option value="">Select Type</option>
                                                                  @foreach($product_variant_labels as $label)
                                                                  <option value="{{ $label->id }}" {{ $value->product_variant == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                                                                  @endforeach
                                                             </select>
                                                        </div>

                                                        {{-- SIZE CATEGORY --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Size Category</label>
                                                             <select name="size_category_id[{{ $index }}]" class="form-select form-select-sm dynamic-create UpdateSizeCategory @error('size_category_id.*') is-invalid @enderror" data-allow-new="true" data-type="size_category" data-placeholder="Create or select Category">
                                                                  <option value="">Select Category</option>
                                                                  @foreach($sizecategory as $cat)
                                                                  <option value="{{ $cat->id }}" {{ $value->size_cat_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                                  @endforeach
                                                             </select>
                                                        </div>

                                                        {{-- SIZES --}}
                                                        <div class="col-md-4">
                                                             <label class="form-label fw-semibold fs-12">Select Sizes</label>
                                                             
                                                             <select id="sizeSelect" name="size[{{ $index }}][]" class="form-select form-select-sm js-size-select dynamic-create" data-allow-new="true" data-type="size" data-placeholder="Create or select sizes" multiple>
                                                                  @foreach($value->sizes_list as $size)
                                                                  <option value="{{ $size->id }}" selected>{{ $size->name }}</option>
                                                                  @endforeach
                                                             </select>
                                                        </div>

                                                        {{-- MATERIAL --}}
                                                        <div class="col-md-6">
                                                             <label class="form-label fw-semibold fs-12">Material</label>
                                                             <input type="text" name="material[{{ $index }}]" class="form-control form-control-sm" value="{{ $value->material }}" maxlength="100">
                                                        </div>

                                                        {{-- IMAGES --}}
                                                        <div class="col-md-6">
                                                             <label class="form-label fw-semibold fs-12">Variant Images</label>
                                                             <input type="file" name="product_image[{{ $index }}][]" class="form-control form-control-sm" multiple>
                                                        </div>

                                                        <div class="col-12">
                                                             <div class="d-flex flex-wrap mt-1 sortable-images" data-variant-id="{{ $value->id }}">
                                                                  @foreach(json_decode($value->image, true) ?? [] as $img)
                                                                  <div class="image-box position-relative me-2 mb-2" data-image="{{ $img }}" style="cursor: move;">
                                                                       <img src="{{ asset('uploads/products/'.$img) }}" width="80" height="80" class="rounded border object-fit-cover">
                                                                       <button type="button" class="btn btn-sm btn-danger delete-image-btn p-1 lh-1" data-variant-id="{{ $value->id }}" data-image="{{ $img }}" style="position:absolute;top:-5px;right:-5px;">
                                                                            <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                                                                       </button>
                                                                  </div>
                                                                  @endforeach
                                                             </div>
                                                             <input type="hidden" class="image-order-input" name="image_order[{{ $index }}]">
                                                        </div>

                                                   </div>
                                              </div>
                                              @endforeach

                                         </div>

                                         {{-- ACTION BUTTONS --}}
                                         <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                              <button type="button" class="btn btn-primary addVariant px-4">
                                                   <iconify-icon icon="solar:plus-circle-linear" class="align-middle me-1"></iconify-icon> Add Another Variant
                                              </button>

                                              <button type="submit" class="btn btn-primary px-4">
                                                   <iconify-icon icon="solar:diskette-linear" class="align-middle me-1"></iconify-icon> Save Changes
                                              </button>
                                         </div>


                               </div>

                               </form>


                          </div>
                     </div>



                </div> <!-- end row -->
           </div>
           <!-- End Container Fluid -->



           @endsection

           @push('chart-scripts')
           <script>
                // Global function to fetch sizes via AJAX
                window.fetchSizes = function(categoryId, sizeSelectElement) {
                     if (!sizeSelectElement) return;

                     if (!categoryId) {
                          if (window.choicesMap && window.choicesMap.has(sizeSelectElement)) {
                               let choicesInstance = window.choicesMap.get(sizeSelectElement);
                               choicesInstance.setChoices([{
                                    value: '',
                                    label: 'Select category first',
                                    selected: true,
                                    disabled: true
                               }], 'value', 'label', true);
                          } else {
                               sizeSelectElement.innerHTML = '<option value="">Select category first</option>';
                          }
                          return;
                     }

                     $.ajax({
                          global: false,
                          url: "{{ url('get-sizes') }}/" + categoryId,
                          type: 'GET',
                          dataType: 'json',
                          success: function(res) {
                               let choices = [];
                               if (res && res.length > 0) {
                                    res.forEach(size => {
                                         choices.push({
                                              value: size.id,
                                              label: size.name
                                         });
                                    });
                               } else {
                                    choices.push({
                                         value: '',
                                         label: 'No sizes found',
                                         disabled: true
                                    });
                               }

                               if (window.choicesMap && window.choicesMap.has(sizeSelectElement)) {
                                    let choicesInstance = window.choicesMap.get(sizeSelectElement);
                                    choicesInstance.setChoices(choices, 'value', 'label', true);
                               } else {
                                    let html = '';
                                    choices.forEach(c => {
                                         html += `<option value="${c.value}">${c.label}</option>`;
                                    });
                                    sizeSelectElement.innerHTML = html;
                               }
                          },
                          error: function() {
                               toastr.error('Failed to load sizes.');
                          }
                     });
                }
                $(document).ready(function() {

                     /* ===============================
                        ADD VARIANT (EDIT PAGE)
                     =============================== */
                     var variantIndex = {
                          
                               count($variant)
                          
                     };

                     $('#editAddVariantBtn').on('click', function(e) {
                          e.preventDefault();

                          let $firstBlock = $('.variant-block:first');
                          if (!$firstBlock.length) return;

                          let $clone = $firstBlock.clone();

                          // 2. CLEANUP: Choices.js creates a UI wrapper. We must remove it to get the raw select back.
                          $clone.find('.choices').each(function() {
                               let $originalSelect = $(this).find('select').clone();
                               $originalSelect.removeClass('choices__input choices-initialized').removeAttr('data-choice').show();
                               $(this).replaceWith($originalSelect);
                          });

                          // 3. RESET VALUES: Clear inputs so the clone is empty
                          $clone.find('input:not([type=hidden])').val('');
                          $clone.find('textarea').val('');
                          $clone.find('select').val('');
                          $clone.find('.image-box').remove();
                          $clone.find('.sortable-images').empty();
                          $clone.find('.image-order-input').val('');

                          // New variant → clear ID
                          $clone.find('.variant_id_input').val('');

                          // 4. INDEXING: Update the array names for the backend
                          $clone.find('input[type="file"]').attr('name', `product_image[${variantIndex}][]`);

                          /* -----------------------------
                             SKU AUTO-INCREMENT
                          ----------------------------- */
                          let prevSku = $('.variant-block:last').find('.skugen').val();
                          if (prevSku) {
                               let baseSku = prevSku.split('-')[0];
                               let match = prevSku.match(/-(\d+)$/);
                               let next = match ? parseInt(match[1]) + 1 : 1;
                               $clone.find('.skugen').val(baseSku + '-' + String(next).padStart(2, '0'));
                          }

                          let $sizeSelect = $clone.find('.js-size-select');
                          $sizeSelect.attr('name', `size[${variantIndex}][]`)
                               .html('<option value="">Select category first</option>');
                          
                          if (window.initChoices) {
                               window.initChoices($sizeSelect[0]);
                          }

                          // 5. UI: Show the remove button
                          $clone.find('.removeVariant').show();
                          $clone.find('h6').text('Variant');

                          // 6. APPEND
                          $('.variantWrapper').append($clone);

                          // 7. RE-INIT & RE-INDEX
                          if (typeof window.reIndexVariants === 'function') {
                               window.reIndexVariants();
                          }

                          variantIndex++;
                     });

                     /* ===============================
                        REMOVE VARIANT (EDIT PAGE)
                     =============================== */
                     $(document).on('click', '.removeVariant', function() {
                          if ($('.variant-block').length > 1) {
                               const selectEl = $(this).closest('.variant-block').find('.js-size-select')[0];
                               if (selectEl && typeof window.destroyChoices === 'function') {
                                    window.destroyChoices(selectEl);
                               }
                               $(this).closest('.variant-block').remove();
                               if (typeof window.reIndexVariants === 'function') {
                                    window.reIndexVariants();
                               }
                          } else {
                               toastr.warning('At least one variant is required.');
                          }
                     });


                     /* ===============================
                        SORTABLE INIT (ON LOAD)
                     =============================== */
                     $('.sortable-images').sortable({
                          placeholder: "ui-state-highlight",
                          forcePlaceholderSize: true,
                          update: function() {
                               updateImageOrder($(this));
                          }
                     });

                     $('.sortable-images').each(function() {
                          updateImageOrder($(this));
                     });


                     /* ===============================
                            FORM SUBMIT LOADING
                         =============================== */
                     $('#editVariantsForm').on('submit', function(e) {
                          let $form = $(this);
                          let $btn = $form.find('button[type="submit"]');

                          // Add validation rules for price
                          $('.ve-price').each(function() {
                               let val = $(this).val();
                               if (!/^\d+$/.test(val)) {
                                    toastr.error('Only digits are allowed for price');
                                    $(this).addClass('is-invalid');
                                    e.preventDefault();
                                    return false;
                               }
                          });

                          // Add validation rules for discount value
                          $('.ve-discount-value').each(function() {
                               let type = $(this).closest('.variant-block').find('.ve-discount-type').val();
                               let val = $(this).val();
                               if ((type === '%' || type === 'percent') && val !== '') {
                                    if (parseFloat(val) > 100) {
                                         toastr.error('Discount value cannot exceed 100 for percentage type');
                                         $(this).addClass('is-invalid');
                                         e.preventDefault();
                                         return false;
                                    }
                               }
                               $(this).removeClass('is-invalid');
                          });

                          if (this.checkValidity()) {

                               $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating...').prop('disabled', true);
                          }
                     });



                });
           </script>
           @endpush