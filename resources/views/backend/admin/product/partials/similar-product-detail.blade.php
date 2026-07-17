<form id="productForm"
    class="no-loader"
    action="{{ route('store.similar.product') }}"
    method="POST"
    enctype="multipart/form-data">
    @csrf
    <div class="similar-product-detail">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" readonly>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories_data as $cat)
                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sub Category</label>
                <select name="subcategory_id" class="form-select subcategory_id">
                    <option value="">Select Sub Category</option>
                    @foreach($subcategories as $s)
                    <option value="{{ $s->id }}" {{ $product->subcategory_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Child Category</label>
                <select name="child_category_id" class="form-select child_category_id">
                    <option value="">Select Child Category</option>
                    @foreach($childcategory as $c)
                    <option value="{{ $c->id }}" {{ $product->child_category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label class="form-label">Brand</label>
                <select name="brand_id" class="form-select categoryBrand">
                    <option value="">Select Brand</option>
                    @foreach($brand as $b)
                    <option value="{{ $b->id }}" {{ $product->brand_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="1" {{ $product->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $product->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label">Short Description</label>
            <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
        </div>

        <div class="mt-3">
            <label class="form-label">Description</label>
            <div class="d-flex justify-content-end mb-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="generateDescBtn">
                    <iconify-icon icon="solar:magic-stick-3-linear" class="fs-14"></iconify-icon> Generate Description Template
                </button>
            </div>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="row mt-3">
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Offers</label>
                <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light-subtle">
                    @php
                    $selectedOffers = json_decode($product->offer_id, true) ?? [];
                    @endphp
                    @foreach ($offers as $value)
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" name="offers[]" value="{{ $value->id }}" id="offer_{{ $value->id }}" {{ in_array($value->id, $selectedOffers) ? 'checked' : '' }}>
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
                    @php
                    $selectedPlacement = json_decode($product->product_in, true) ?? [];
                    @endphp
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" name="product_in[]" value="1" id="in_1" {{ in_array(1, $selectedPlacement) ? 'checked' : '' }}>
                        <label class="form-check-label cursor-pointer" for="in_1">Best Seller</label>
                    </div>
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" name="product_in[]" value="2" id="in_2" {{ in_array(2, $selectedPlacement) ? 'checked' : '' }}>
                        <label class="form-check-label cursor-pointer" for="in_2">Trending</label>
                    </div>
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" name="product_in[]" value="3" id="in_3" {{ in_array(3, $selectedPlacement) ? 'checked' : '' }}>
                        <label class="form-check-label cursor-pointer" for="in_3">Popular</label>
                    </div>
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" name="product_in[]" value="4" id="in_4" {{ in_array(4, $selectedPlacement) ? 'checked' : '' }}>
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
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ $product->is_featured ? 'checked' : '' }}>
                        <label class="form-check-label cursor-pointer" for="is_featured">Mark as Featured Product</label>
                    </div>
                    <hr>
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label">Warranty</label>
                            <select name="vendor_warranty" class="form-select" id="vendor_warranty">
                                <option value="" {{ $product->vendor_warranty == '' ? 'selected' : '' }}>No Warranty</option>
                                <option value="6 Months" {{ $product->vendor_warranty == '6 Months' ? 'selected' : '' }}>6 Months</option>
                                <option value="1 Year" {{ $product->vendor_warranty == '1 Year' ? 'selected' : '' }}>1 Year</option>
                                <option value="2 Years" {{ $product->vendor_warranty == '2 Years' ? 'selected' : '' }}>2 Years</option>
                                <option value="3 Years" {{ $product->vendor_warranty == '3 Years' ? 'selected' : '' }}>3 Years</option>
                                <option value="5 Years" {{ $product->vendor_warranty == '5 Years' ? 'selected' : '' }}>5 Years</option>
                                <option value="Lifetime" {{ $product->vendor_warranty == 'Lifetime' ? 'selected' : '' }}>Lifetime</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="vendor_payment" value="1" id="vendor_payment" {{ $product->vendor_payment ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="vendor_payment">100% Secure Payments</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="vendor_return" value="1" id="vendor_return" {{ $product->vendor_return ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="vendor_return">Easy & Hassle-Free Returns</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="vendor_delivery" value="1" id="vendor_delivery" {{ $product->vendor_delivery ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="vendor_delivery">Free Delivery</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- VARIANTS --}}
        <div class="fetch-variant-section mt-4">
            <h5 class="mb-3">Variants</h5>
            <div class="variantWrapper">

            @if(isset($variant) && $variant->count())
            @foreach($variant as $index => $v)
            <input type="hidden" name="variant_id[]" value="{{ $v->id }}">
            <div class="variant-block border p-3 mb-4 rounded text-start">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Variant #{{ $index + 1 }}</h6>
                    <button type="button" class="btn btn-sm btn-danger removeVariant">✕</button>
                </div>
                <div class="row g-3">
                    <input type="hidden" name="variant[]" class="form-control" value="{{$index + 1 }}" required>

                    <div class="col-md-4">
                        <label>SKU</label>
                        <input type="text" name="sku[]" class="form-control skugen" value="{{ $v->sku }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Color</label>
                        <input type="text" name="color[]" class="form-control" value="{{ $v->color }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Stock</label>
                        <input type="text" name="stock[]" class="form-control" value="{{ $v->stock }}" required>
                    </div>

                    <div class="col-md-4">
                        <label>Price</label>
                        <input type="text" name="price[]" class="form-control" value="{{ $v->price }}" step="any">
                    </div>
                    <div class="col-md-4">
                        <label>Discount Type</label>
                        <select name="discount_type[]" class="form-control ve-discount-type">
                            <option value="">None</option>
                            <option value="off" {{ in_array($v->discount_type, ['off', 'flat']) ? 'selected' : '' }}>Flat</option>
                            <option value="%" {{ in_array($v->discount_type, ['%', 'percent']) ? 'selected' : '' }}>%</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Discount Value</label>
                        <input type="text" name="discount_value[]" class="form-control ve-discount-value" value="{{ $v->discount_value }}" step="any" max="100">
                    </div>

                    <div class="col-md-4 mb-2">
                        <label>Product Variant</label>
                        <select name="product_variant[]" class="form-control dynamic-create @error('product_variant.*') is-invalid @enderror" data-allow-new="true" data-type="product_variant" data-placeholder="Create or select Type">
                            <option value="">Select product Variant </option>
                            @foreach($product_variant_labels as $label)
                            <option value="{{ $label->id }}" {{ ($v->product_variant ?? '') == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                            @endforeach
                        </select>
                        @error('product_variant.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>


                    <div class="col-md-4 mb-2">
                        <label>Size Category</label>
                        <select name="size_cat_id[]" class="form-control dynamic-create similerSelectSizeCategory" data-allow-new="true" data-type="size_category" data-placeholder="Create or select Category">
                            <option value="">Select Size Category</option>
                            @foreach($sizecategory as $cat)
                            <option value="{{ $cat->id }}" {{ ($v->size_cat_id ?? $v->size_cat_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    
                    <div class="col-md-4">
                        <label>Sizes</label>
                        <select name="size[{{ $index }}][]" class="form-control update-size-select js-size-select dynamic-create" data-allow-new="true" data-type="size" data-placeholder="Create or select sizes" multiple>
                            @if(isset($v->sizes_list) && $v->sizes_list->count())
                            @foreach($v->sizes_list as $s)
                            <option value="{{ $s->id }}" selected>{{ $s->name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Material</label>
                    <input type="text" name="material[]" class="form-control" value="{{ $v->material }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Pkg Weight (kg)</label>
                    <input type="number" step="0.01" min="0" name="package_weight[]" class="form-control" value="{{ $v->package_weight }}" placeholder="0.00">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Pkg Length (cm)</label>
                    <input type="number" step="0.1" min="0" name="package_length[]" class="form-control" value="{{ $v->package_length }}" placeholder="0.0">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Pkg Width (cm)</label>
                    <input type="number" step="0.1" min="0" name="package_width[]" class="form-control" value="{{ $v->package_width }}" placeholder="0.0">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Pkg Height (cm)</label>
                    <input type="number" step="0.1" min="0" name="package_height[]" class="form-control" value="{{ $v->package_height }}" placeholder="0.0">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Package Type</label>
                    <select name="package_type[]" class="form-control">
                        <option value="">Select</option>
                        <option value="box" {{ ($v->package_type ?? '') == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="cardboard_box" {{ ($v->package_type ?? '') == 'cardboard_box' ? 'selected' : '' }}>Cardboard Box</option>
                        <option value="corrugated_box" {{ ($v->package_type ?? '') == 'corrugated_box' ? 'selected' : '' }}>Corrugated Box</option>
                        <option value="polybag" {{ ($v->package_type ?? '') == 'polybag' ? 'selected' : '' }}>Poly Bag</option>
                        <option value="bubble_wrap" {{ ($v->package_type ?? '') == 'bubble_wrap' ? 'selected' : '' }}>Bubble Wrap</option>
                        <option value="padded_envelope" {{ ($v->package_type ?? '') == 'padded_envelope' ? 'selected' : '' }}>Padded Envelope</option>
                        <option value="paper_envelope" {{ ($v->package_type ?? '') == 'paper_envelope' ? 'selected' : '' }}>Paper Envelope</option>
                        <option value="plastic_pouch" {{ ($v->package_type ?? '') == 'plastic_pouch' ? 'selected' : '' }}>Plastic Pouch</option>
                        <option value="zip_lock_bag" {{ ($v->package_type ?? '') == 'zip_lock_bag' ? 'selected' : '' }}>Zip Lock Bag</option>
                        <option value="foam_packaging" {{ ($v->package_type ?? '') == 'foam_packaging' ? 'selected' : '' }}>Foam Packaging</option>
                        <option value="thermocol_box" {{ ($v->package_type ?? '') == 'thermocol_box' ? 'selected' : '' }}>Thermocol Box</option>
                        <option value="wooden_box" {{ ($v->package_type ?? '') == 'wooden_box' ? 'selected' : '' }}>Wooden Box</option>
                        <option value="carton" {{ ($v->package_type ?? '') == 'carton' ? 'selected' : '' }}>Carton</option>
                        <option value="gift_box" {{ ($v->package_type ?? '') == 'gift_box' ? 'selected' : '' }}>Gift Box</option>
                        <option value="tube" {{ ($v->package_type ?? '') == 'tube' ? 'selected' : '' }}>Tube</option>
                        <option value="other" {{ ($v->package_type ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="mt-3">
                    <label>Variant Images</label>
                    <input type="file" name="product_image[{{ $index }}][]" class="form-control" multiple>
                    <div class="d-flex flex-wrap mt-2">
                        @foreach($v->images as $img)
                        <div class="image-box position-relative me-2 mb-2" data-image="{{ $img }}" data-variant-id="{{ $v->id }}">
                            <img src="{{ asset('uploads/products/'.$img) }}" width="100" class="rounded border">
                            <button type="button" class="btn btn-sm btn-danger delete-image-btn">✕</button>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="image_order[{{ $v->id }}]" class="image-order-input">
                </div>
            </div>
            @endforeach
            @else
            <p class="text-muted">No variants found</p>
            @endif
            </div>

            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
               <button type="button" class="btn btn-info addVariant">
                  + Add Another Variant                
                </button>
                  <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
        </div>
    </div>
<script>
    function generateDescription() {
        let color = $('input[name="color[]"]').val() || '';
        let material = $('input[name="material[]"]').val() || '';
        let sizeText = '';
        let sizeSelect = $('.js-size-select').first();
        if (sizeSelect.length) {
            let selectedOptions = sizeSelect.find('option:selected');
            if (selectedOptions.length > 0) {
                sizeText = selectedOptions.map(function() { return $(this).text(); }).get().join(', ');
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

    $(document).on('click', '#generateDescBtn', generateDescription);
</script>
</form>
