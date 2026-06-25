@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <!-- Start Container Fluid -->
 <div class="container-fluid">
            <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1 anchor mb-4" id="basic">
                            Update Offer
                        </h5>

                        <form action="{{ route('update.offer') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $offer->id }}">

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Offer Code</label>
                                    <input type="text" name="code" value="{{ old('code', $offer->code) }}"
                                        class="form-control @error('code') is-invalid @enderror"
                                        placeholder="e.g. SUMMER2024" maxlength="50" required>
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Offer Type</label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                                        <option value="0" {{ old('type', $offer->type) == '0' ? 'selected' : '' }}>Fixed Amount Off</option>
                                        <option value="1" {{ old('type', $offer->type) == '1' ? 'selected' : '' }}>Percentage Off (%)</option>
                                    </select>
                                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Discount Value</label>
                                    <input type="number" step="0.01" name="value" value="{{ old('value', $offer->value) }}"
                                        class="form-control @error('value') is-invalid @enderror"
                                        placeholder="0.00" max="1000000" required>
                                    @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Max Uses</label>
                                    <input type="number" name="max_uses" value="{{ old('max_uses', $offer->max_uses) }}"
                                        class="form-control @error('max_uses') is-invalid @enderror"
                                        placeholder="Leave empty for unlimited" max="1000000">
                                    @error('max_uses')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Valid From</label>
                                    <input type="date" name="valid_from" value="{{ old('valid_from', $offer->valid_from ? $offer->valid_from->format('Y-m-d') : '') }}"
                                        class="form-control @error('valid_from') is-invalid @enderror">
                                    @error('valid_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Valid Until</label>
                                    <input type="date" name="valid_until" value="{{ old('valid_until', $offer->valid_until ? $offer->valid_until->format('Y-m-d') : '') }}"
                                        class="form-control @error('valid_until') is-invalid @enderror">
                                    @error('valid_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="1" {{ old('status', $offer->status) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', $offer->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                               <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('offer.list') }}" class="btn border-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Offer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- [ Main Content ] end -->

@endsection
