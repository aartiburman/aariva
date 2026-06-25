@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Edit Coupon: {{ $coupon->code }}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('coupons.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $coupon->id }}">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Coupon Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ $coupon->code }}" required>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-control">
                                        <option value="1" {{ $coupon->type == 1 ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="0" {{ $coupon->type == 0 ? 'selected' : '' }}>Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Value</label>
                                    <input type="number" name="value" class="form-control" value="{{ $coupon->value }}" step="0.01" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Valid From</label>
                                    <input type="date" name="valid_from" class="form-control" value="{{ $coupon->valid_from ? $coupon->valid_from->format('Y-m-d') : '' }}">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Valid Until</label>
                                    <input type="date" name="valid_until" class="form-control" value="{{ $coupon->valid_until ? $coupon->valid_until->format('Y-m-d') : '' }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Max Uses</label>
                                    <input type="number" name="max_uses" class="form-control" value="{{ $coupon->max_uses }}">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control" data-choices>
                                        <option value="1" {{ $coupon->status ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$coupon->status ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Applicable Categories (Optional)</label>
                                    <select name="category_ids[]" class="form-control" multiple data-choices data-choices-removeItem>
                                        @php($selectedCats = $coupon->categories->pluck('id')->toArray())
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCats) ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">If none selected, applicable to all categories.</small>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Applicable Products (Optional)</label>
                                    <select name="product_ids[]" class="form-control" multiple data-choices data-choices-removeItem>
                                        @php($selectedProds = $coupon->products->pluck('id')->toArray())
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" {{ in_array($prod->id, $selectedProds) ? 'selected' : '' }}>
                                                {{ $prod->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">If none selected, applicable to all products.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label">Applicable Vendors (Optional)</label>
                                    <select name="vendor_ids[]" class="form-control" multiple data-choices data-choices-removeItem>
                                        @php($selectedVendors = $coupon->vendors->pluck('id')->toArray())
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ in_array($vendor->id, $selectedVendors) ? 'selected' : '' }}>
                                                {{ $vendor->name }} ({{ $vendor->store_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">If none selected, applicable to all vendors.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('coupons.list') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Coupon</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
