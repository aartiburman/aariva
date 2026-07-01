@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">
                     General Settings
                </h4>
            </div>
            <div class="col-md-6 text-end">
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                            <!-- General Settings Form -->
                            <form id="generalSettingForm" class="no-loader" action="{{ route('general.setting.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-purple">Website Information</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Website Name</label>
                                        <input type="text" name="website_name" class="form-control" placeholder="Website Name" value="{{ optional($websiteName)->value }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Contact Email</label>
                                        <input type="email" name="contact_email" class="form-control" placeholder="support@example.com" value="{{ optional($contactEmail)->value }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Contact Phone</label>
                                        <input type="text" name="contact_phone" class="form-control" placeholder="+1234567890" value="{{ optional($contactPhone)->value }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Default Currency</label>
                                        <select name="default_currency" class="form-select">
                                            <option value="AED" {{ (optional($defaultCurrency)->value) == 'AED' ? 'selected' : '' }}>AED (United Arab Emirates Dirham)</option>
                                            <option value="USD" {{ (optional($defaultCurrency)->value) == 'USD' ? 'selected' : '' }}>USD (United States Dollar)</option>
                                            <option value="INR" {{ (optional($defaultCurrency)->value) == 'INR' ? 'selected' : '' }}>INR (Indian Rupee)</option>
                                            <!-- Add more currencies as needed -->
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Language</label>
                                         <select name="language" class="form-select">
                                            <option value="en">English</option>
                                            <!-- Add more languages as needed -->
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Timezone</label>
                                        <select name="timezone" class="form-select">
                                            <option value="Asia/Dubai" {{ (optional($timezone)->value) == 'Asia/Dubai' ? 'selected' : '' }}>GMT +4:00 Abu Dhabi, Muscat</option>
                                            <option value="UTC" {{ (optional($timezone)->value) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <!-- Add more timezones as needed -->
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Address</label>
                                        <textarea name="address" class="form-control" rows="2" placeholder="Enter address">{{ optional($address)->value }}</textarea>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Website Logo (Dark)</label>
                                        <input type="file" name="website_logo_dark" class="form-control">
                                        <p class="text-muted small">Recommended for light backgrounds (e.g. Login Page)</p>
                                        @if(isset($websiteLogoDark) && $websiteLogoDark->value)
                                            <div class="mt-2 p-2 bg-light border rounded text-center">
                                                <img src="{{ asset('uploads/settings/' . $websiteLogoDark->value) }}" alt="Logo Dark" class="img-fluid" style="max-height: 50px;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Website Logo (Light)</label>
                                        <input type="file" name="website_logo_light" class="form-control">
                                        <p class="text-muted small">Recommended for dark backgrounds (e.g. Sidebar)</p>
                                        @if(isset($websiteLogoLight) && $websiteLogoLight->value)
                                            <div class="mt-2 p-2 bg-dark border rounded text-center">
                                                <img src="{{ asset('uploads/settings/' . $websiteLogoLight->value) }}" alt="Logo Light" class="img-fluid" style="max-height: 50px;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Favicon</label>
                                        <input type="file" name="favicon" class="form-control">
                                        <p class="text-muted small">Site icon displayed in browser tabs</p>
                                        @if(optional($favicon)->value)
                                            <div class="mt-2 p-2 bg-light border rounded text-center">
                                                <img src="{{ asset('uploads/settings/' . $favicon->value) }}" alt="Favicon" class="img-fluid" style="max-height: 32px;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <hr>
                                    </div>

                                    <!-- Payout Controls Section -->
                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-purple">Payout Controls</h5>
                                        <p class="text-muted small mb-3">Choose which payout frequencies vendors can select on their profile.</p>
                                        
                                        <div class="d-flex flex-wrap gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="payout_frequencies[]" value="weekly" id="payout_weekly" {{ in_array('weekly', $selectedFrequencies) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="payout_weekly">Weekly</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="payout_frequencies[]" value="monthly" id="payout_monthly" {{ in_array('monthly', $selectedFrequencies) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="payout_monthly">Monthly</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="payout_frequencies[]" value="bi-weekly" id="payout_biweekly" {{ in_array('bi-weekly', $selectedFrequencies) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="payout_biweekly">Bi-weekly</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="payout_frequencies[]" value="daily" id="payout_daily" {{ in_array('daily', $selectedFrequencies) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="payout_daily">Daily</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <hr>
                                    </div>

                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-purple">Vendor Settings</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Common Vendor Commission %</label>
                                        <input type="number" step="0.01" name="vendor_commission" class="form-control" placeholder="e.g. 10" value="{{ $commission->value ?? '' }}">
                                        <p class="text-muted small">Commission % applied to all vendors.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Payment Gateway Fee %</label>
                                        <input type="number" step="0.01" name="pg_fee_percent" class="form-control" placeholder="e.g. 2" value="{{ optional($pgFeePercent)->value ?? '0' }}">
                                        <p class="text-muted small">PG fee % deducted from vendor settlement.</p>
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <hr>
                                    </div>

                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-purple">Referral Reward Settings</h5>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-check form-switch form-switch-lg mb-3">
                                            <input type="hidden" name="referral_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="referral_enabled" id="referral_enabled" value="1" {{ (optional($referralEnabled)->value ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold ms-2" for="referral_enabled">Enable Referral Rewards</label>
                                        </div>
                                        <p class="text-muted small">When enabled, referrers and referred users get fixed rewards on first successful order (min cart INR 1000).</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Referrer Reward (INR)</label>
                                        <input type="number" step="0.01" min="0" name="referral_referrer_reward" class="form-control" placeholder="200" value="{{ optional($referralReferrerReward)->value ?? '200' }}">
                                        <p class="text-muted small">Amount credited to referrer on referred user's first order.</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Referred User Reward (INR)</label>
                                        <input type="number" step="0.01" min="0" name="referral_referred_reward" class="form-control" placeholder="100" value="{{ optional($referralReferredReward)->value ?? '100' }}">
                                        <p class="text-muted small">Amount credited to referred user on their first order.</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Minimum Cart Value (INR)</label>
                                        <input type="number" step="1" min="0" name="referral_min_cart_value" class="form-control" placeholder="1000" value="{{ optional($referralMinCart)->value ?? '1000' }}">
                                        <p class="text-muted small">Order must meet this value to trigger referral reward.</p>
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <h5 class="mb-3 text-purple">System Status</h5>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-check form-switch form-switch-lg mb-3">
                                            <input type="hidden" name="maintenance_mode" value="0">
                                            <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" {{ $isMaintenance ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold ms-2" for="maintenance_mode">Enable Maintenance Mode</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Apply Maintenance to Roles</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="maintenance_roles[]" value="2" id="role_vendor" {{ in_array('2', $selectedRoles) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_vendor">Vendors (Role 2)</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="maintenance_roles[]" value="3" id="role_customer" {{ in_array('3', $selectedRoles) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_customer">Customers (Role 3)</label>
                                            </div>
                                        </div>
                                        <p class="text-muted small mt-1">Select which user roles should be affected by the maintenance mode.</p>
                                    </div>

                                    <div class="col-md-12 mb-3" id="custom_url_container">
                                        <label class="form-label fw-bold">Custom Redirect URL (for Role 3 & Guests)</label>
                                        <input type="text" name="maintenance_custom_url" class="form-control" placeholder="e.g. /coming-soon" value="{{ $currentCustomUrl }}">
                                        <p class="text-muted small">If provided, Customers and Guests will be redirected to this URL instead of seeing a 503 page.</p>
                                    </div>

                                    <div class="col-md-12 mt-4 text-end">
                                        <button type="submit" id="saveSettingBtn" class="btn btn-primary px-4">
                                            Save Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm bg-soft-purple">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:info-circle-linear" width="24" class="text-purple"></iconify-icon>
                            <h5 class="mb-0 fw-bold text-dark">Maintenance Mode</h5>
                        </div>
                        
                        <p class="text-muted fs-14">
                            Use maintenance mode when you are performing updates or maintenance on your site.
                        </p>
                        <ul class="text-muted fs-13 ps-3">
                            <li class="mb-2">Admins (Role 1) can still login and access everything.</li>
                            <li class="mb-2">
                                 Vendors (Role 2): 
                                 @if(in_array('2', $selectedRoles))
                                     <span class="text-danger">Affected (Shows 503 Page)</span>
                                 @else
                                     <span class="text-success">Not Affected</span>
                                 @endif
                             </li>
                            <li class="mb-2">
                                 Customers (Role 3) & Guests: 
                                 @if(in_array('3', $selectedRoles))
                                     <span class="text-danger">Affected (Redirects or JSON Error)</span>
                                 @else
                                     <span class="text-success">Not Affected</span>
                                 @endif
                             </li>
                             <li>API requests for affected roles will receive a 503 JSON response.</li>
                        </ul>

                        <div class="mt-4 pt-3 border-top border-purple border-opacity-10">
                            <small class="text-muted d-block mb-2 text-uppercase fw-bold fs-11">Status</small>
                            <div class="d-flex align-items-center gap-2">
                                @if($isMaintenance)
                                    <span class="badge bg-danger">Maintenance Active</span>
                                @else
                                    <span class="badge bg-success">System Online</span>
                                @endif
                            </div>
                        </div>
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
        $(document).on('submit', '#generalSettingForm', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var btn = $('#saveSettingBtn');
            var originalText = btn.html();
            
            // Disable button to prevent double submit
            btn.prop('disabled', true);
            
            // Change text to loading state
            btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');
            
            var formData = new FormData(this);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        // Fallback for non-JSON response or error status
                         Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong!',
                        });
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    var errorMessage = 'Something went wrong!';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                }
            });
        });
    });
</script>
@endpush

