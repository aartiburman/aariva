@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Create Campaign</h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('campaign.store') }}" id="campaignForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount %</label>
                            <input type="text" name="discount_percent" class="form-control" value="{{ old('discount_percent') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status','1')=='1'?'selected':'' }}>Active</option>
                                <option value="0" {{ old('status')==='0'?'selected':'' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Budget per Vendor</label>
                            <input type="text" name="budget_per_vendor" class="form-control" value="{{ old('budget_per_vendor') }}">
                            <small class="text-muted">Optional: Fixed budget for vendors</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Vendors</label>
                            <input type="text"  name="max_vendors" class="form-control" value="{{ old('max_vendors') }}">
                            <small class="text-muted">Optional: Limit vendor participation</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link Offer (optional)</label>
                            <select name="offer_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($offers as $o)
                                    <option value="{{ $o->id }}" {{ old('offer_id') == $o->id ? 'selected' : '' }}>
                                        {{ $o->code }} ({{ $o->type == 1 ? 'Percent' : 'Flat' }} @ {{ $o->value }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">If selected, the campaign mirrors offer discount and dates.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Assign Vendors (optional)</label>
                            <select name="vendor_ids[]" class="form-control" multiple data-choices data-choices-removeItem>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" {{ in_array($v->id, (array) old('vendor_ids', [])) ? 'selected' : '' }}>
                                        {{ $v->store_name ?? $v->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Vendors may still need to join and fund budget.</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Assign Products (optional)</label>
                            <select name="product_ids[]" class="form-control" multiple data-choices data-choices-removeItem>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Phase‑1: a product can belong to only one campaign.</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">Create</button>
                        <a href="{{ route('campaign.list') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        var $form = $('#campaignForm');

        @if(session('vendor_skip_warning'))
        // Show a toast/alert warning if some vendors were skipped due to low wallet
        try {
            if (window.toastr) {
                toastr.warning(@json(session('vendor_skip_warning')));
            } else {
                alert(@json(session('vendor_skip_warning')));
            }
        } catch (e) {}
        @endif

        if ($form.length && $.fn.validate) {
            $form.validate({
                rules: {
                    name: {
                        required: true
                    },
                    discount_percent: {
                        required: true,
                        digits: true
                    },
                    budget_per_vendor: {
                         required: true,
                        digits: true
                    },
                    max_vendors: {
                         required: true,
                        digits: true
                    }
                },
                messages: {
                    name: {
                        required: 'Campaign name is required.'
                    },
                    discount_percent: {
                        digits: 'Please enter digits only for Discount %.'
                    },
                    budget_per_vendor: {
                        digits: 'Please enter digits only for Budget per Vendor.'
                    },
                    max_vendors: {
                        digits: 'Please enter digits only for Max Vendors.'
                    }
                },
                errorClass: 'text-danger',
                errorElement: 'span',
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                }
            });
        }

        // Restrict typing/pasting to digits only
        $('[name="discount_percent"], [name="budget_per_vendor"], [name="max_vendors"]').on('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
</script>
@endpush

@endsection
