@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <!-- Start Container Fluid -->
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                 <div class="page-title page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="page-title">Edit Vendor</h4>
                     <div class="page-title-right">
                        <div class="col-auto">
                        <a href="{{ route('vendors.list') }}" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                            Back to List
                        </a>
                    </div>
                </div>
                <div class="card">
                   
                <div class="card-body">
                    <form id="vendorEditForm" class="no-loader" action="{{ route('vendor.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="vender_uqid" value="{{ $vendor->id }}">
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-lg-12">
                                <h5 class="mb-3">Basic Information</h5>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                                            <input type="text" name="owner_name" class="form-control" placeholder="Enter Owner Name" value="{{ old('owner_name', $vendor->name) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Store Name <span class="text-danger">*</span></label>
                                            <input type="text" name="store_name" class="form-control" placeholder="Enter Store Name" value="{{ old('store_name', $vendor->store_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" placeholder="Enter Email" value="{{ old('email', $vendor->email) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                            <input type="text" name="phone" class="form-control" placeholder="Enter Phone" value="{{ old('phone', $vendor->phone) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">New Password <span class="text-muted">(Leave blank to keep current)</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password" class="form-control" id="password" placeholder="Enter New Password">
                                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                                    <iconify-icon icon="solar:eye-linear" class="align-middle"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Confirm New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm New Password">
                                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                                    <iconify-icon icon="solar:eye-linear" class="align-middle"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Store Categories & Logo -->
                            <div class="col-lg-12 mt-4">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5 class="mb-3">Store Categories <span class="text-danger">*</span></h5>
                                        <div class="mb-3">
                                            @php $selectedCats = is_array($vendor->category_ids) ? $vendor->category_ids : json_decode($vendor->category_ids, true) ?? []; @endphp
                                            <select name="category_ids[]" class="form-control" multiple data-choices data-choices-removeItem data-placeholder="Choose categories">
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCats) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <h5 class="mb-3">Store Logo</h5>
                                        <div class="mb-3 d-flex align-items-center gap-3">
                                            @if($vendor->logo)
                                                <img src="{{ $vendor->logo }}" alt="logo" class="img-thumbnail" style="height: 60px; width: 60px; object-fit: cover;">
                                            @endif
                                            <div class="flex-grow-1">
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                                <p class="text-muted small mt-1 mb-0">Recommended size: 200x200px. Max: 2MB</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Details -->
                            <div class="col-lg-12 mt-4">
                                <h5 class="mb-3">Business & Bank Details</h5>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Business Name <span class="text-danger">*</span></label>
                                            <input type="text" name="business_name" class="form-control" placeholder="Legal Business Name" value="{{ old('business_name', $vendor->business_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">PAN Number <span class="text-danger">*</span></label>
                                            <input type="text" name="pan_no" class="form-control" placeholder="PAN/Tax ID" value="{{ old('pan_no', $vendor->pan_no) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">GST/VAT Number <span class="text-danger">*</span></label>
                                            <input type="text" name="vendor_tax" class="form-control" placeholder="GST/VAT/Tax No" value="{{ old('vendor_tax', $vendor->vendor_tax) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                            <input type="text" name="bank_name" class="form-control" placeholder="Bank Name" value="{{ old('bank_name', $vendor->bank_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                            <input type="text" name="account_holder_name" class="form-control" placeholder="Account Holder Name" value="{{ old('account_holder_name', $vendor->account_holder_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                            <input type="text" name="account_number" class="form-control" placeholder="Account Number" value="{{ old('account_number', $vendor->account_number) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">Branch Location <span class="text-danger">*</span></label>
                                            <input type="text" name="branch_location" class="form-control" placeholder="Branch Location" value="{{ old('branch_location', $vendor->branch_location) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="col-lg-12 mt-4">
                                <h5 class="mb-3">Address Information</h5>
                                <div class="row">
                                   
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">Country <span class="text-danger">*</span></label>
                                            <select name="country_id" class="form-control" data-choices data-placeholder="Select Country">
                                                <option value="">Select Country</option>
                                                @foreach($countries as $c)
                                                    <option value="{{ $c->id }}" {{ $vendor->country_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">State <span class="text-danger">*</span></label>
                                            <select name="state_id" class="form-control" data-choices data-placeholder="Select State">
                                                <option value="">Select State</option>
                                                @foreach($states as $s)
                                                    <option value="{{ $s->id }}" {{ $vendor->state_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">City <span class="text-danger">*</span></label>
                                            <select name="city_id" class="form-control" data-choices data-placeholder="Select City">
                                                <option value="">Select City</option>
                                                @foreach($cities as $ct)
                                                    <option value="{{ $ct->id }}" {{ $vendor->city_id == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">ZIP Code <span class="text-danger">*</span></label>
                                            <input type="text" name="zip" class="form-control" placeholder="Zip" value="{{ old('zip', $vendor->zip) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                            <textarea name="address" class="form-control" rows="2" placeholder="Full Business Address">{{ old('address', $vendor->address) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('vendors.list') }}" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-primary" id="btnUpdateVendor">Update Vendor</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Container Fluid -->


</div>


@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2();
        $('.select2-multiple').select2();
    }

    // Toggle Password Visibility
    $('.toggle-password').on('click', function() {
        const input = $(this).siblings('input');
        const icon = $(this).find('iconify-icon');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.attr('icon', 'solar:eye-closed-linear');
        } else {
            input.attr('type', 'password');
            icon.attr('icon', 'solar:eye-linear');
        }
    });

    $('#vendorEditForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const formData = new FormData(this);
        const submitBtn = $('#btnUpdateVendor');
        const originalText = submitBtn.text();

        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message || 'Vendor updated successfully');
                    window.location.href = "{{ route('vendors.list') }}";
                } else {
                    toastr.error(response.message || 'Failed to update vendor');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).text(originalText);
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        let input = form.find(`[name="${key}"], [name="${key}[]"]`);
                        input.addClass('is-invalid');
                        
                        if (input.closest('.choices').length) {
                            input.closest('.choices').after(`<div class="invalid-feedback d-block">${errors[key][0]}</div>`);
                            input.closest('.choices').find('.choices__inner').addClass('border-danger');
                        } else {
                            input.after(`<div class="invalid-feedback d-block">${errors[key][0]}</div>`);
                        }
                    });
                    toastr.error('Please fix the validation errors.');
                    
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 100
                    }, 500);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            }
        });
    });
});
</script>
@endpush

