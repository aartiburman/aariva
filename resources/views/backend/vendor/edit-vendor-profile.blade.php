@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">My Profile</h4>
                </div>
            </div>
        </div>

        <form action="{{ route('update.vendor.profile') }}" method="POST" enctype="multipart/form-data" class="no-loader">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <label class="form-label fw-bold">Business Categories <span class="text-danger">*</span></label>
                            <select name="category_ids[]" class="form-control" multiple data-choices data-choices-removeItem autocomplete="off">
                                <option value="" disabled>Select Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" @if($vendor_data->category_ids && in_array($category->id, $vendor_data->category_ids)) selected @endif>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_ids')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <p class="text-muted small mt-2 mb-0">Select the categories that best describe your business. This helps customers find your products more easily.</p>
                        </div>
                    </div>
                </div>

                <!-- Left Column -->
                <div class="col-lg-4">
                    <div class="logo-upload-box card">
                        <h6>Store Logo</h6>
                        <div class="logo-preview-wrapper">
                            <img src="{{ $vendor_data->image }}" alt="Store Logo" id="profilePreview">
                        </div>
                        <div class="upload-hint">
                            Allowed *.jpeg, *.jpg, *.png, *.gif<br>
                            Max size of 5 MB
                        </div>
                        <label for="profileImageInput" class="btn btn-sm btn-primary mb-3">
                            <iconify-icon icon="solar:upload-linear" class="me-1"></iconify-icon> Change Logo
                        </label>
                        <input type="file" id="profileImageInput" class="d-none" accept="image/*">

                        <div class="mt-2">
                            @if($vendor_data->isDocumentsVerified())
                            <div class="verified-badge">
                                <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                Account Verified
                            </div>
                            @else
                            <div class="verified-badge bg-warning-subtle text-warning">
                                <iconify-icon icon="solar:info-circle-bold"></iconify-icon>
                                Verification Pending
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h6 class="fw-bold mb-4">Quick Contact</h6>
                            <div class="contact-item">
                                <iconify-icon icon="solar:letter-linear" class="contact-icon"></iconify-icon>
                                <div class="contact-info">
                                    <label>Support Email</label>
                                    <p>{{ $vendor_data->email }}</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <iconify-icon icon="solar:phone-linear" class="contact-icon"></iconify-icon>
                                <div class="contact-info">
                                    <label>Helpline</label>
                                    <p>{{ $vendor_data->phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-8">
                    <!-- General Information -->
                    <div class="card">
                        <div class="card-body">
                            <div class="section-title">
                                <iconify-icon icon="solar:user-id-linear"></iconify-icon>
                                General Information
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $vendor_data->business_name) }}" placeholder="Aether Gadgets UAE">
                                </div>

                                  <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vendor_description">Business Description <span class="text-danger">*</span></label>
                                    <textarea  rows="3" name="vendor_description" id="vendor_description" class="form-control @error('vendor_description') is-invalid @enderror">{{ old('vendor_description', $vendor_data->vendor_description ?? '') }}</textarea>
                                   

                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Owner Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $vendor_data->name) }}" placeholder="Ahmed Al-Sayed">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor_data->phone) }}" placeholder="+971 xx 123 xxxx">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $vendor_data->email) }}" placeholder="ahmed.store@example.com">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Details -->
                    <div class="card">
                        <div class="card-body">
                            <div class="section-title">
                                <iconify-icon icon="solar:map-point-linear"></iconify-icon>
                                Address Details
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $vendor_data->address) }}" placeholder="Office 402, Business Bay, Sheikh Zayed Road">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Country</label>
                                    <select name="country_id" class="form-select country_id" data-selected="{{$vendor_data->country_id}}">
                                        <option value="">Select Country</option>
                                        @foreach($country as $c)
                                        <option value="{{ $c->id }}" {{ $vendor_data->country_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">State</label>
                                    <select name="state_id" class="form-select state_id"  data-selected="{{$vendor_data->state_id}}">
                                        <option value="">Select State</option>
                                       
                                    </select>
                                </div>
                                 <div class="col-md-6">
                                    <label class="form-label">City</label>
                                    <select name="city_id" class="form-select city_id"  data-selected="{{$vendor_data->city_id}}">
                                        <option value="">Select City</option>
                                        
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Zip Code</label>
                                    <input type="text" name="zip" class="form-control" value="{{ old('zip', $vendor_data->zip) }}" placeholder="12345">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payout Frequency -->
                    <div class="card">
                        <div class="card-body">
                            <div class="section-title">
                                <iconify-icon icon="solar:calendar-linear"></iconify-icon>
                                Payout Frequency
                            </div>
                            <p class="text-muted small mb-3">Choose how often you want to receive payouts from your wallet balance.</p>
                            <div class="d-flex flex-wrap gap-4 p-3 border rounded bg-light-subtle">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payout_frequency" id="payout_weekly" value="weekly" {{ old('payout_frequency', $vendor_data->payout_frequency ?? 'monthly') === 'weekly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold cursor-pointer" for="payout_weekly">Weekly</label>
                                    <div class="text-muted small ms-1">Payouts processed every week</div>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payout_frequency" id="payout_biweekly" value="bi-weekly" {{ old('payout_frequency', $vendor_data->payout_frequency ?? 'monthly') === 'bi-weekly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold cursor-pointer" for="payout_biweekly">Bi-weekly</label>
                                    <div class="text-muted small ms-1">Payouts processed every 2 weeks</div>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payout_frequency" id="payout_monthly" value="monthly" {{ old('payout_frequency', $vendor_data->payout_frequency ?? 'monthly') === 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold cursor-pointer" for="payout_monthly">Monthly</label>
                                    <div class="text-muted small ms-1">Payouts processed every month</div>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payout_frequency" id="payout_daily" value="daily" {{ old('payout_frequency', $vendor_data->payout_frequency ?? 'monthly') === 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold cursor-pointer" for="payout_daily">Daily</label>
                                    <div class="text-muted small ms-1">Payouts processed daily per delivered order</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details -->
                    <div class="card">
                        <div class="card-body">
                            <div class="section-title">
                                <iconify-icon icon="solar:bank-linear"></iconify-icon>
                                Bank Details
                            </div>

                            <div class="bank-warning">
                                <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
                                Changing bank details requires admin re-approval for payouts.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $vendor_data->bank_name) }}" placeholder="Emirates NBD">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $vendor_data->account_holder_name) }}" placeholder="Aether Gadgets LLC">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $vendor_data->account_number) }}" placeholder="**** **** **** 1234">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Branch Location <span class="text-danger">*</span></label>
                                    <input type="text" name="branch_location" class="form-control @error('branch_location') is-invalid @enderror" value="{{ old('branch_location', $vendor_data->branch_location) }}" placeholder="AE980234..">
                                    @error('branch_location')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Cancelled Cheque / Bank Proof</label>
                                    <div class="p-3 border bg-light-subtle d-flex align-items-center justify-content-between">
                                        <div class="flex-grow-1">
                                            <input type="file" name="cancelled_cheque" class="form-control form-control-sm border-0 bg-transparent" accept=".jpg,.jpeg,.png,.pdf">
                                            <small class="text-muted d-block mt-1">Upload a clear copy of your cancelled cheque or bank statement.</small>
                                        </div>
                                        @if($vendor_data->cancelled_cheque && !Str::contains($vendor_data->cancelled_cheque, 'no-image.jpg'))
                                        <a href="{{ $vendor_data->cancelled_cheque }}" target="_blank" class="btn btn-sm btn-primary ms-2">
                                            <iconify-icon icon="solar:eye-linear" class="align-middle me-1"></iconify-icon> View
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-end mt-4 mb-5">
                        <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
                // Custom validation for file extension and size
                if (typeof $.validator !== 'undefined') {
                    $.validator.addMethod("extension", function(value, element, param) {
                        param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
                        return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
                    }, "Please upload a valid image file (png, jpg, jpeg, gif).");

                    $.validator.addMethod("filesize", function(value, element, param) {
                        return this.optional(element) || (element.files[0].size <= param);
                    }, "File size must be less than 5MB.");

                    $("form.no-loader").validate({
                        rules: {
                            business_name: {
                                required: true
                            },
                            name: {
                                required: true
                            },
                            phone: {
                                required: true,
                                digits: true,
                                minlength: 10,
                                maxlength: 10
                            },
                            email: {
                                required: true,
                                email: true
                            },
                            image: {
                                extension: "png|jpe?g|gif",
                                filesize: 5 * 1024 * 1024 // 5MB
                            },
                            address: {
                                required: true
                            },
                            country_id: {
                                required: true
                            },
                            city_id: {
                                required: true
                            },
                            branch_location: {
                                required: true
                            },
                            zip: {
                                required: true
                            }
                        },
                        messages: {
                            business_name: "Please enter business name",
                            name: "Please enter owner name",
                            phone: "Please enter a valid 10-digit phone number",
                            email: "Please enter a valid email address",
                            image: {
                                extension: "Only png, jpg, jpeg, and gif files are allowed.",
                                filesize: "Image size must not exceed 5MB."
                            },
                            address: "Please enter street address",
                            country_id: "Please select a country",
                            city_id: "Please select a city",
                            branch_location: "Please enter branch location",
                            zip: "Please enter zip code"
                        },
                        errorElement: 'small',
                        errorPlacement: function(error, element) {
                            error.addClass('text-danger d-block mt-1');
                            element.closest('.col-12, .col-md-6').append(error);
                        },
                        highlight: function(element, errorClass, validClass) {
                            $(element).addClass('is-invalid');
                        },
                        unhighlight: function(element, errorClass, validClass) {
                            $(element).removeClass('is-invalid');
                        }
                    });
                }

                // Preview image before upload
                document.getElementById('profileImageInput').onchange = function(evt) {
                    var tgt = evt.target || window.event.srcElement,
                        files = tgt.files;

                    if (FileReader && files && files.length) {
                        var fr = new FileReader();
                        fr.onload = function() {
                            document.getElementById('profilePreview').src = fr.result;
                        }
                        fr.readAsDataURL(files[0]);

                        // AJAX upload
                        var formData = new FormData();
                        formData.append('image', files[0]);
                        formData.append('_token', '{{ csrf_token() }}');

                        $.ajax({
                            url: "{{ route('update.vendor.logo') }}",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.status) {
                                    toastr.success(response.message);
                                } else {
                                    toastr.error(response.message || 'Error updating logo.');
                                }
                            },
                            error: function(xhr) {
                                let msg = 'Error updating logo.';
                                try {
                                    const res = xhr.responseJSON;
                                    if (res && res.message) msg = res.message;
                                    if (res && res.errors && res.errors.image) msg = res.errors.image[0];
                                } catch (e) {}
                                toastr.error(msg);
                            }
                        });
                    }
                }
            });
</script>
@endpush

@endsection