@foreach ($products as $value)
<tr id="row_{{ $value->id }}">
    <td>
        <div class="form-check">
            <input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="{{ $value->id }}">
        </div>
    </td>

    <!--PRODUCT NAME-->
    <td>
        @php
        $firstVariant = $value->variants->first();
        $firstImage = '';
        if ($firstVariant) {
        $images = json_decode($firstVariant->image, true);
        if (is_array($images) && count($images) > 0) {
        $firstImage = $images[0];
        }
        }
        @endphp
        <div class="d-flex align-items-center gap-2">
            @if($firstImage)
            <img src="{{ asset('uploads/products/'.$firstImage) }}" alt="product" class="avatar-sm rounded border">
            @else
            <div class="avatar-sm rounded 
                    border d-flex align-items-center justify-content-center rounded-circle">
                <iconify-icon icon="solar:gallery-linear" class="fs-20 text-muted"></iconify-icon>
            </div>
            @endif
            <div>
                @php $lang = app()->getLocale(); @endphp
                <span class="toggle-variant cursor-pointer" data-id="{{ $value->id }}">
                    <strong>{{ $value->{"name_$lang"} ?? $value->name }}</strong>
                    <iconify-icon icon="solar:alt-arrow-down-linear" class="ms-1 align-middle"></iconify-icon>
                </span>
                @if($value->available_offers && $value->available_offers->count() > 0)
                <div class="mt-1">
                    @foreach($value->available_offers as $offer)
                    <span class="badge bg-soft-info text-info border border-info border-opacity-25 fs-11" data-bs-toggle="tooltip" title="{{ $offer->type == 1 ? $offer->value . '%' : '₹' . $offer->value }} OFF">
                        <iconify-icon icon="solar:tag-linear" class="align-middle me-1"></iconify-icon>{{ $offer->code }}
                    </span>
                    @endforeach
                </div>
                @endif
                <br>
                <small class="text-muted">ID: #{{ $value->id }}</small>
            </div>
        </div>

        <!-- 🔽 VARIANT SECTION -->
        <div class="variant-row d-none mt-3" id="variant_{{ $value->id }}">

            @forelse ($value->variants as $variant)
            @php
            $images = json_decode($variant->image, true) ?? [];
            @endphp

            <div class="single-variant-container mb-4 pb-3 border-bottom border-light-subtle last-child-border-0">
                <div class="variant-box mb-3 p-2 border rounded" data-price="{{ $variant->price }}" data-discount-type="{{ $variant->discount_type }}" data-discount-value="{{ $variant->discount_value }}">

                    <!-- INLINE IMAGES -->
                    @if(count($images))
                    <div class="variant-images d-flex gap-2 mb-2">
                        @foreach ($images as $img)
                        <img src="{{ asset('uploads/products/'.$img) }}"
                            class="variant-thumb">
                        @endforeach
                    </div>
                    @endif

                    <!-- VARIANT DETAILS -->
                    <div class="variant-details">
                        <p class="mb-1"><strong>Color:</strong> {{ $variant->color }}</p>

                        <p class="mb-1">
                            <strong>Size:</strong>
                            @foreach($variant->sizes_list as $size)
                            {{ $size->name }}@if(!$loop->last), @endif
                            @endforeach
                        </p>

                        <p class="mb-1"><strong>Base Price:</strong> {{ $variant->price }}</p>
                        <p class="mb-1"><strong>Stock:</strong> {{ $variant->stock }}</p>
                        <p class="mb-1">
                            <strong>Discount:</strong>
                            {{ $variant->discount_value }} {{ $variant->discount_type }}
                        </p>
                        <p class="mb-0"><strong>Final Price:</strong> {{ $variant->final_price }}</p>
                    </div>
                
                </div>
                <div class="w-100">
                    <div class="mt-2 row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="small text-muted mb-1">Discount Type</label>
                            <select class="form-select form-select-sm ve-discount-type">
                                <option value="">None</option>
                                <option value="off" @selected($variant->discount_type == 'off')>Flat</option>
                                <option value="%" @selected($variant->discount_type == '%' || $variant->discount_type == 'percent')>%</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted mb-1">Discount Value</label>
                            <input type="number" step="0.01" class="form-control form-control-sm ve-discount-value" value="{{ $variant->discount_value }}" placeholder="0" @disabled(!$variant->discount_type)>
                        </div>

                    </div>
                </div>
            </div>
            @empty
            <span class="text-muted">No variants found</span>
            @endforelse
          </div>
        </div>
    </td>
    <td>{{ $value->{"category_name_$lang"} ?? $value->category_name ?? 'N/A' }}</td>
    <td>{{ $value->{"subcategory_name_$lang"} ?? $value->subcategory_name ?? 'N/A' }}</td>
    <td>{{ $value->{"child_category_name_$lang"} ?? $value->child_category_name ?? 'N/A' }}</td>
    <td>{{ $value->brand_name ?? 'N/A' }}</td>
    <td>{{ $value->vendor_name }} @if(!empty($value->store_name))
        <br>({{ $value->store_name }}) @else <br> (Nepoora) @endif
    </td>

    <td>
        <span class="badge bg-primary-subtle text-primary fs-12">
            {{ $value->variants->count() }} Variants
        </span>
    </td>

    <td>
        @if(Auth::user()->role == '1')
        <div class="product-status-container" data-id="{{ $value->id }}" style="cursor: pointer;">
            @if($value->status == 0)
            <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-11 text-uppercase product-status-badge">Pending</span>
            @elseif($value->status == 1)
            <span class="badge bg-success-subtle text-success py-1 px-2 fs-11 text-uppercase product-status-badge">Approved</span>
            @elseif($value->status == 2)
            <span class="badge bg-danger-subtle text-danger py-1 px-2 fs-11 text-uppercase product-status-badge">Rejected</span>
            @endif

            <select class="form-select form-select-sm product-status-select d-none">
                @if($value->status != 1)
                <option value="0" @selected($value->status == 0)>Pending</option>
                @endif
                <option value="1" @selected($value->status == 1)>Approve</option>
                <option value="2" @selected($value->status == 2)>Reject</option>
            </select>
        </div>
        @elseif(Auth::user()->role == '2')
        @if($value->status == 0)
        <span class="badge bg-warning">Pending</span>
        @elseif($value->status == 1)
        <span class="badge bg-success">Approved</span>
        @elseif($value->status == 2)
        <span class="badge bg-danger-subtle text-danger py-1 px-2 fs-11 text-uppercase">Rejected</span>
        @endif
        @endif
    </td>

    <!-- ACTIONS -->
    <td>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ url('product-detail/'.$value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Product">
                <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
            </a>
            <a href="{{ url('edit-product/'.$value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Product">
                <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
            </a>
            <a href="{{ url('edit-variant/'.$value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Variant">
                <iconify-icon icon="solar:tuning-square-linear" class="fs-20"></iconify-icon>
            </a>
            <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-product" data-id="{{ $value->id }}" data-bs-toggle="tooltip" title="Delete">
                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
            </a>
        </div>
    </td>
</tr>
@endforeach
