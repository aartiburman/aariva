@if(isset($activeVendorPolicy) && $activeVendorPolicy)
<div class="modal fade" id="vendorPolicyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="vendorPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title d-flex align-items-center" id="vendorPolicyModalLabel">
                    <iconify-icon icon="solar:document-text-bold-duotone" class="me-2 fs-22"></iconify-icon>
                    Vendor Policy Update ({{ $activeVendorPolicy->version }})
                </h5>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-dark">{{ $activeVendorPolicy->title }}</h4>
                    <p class="text-muted small">Last Updated: {{ $activeVendorPolicy->updated_at->format('F d, Y') }}</p>
                </div>
                <div class="policy-content text-dark lh-base" style="font-size: 15px;">
                    {!! $activeVendorPolicy->content !!}
                </div>
                <hr class="my-4">
                <div class="form-check mb-0">
                    <input class="form-check-input border-primary" type="checkbox" id="acceptPolicyCheckbox">
                    <label class="form-check-label fw-semibold text-dark" for="acceptPolicyCheckbox">
                        I have read and agree to the terms and conditions of this vendor policy.
                    </label>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-3">
                <form action="{{ route('vendor.policy.accept') }}" method="POST" id="acceptPolicyForm" class="w-100">
                    @csrf
                    <input type="hidden" name="policy_id" value="{{ $activeVendorPolicy->id }}">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold disabled" id="acceptPolicyBtn">
                        <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                        Accept and Continue to Dashboard
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var policyModal = new bootstrap.Modal(document.getElementById('vendorPolicyModal'));
    policyModal.show();

    $('#acceptPolicyCheckbox').on('change', function() {
        if ($(this).is(':checked')) {
            $('#acceptPolicyBtn').removeClass('disabled');
        } else {
            $('#acceptPolicyBtn').addClass('disabled');
        }
    });

    $('#acceptPolicyForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = $('#acceptPolicyBtn');
        
        if ($('#acceptPolicyCheckbox').is(':checked')) {
            btn.addClass('disabled').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.status) {
                        policyModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Policy Accepted',
                            text: 'Thank you for accepting the updated vendor policy.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function() {
                    btn.removeClass('disabled').html('<iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> Accept and Continue to Dashboard');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.'
                    });
                }
            });
        }
    });
});
</script>
@endpush
@endif