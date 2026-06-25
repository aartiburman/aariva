 <!-- Images & Status -->
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Category</label>
                                <select name="category_id" class="form-select category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Brand</label>
                                <select name="brand_id" class="form-select categoryBrand">
                                    <option value="">Select Brand</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Short Description</label>
                            <textarea name="short_description" class="form-control"></textarea>
                        </div>

                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" rows="4" class="form-control"></textarea>
                        </div>

                    </div>
                </div>

                {{-- ================= VARIANTS ================= --}}
                <div class="fetch-variant-section">
                    <h5 class="mb-3">Color, Size & Stock</h5>

                    @foreach($variant as $index => $value)

                    <input type="hidden" name="variant_id[]" value="{{ $value->id }}">

                    <div class="variant-block border p-3 mb-4 rounded text-start">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Variant #{{ $index + 1 }}</h6>
                            <button type="button" class="btn btn-sm btn-danger removeVariant">✕</button>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>SKU</label>
                                <input type="text" name="sku[]" class="form-control" value="{{ $value->sku }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Color</label>
                                <input type="text" name="color[]" class="form-control" value="{{ $value->color }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Stock</label>
                                <input type="number" name="stock[]" class="form-control" value="{{ $value->stock }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Price</label>
                                <input type="number" name="price[]" class="form-control" value="{{ $value->price }}" step="any">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Discount Type</label>
                                <select name="discount_type[]" class="form-control">
                                    <option value="">None</option>
                                    <option value="off" {{ $value->discount_type=='off' ? 'selected' : '' }}>Flat</option>
                                    <option value="%" {{ $value->discount_type=='%' ? 'selected' : '' }}>%</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Discount Value</label>
                                <input type="number" name="discount_value[]" class="form-control"
                                    value="{{ $value->discount_value }}" step="any">
                            </div>

                            {{-- Size Category --}}
                            <div class="col-md-6 mb-3">
                                <label>Size Category</label>
                                <select name="size_category_id[]"
                                    class="form-control UpdateSizeCategory">
                                    <option value="">Select Size Category</option>
                                    @foreach($sizecategory as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ $value->size_cat_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Sizes --}}
                            <div class="col-md-6 mb-3">
                                <label>Size</label>

                                <select name="size[{{ $index }}][]"
                                    class="form-control update-size-select js-size-select"
                                    multiple>
                                    @foreach($value->sizes_list as $size)
                                    <option value="{{ $size->id }}" selected>
                                        {{ $size->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>



                        {{-- Images --}}
                        <div class="mb-3">
                            <label>Variant Images</label>
                            <input type="file" name="product_image[{{ $index }}][]" class="form-control" multiple>

                            <div class="d-flex flex-wrap mt-2">
                                @foreach(json_decode($value->image, true) ?? [] as $img)
                                <div class="image-box position-relative me-2 mb-2"
                                    data-image="{{ $img }}"
                                    data-variant-id="{{ $value->id }}">
                                    <img src="{{ asset('uploads/products/'.$img) }}" width="100" class="rounded border">
                                    <button type="button" class="btn btn-sm btn-danger delete-image-btn">✕</button>
                                </div>
                                @endforeach
                            </div>

                            <input type="hidden" name="image_order[{{ $value->id }}]" class="image-order-input">
                        </div>
                    </div>
                    @endforeach

                    <button type="button"  class="btn btn-outline-primary mt-2 addVariant">
                        + Add Another Variant
                    </button>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Save Product</button>
                    </div>

                </div>
            </div>
