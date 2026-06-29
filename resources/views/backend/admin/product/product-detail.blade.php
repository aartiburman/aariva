@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid ">

        <!-- Page Header -->
        <div class="row align-items-center mt-4 mb-3">
            <div class="col">
                <h4 class="mb-0">Product Details</h4>
            </div>
            <div class="col-auto">
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                    Back
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Product Info -->
            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Basic Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Product Name:</label>
                                <p class="fs-15 text-dark">{{ $product->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Slug:</label>
                                <p class="fs-15 text-dark">{{ $product->slug }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Category:</label>
                                <p class="fs-15 text-dark">{{ $product->category->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Sub Category:</label>
                                <p class="fs-15 text-dark">{{ $product->subCategory->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Child Category:</label>
                                <p class="fs-15 text-dark">{{ $product->childCategory->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Brand:</label>
                                <p class="fs-15 text-dark">{{ $product->brand->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Vendor:</label>
                                <p class="fs-15 text-dark">{{ $product->vendor->name ?? 'N/A' }} ({{ $product->vendor->store_name ?? '' }})</p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Status:</label>
                                <div>
                                    @if($product->status == 1)
                                        <span class="badge bg-success-subtle text-success">Approved</span>
                                    @elseif($product->status == 0)
                                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                                    @elseif($product->status == 2)
                                        <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold text-muted">Short Description:</label>
                            <div class="bg-light p-2 rounded">
                                {!! $product->short_description !!}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold text-muted">Description:</label>
                            <div class="bg-light p-3 rounded">
                                {!! $product->description !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Stats / Additional Info -->
            <div class="col-xl-4 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Product Statistics</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Variants
                                <span class="badge bg-primary rounded-pill">{{ $product->variants->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Created At
                                <span class="text-muted">{{ $product->created_at->format('d M Y') }}</span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                Updated At
                                <span class="text-muted">{{ $product->updated_at->format('d M Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                     <div class="card-header">
                        <h4 class="card-title">Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                             <a href="{{ route('edit.product', $product->id) }}" class="btn btn-primary">
                                <iconify-icon icon="solar:pen-linear" class="align-middle me-1"></iconify-icon> Edit Product
                            </a>
                            <a href="{{ route('edit.variant', $product->id) }}" class="btn btn-secondary">
                                <iconify-icon icon="solar:tuning-square-linear" class="align-middle me-1"></iconify-icon> Edit Variants
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variants Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Product Variants</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover table-centered mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Image</th>
                                        <th>Variant Type</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Discount</th>
                                        <th>Final Price</th>
                                        <th>SKU</th>
                                        <th>Packaging</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($product->variants as $variant)
                                    @php
                                        $images = json_decode($variant->image, true) ?? [];
                                        $sizes = \App\Models\ProductSize::whereIn('id', json_decode($variant->size, true) ?? [])->pluck('name')->toArray();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @foreach(array_slice($images, 0, 3) as $img)
                                                    <img src="{{ asset('uploads/products/'.$img) }}" alt="variant" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                                @endforeach
                                                @if(count($images) > 3)
                                                    <span class="badge bg-light text-dark border d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">+{{ count($images) - 3 }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {{ $variantLabels[$variant->product_variant] ?? 'N/A' }}
                                        </td>
                                        <td>{{ $variant->color ?: 'N/A' }}</td>
                                        <td>
                                            @if(count($sizes))
                                                {{ implode(', ', $sizes) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $variant->price }}</td>
                                        <td>
                                            @if($variant->stock > 0)
                                                <span class="text-success">{{ $variant->stock }}</span>
                                            @else
                                                <span class="text-danger">Out of Stock</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($variant->discount_value > 0)
                                                {{ $variant->discount_value }} {{ $variant->discount_type == 'percent' || $variant->discount_type == '%' ? '%' : 'OFF' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $variant->final_price ?? $variant->price }}</td>
                                        <td>{{ $variant->sku }}</td>
                                        <td>
                                            @php
                                                $pkgParts = [];
                                                if ($variant->package_weight) $pkgParts[] = $variant->package_weight . 'kg';
                                                if ($variant->package_length && $variant->package_width && $variant->package_height) $pkgParts[] = $variant->package_length . 'x' . $variant->package_width . 'x' . $variant->package_height . 'cm';
                                                if ($variant->package_type) $pkgParts[] = ucfirst(str_replace('_', ' ', $variant->package_type));
                                            @endphp
                                            @if(count($pkgParts))
                                                <small class="text-muted">{{ implode(', ', $pkgParts) }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No variants found for this product.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Customer Reviews</h4>
                        <span class="badge bg-primary">{{ $product->reviews->count() }} Total Reviews</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($product->reviews as $review)
                            <div class="col-md-6 mb-3">
                                <div class="p-3 border rounded bg-light-subtle h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm">
                                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                    {{ substr($review->user->name ?? 'C', 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fs-14">{{ $review->user->name ?? 'Customer' }}</h6>
                                                <small class="text-muted">{{ $review->created_at->format('d M Y') }}</small>
                                            </div>
                                        </div>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                <iconify-icon icon="solar:star-{{ $i <= $review->rating ? 'bold' : 'linear' }}"></iconify-icon>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="mb-0 fs-13 text-dark italic">"{{ $review->review }}"</p>
                                    <!-- @if($review->status == 1)
                                        <span class="badge bg-success-subtle text-success fs-10 mt-2">Approved</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fs-10 mt-2">Pending</span>
                                    @endif -->
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-4">
                                <iconify-icon icon="solar:chat-line-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                                <p class="text-muted">No reviews yet for this product.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">Earnings Estimate</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-4">
                                <label class="form-label">Selling Price</label>
                                <input type="number" step="0.01" id="pd_price" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Discount Type</label>
                                <select id="pd_dt" class="form-select">
                                    <option value="">None</option>
                                    <option value="%">%</option>
                                    <option value="off">Flat</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">Discount Value</label>
                                <input type="text" step="0.01" id="pd_dv" class="form-control" placeholder="0" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Shipping</label>
                                <input type="text" step="0.01" id="pd_ship" class="form-control" placeholder="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Tax</label>
                                <input type="text" step="0.01" id="pd_tax" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="mt-2 small">
                            <div>Commission: <span id="pd_commission">0.00</span> ({{ $commissionPercent ?? 0 }}%)</div>
                            <div>PG Fee: <span id="pd_pg">0.00</span> ({{ $pgFeePercent ?? 0 }}%)</div>
                            <div>Selling: <span id="pd_sell">0.00</span></div>
                            <div class="fw-bold">Net Payout: <span id="pd_net">0.00</span></div>
                        </div>
                    </div>
                </div>
    </div>
</div>

@endsection
@push('chart-scripts')
<script>
    (function(){
        const rateC = {{ $commissionPercent ?? 0 }};
        const ratePG = {{ $pgFeePercent ?? 0 }};
        const dtEl = document.getElementById('pd_dt');
        const dvEl = document.getElementById('pd_dv');

        function calc(){
            const p = parseFloat(document.getElementById('pd_price').value)||0;
            const dt = dtEl.value;
            const dv = parseFloat(dvEl.value)||0;
            const ship = parseFloat(document.getElementById('pd_ship').value)||0;
            const tax = parseFloat(document.getElementById('pd_tax').value)||0;

            // Enable/Disable logic
            if (dt === "") {
                dvEl.value = "";
                dvEl.disabled = true;
                dvEl.classList.remove('is-invalid');
            } else {
                dvEl.disabled = false;
            }

            // Validation logic
            if (dt !== "") {
                if (!/^\d+$/.test(dvEl.value) && dvEl.value !== "") {
                    dvEl.classList.add('is-invalid');
                    if (!dvEl.nextElementSibling || !dvEl.nextElementSibling.classList.contains('invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'Only digits allowed';
                        dvEl.parentNode.appendChild(feedback);
                    }
                } else if (dv > 100) {
                    dvEl.classList.add('is-invalid');
                    if (!dvEl.nextElementSibling || !dvEl.nextElementSibling.classList.contains('invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'Max 100';
                        dvEl.parentNode.appendChild(feedback);
                    } else {
                        dvEl.nextElementSibling.textContent = 'Max 100';
                    }
                } else {
                    dvEl.classList.remove('is-invalid');
                    const feedback = dvEl.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.remove();
                    }
                }
            } else {
                dvEl.classList.remove('is-invalid');
                const feedback = dvEl.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }

            let sp = p;
            if(dt==='%' && dv>0){ sp = p - ((p*dv)/100); }
            if(dt==='off' && dv>0){ sp = p - dv; }
            if(sp<0) sp = 0;
            const commission = (sp*rateC)/100;
            const pg = (sp*ratePG)/100;
            const net = Math.max(0, sp - commission - pg - ship - tax);
            document.getElementById('pd_sell').textContent = sp.toFixed(2);
            document.getElementById('pd_commission').textContent = commission.toFixed(2);
            document.getElementById('pd_pg').textContent = pg.toFixed(2);
            document.getElementById('pd_net').textContent = net.toFixed(2);
        }

        ['pd_price','pd_dt','pd_dv','pd_ship','pd_tax'].forEach(id=>{
            const el=document.getElementById(id);
            if(el){ el.addEventListener('input', calc); el.addEventListener('change', calc); }
        });
        calc();
    })();
</script>
@endpush
