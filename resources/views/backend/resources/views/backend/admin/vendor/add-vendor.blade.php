@extends('backend.layouts.app')
@section('content')

<div class="page-content">
  <!-- Start Container Fluid -->
  <div class="container">
    <div class="row">
      <div class="col-xl-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-3">Add Vendor</h5>

            <form method="POST" action="{{ route('store.vendor') }}" id="VendorForm" enctype="multipart/form-data" autocomplete="off">
              @csrf

              <div class="row">
                <!-- Owner Name -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">Owner Name</label>
                  <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name') }}" placeholder="Enter Owner Name" autocomplete="off">
                  @error('owner_name')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- Store Name -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">Store Name</label>
                  <input type="text" name="store_name" class="form-control @error('store_name') is-invalid @enderror" value="{{ old('store_name') }}" placeholder="Enter Store Name" autocomplete="off">
                  @error('store_name')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- Business Name -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">Business Name</label>
                  <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" placeholder="Enter Business Name" autocomplete="off">
                  @error('business_name')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              </div>
                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vendor_description">Business Description <span class="text-danger">*</span></label>
                                    <textarea  rows="3" name="vendor_description" id="vendor_description" class="form-control @error('vendor_description') is-invalid @enderror">{{ old('vendor_description') }}</textarea>
                                    @error('vendor_description')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

              <div class="row">
                <div class="mb-3 col-md-12">
                  <label class="form-label">Logo</label>
                  <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" autocomplete="off">
                  @error('image')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              </div>

              <div class="row">
                <!-- Email -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" id="email_input" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter Email" autocomplete="new-email">
                  <small id="email_check_msg" class="text-muted"></small>
                  @error('email')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- Phone -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Phone</label>
                  <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Enter Phone Number" autocomplete="off">
                  @error('phone')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              </div>

              <div class="row">
                <!-- Password -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Password</label>
                  <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter Password" autocomplete="new-password">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password">
                      <iconify-icon icon="solar:eye-linear" class="align-middle"></iconify-icon>
                    </button>
                  </div>
                  @error('password')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Confirm Password</label>
                  <div class="input-group">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm Password" autocomplete="new-password">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password_confirmation">
                      <iconify-icon icon="solar:eye-linear" class="align-middle"></iconify-icon>
                    </button>
                  </div>
                </div>
              </div>

              <div class="row">
                <!-- Address -->
                <div class="mb-3 col-md-12">
                  <label class="form-label">Address</label>
                  <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" placeholder="Enter Address">
                  @error('address')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="mb-3 col-md-4">
                  <label class="form-label">Country</label>
                  <select name="country_id" class="form-select @error('country') is-invalid @enderror country_id">
                    <option value="" selected disabled>Select Country</option>
                    @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                  </select>
                  @error('country_id')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <div class="mb-3 col-md-4">
                  <label class="form-label">State</label>
                  <select name="state_id" class="form-select @error('state') is-invalid @enderror state_id">
                    <option value="" selected disabled>Select State</option>
                  </select>
                  @error('state_id')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- City / State / Zip -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">City <span class="text-danger">*</span></label>
                  <select name="city_id" id="city-dd" class="form-select @error('city') is-invalid @enderror city_id">
                    <option value="" selected disabled>Select City</option>
                  </select>
                  @error('city_id')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Zip Code</label>
                  <input type="text" name="zip" class="form-control @error('zip') is-invalid @enderror" value="{{ old('zip') }}" placeholder="Enter Zip Code">
                  @error('zip')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- Categories -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Categories</label>
                  <select name="category_ids[]" class="form-control" multiple data-choices data-choices-removeItem autocomplete="off">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                  </select>
                  @error('category_ids')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              
                <!-- PAN Number -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">PAN Number</label>
                  <input type="text" name="pan_no" class="form-control @error('pan_no') is-invalid @enderror" value="{{ old('pan_no') }}" placeholder="Enter PAN Number">
                  @error('pan_no')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- VAT/Tax Number -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">VAT / Tax Number</label>
                  <input type="text" name="vat_or_tax" class="form-control @error('vat_or_tax') is-invalid @enderror" value="{{ old('vat_or_tax') }}" placeholder="Enter VAT or Tax Number">
                  @error('vat_or_tax')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- Bank Name -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">Bank Name</label>
                  <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" placeholder="Enter Bank Name">
                  @error('bank_name')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              </div>

              <div class="row">
                <!-- Account Number -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">Account Number</label>
                  <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number') }}" placeholder="Enter Account Number">
                  @error('account_number')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
                
                <!-- Account Holder Name -->
                <div class="mb-3 col-md-4">
                  <label class="form-label">Account Holder Name</label>
                  <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ old('account_holder_name') }}" placeholder="Enter Account Holder Name">
                  @error('account_holder_name')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <!-- IFSC Code -->
                <!-- <div class="mb-3 col-md-4">
                  <label class="form-label">IFSC / SWIFT Code</label>
                  <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror" value="{{ old('ifsc_code') }}" placeholder="Enter IFSC/SWIFT Code">
                  @error('ifsc_code')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
              </div> -->

              <!-- Vendor Status -->
                <!-- <div class="mb-3 col-md-6">
                  <label class="form-label">Vendor Status</label>
                  <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="" selected disabled>Select Status</option>
                    <option value="0">Pending</option>
                    <option value="1">Approved</option>
                    <option value="2">Rejected</option>
                  </select>
                  @error('status')
                  <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div> -->
              </div>

              <!-- Agreement -->
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="agreement" id="agreement">
                  <label class="form-check-label" for="agreement">
                    I confirm vendor details are correct
                  </label>
                </div>
              </div>



              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('vendors.list') }}" class="btn border-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Vendor</button>
              </div>

            </form>
          </div>
        </div>


      </div> <!-- end col -->


    </div> <!-- end row -->
  </div>
  <!-- End Container Fluid -->


</div>

@endsection