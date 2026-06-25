@if(isset($activeVendorPolicy) && $activeVendorPolicy)
<div class="modal fade" id="vendorPolicyModal" tabindex="-1" aria-labelledby="vendorPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header border-0 bg-primary text-white py-3 px-4 d-flex align-items-center justify-content-between">
                <h5 class="modal-title d-flex align-items-center text-white fs-18 fw-bold mb-0" id="vendorPolicyModalLabel">
                    <iconify-icon icon="solar:document-text-bold-duotone" class="me-2 fs-24"></iconify-icon>
                    Vendor Policy Update ({{ $activeVendorPolicy->version }})
                </h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="policy-header text-center py-4 bg-light-subtle border-bottom">
                    <h4 class="fw-bold text-dark mb-1">{{ $activeVendorPolicy->title }}</h4>
                    <p class="text-muted small mb-0">
                        <iconify-icon icon="solar:calendar-minimalistic-bold-duotone" class="align-middle me-1"></iconify-icon>
                        Last Updated: {{ $activeVendorPolicy->updated_at->format('F d, Y') }}
                    </p>
                </div>
                <div class="policy-content-wrapper p-4">
                    <div class="policy-content text-dark lh-base px-2 custom-scrollbar" style="font-size: 15px; text-align: justify; max-height: 400px; overflow-y: auto;">
                        {!! $activeVendorPolicy->content !!}
                    </div>
                </div>
                
                <div class="px-4 pb-4">
                    <div class="acceptance-box p-3 border rounded-3 bg-light">
                        <div class="form-check mb-0">
                            <input class="form-check-input border-primary shadow-none cursor-pointer" type="checkbox" id="acceptPolicyCheckbox" style="width: 20px; height: 20px;">
                            <label class="form-check-label fw-semibold text-dark ms-2 cursor-pointer" for="acceptPolicyCheckbox">
                                I have read and agree to the terms and conditions of this vendor policy.
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <form action="{{ route('vendor.policy.accept') }}" method="POST" id="acceptPolicyForm" class="w-100">
                    @csrf
                    <input type="hidden" name="policy_id" value="{{ $activeVendorPolicy->id }}">
                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 disabled no-loader" id="acceptPolicyBtn" style="border-radius: 10px; font-size: 16px; transition: all 0.3s;">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20"></iconify-icon>
                        Accept and Continue to Dashboard
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #999;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    #vendorPolicyModal .modal-content {
        animation: modalFadeIn 0.3s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #acceptPolicyBtn:not(.disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(var(--bs-primary-rgb), 0.3) !important;
    }
</style>

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
            btn.addClass('disabled').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...');
            
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
                    btn.removeClass('disabled').html('<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20"></iconify-icon> Accept and Continue to Dashboard');
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