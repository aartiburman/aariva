@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-6">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Create Vendor Payout</h4>
                </div>
            </div>
            <div class="col-6 text-end">
                <a href="{{ route('vendor.payout', ['frequency' => 'monthly']) }}" class="btn btn-sm btn-outline-secondary">Back to Payouts</a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">Payout Details</h5>
                    </div>
                    <div class="card-body">
                        <form id="payoutCreateForm" class="no-loader" method="POST" action="{{ route('vendor.payout.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vendor</label>
                                <select name="vendor_id" class="form-select" required>
                                    <option value="">Select Vendor</option>
                                    @foreach(($vendors ?? []) as $v)
                                        <option value="{{ $v->id }}">{{ $v->store_name ?? $v->name }} (ID: {{ $v->id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Order ID (optional)</label>
                                    <input type="number" name="order_id" class="form-control" placeholder="Enter Order ID">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Payment Method</label>
                                    <input type="text" name="payment_method" class="form-control" value="Bank Transfer">
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Order Amount</label>
                                    <input type="number" step="0.01" name="order_amount" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Commission</label>
                                    <input type="number" step="0.01" name="commission_amount" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Payout Amount</label>
                                    <input type="number" step="0.01" name="payout_amount" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label fw-semibold">Note</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Optional note"></textarea>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="btnSubmitPayout">Create Payout</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#payoutCreateForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const formData = new FormData(this);
        const submitBtn = $('#btnSubmitPayout');
        const originalText = submitBtn.text();

        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message || 'Payout created successfully');
                    window.location.href = "{{ route('vendor.payout') }}";
                } else {
                    toastr.error(response.message || 'Failed to create payout');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).text(originalText);
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        const input = form.find(`[name="${key}"]`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback d-block">${errors[key][0]}</div>`);
                    });
                    toastr.error('Please fix the validation errors.');
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            }
        });
    });
});
</script>
@endpush

