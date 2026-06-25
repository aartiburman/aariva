@extends('backend.layouts.app')
@section('content')

<div class="page-content">
    <!-- Start Container Fluid -->
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1 anchor mb-3" id="basic">
                            Update Vendor
                        </h5>

                        <form method="POST"
                            action="{{ route('vendor.update') }}"
                            id="VendorEditForm"
                            enctype="multipart/form-data"
                            autocomplete="off">

                            @csrf
                            {{-- hidden vendor id --}}
                            <input type="hidden" name="vender_uqid" value="{{ $vendor->id }}">

                            <div class="row">

                                <!-- Owner Name -->
                                <div class="mb-4 col-md-4">
                                    <label class="form-label" for="owner_name">Owner Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="owner_name"
                                        id="owner_name"
                                        class="form-control @error('owner_name') is-invalid @enderror"
                                        value="{{ old('owner_name', $vendor->name) }}"
                                        autocomplete="off">
                                    @error('owner_name')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Store Name -->
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="store_name">Store Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="store_name"
                                        id="store_name"
                                        class="form-control @error('store_name') is-invalid @enderror"
                                        value="{{ old('store_name', $vendor->store_name) }}"
                                        autocomplete="off">
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="business_name">Business Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                        name="business_name" 
                                        id="business_name"
                                        class="form-control @error('business_name') is-invalid @enderror" 
                                        value="{{ old('business_name',$vendor->business_name ?? $vendor->store_name) }}"
                                        autocomplete="off">
                                    @error('business_name')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vendor_description">Business Description <span class="text-danger">*</span></label>
                                    <textarea  rows="3" name="vendor_description" id="vendor_description" class="form-control @error('vendor_description') is-invalid @enderror">{{ old('vendor_description', $vendor->vendor_description ?? '') }}</textarea>
                                    @error('vendor_description')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                 <!-- Categories -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Categories</label>
                                    <select name="category_ids[]" class="form-control" multiple data-choices data-choices-removeItem autocomplete="off">
                                        <option value="" disabled>Select Categories</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @if($vendor->category_ids && in_array($category->id, $vendor->category_ids)) selected @endif>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_ids')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <!-- Logo -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="image">Logo</label>
                                    <input type="file"
                                        name="image"
                                        id="image"
                                        class="form-control @error('image') is-invalid @enderror"
                                        accept="image/*"
                                        autocomplete="off">

                                    @if($vendor->image)
                                    <div class="mt-2">
                                        <img src="{{$vendor->logo }}"
                                            width="60"
                                            class="rounded border">
                                    </div>
                                    @endif
                                </div>

                                <!-- Email -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email"
                                        name="email"
                                        id="email_input"
                                        class="form-control"
                                        value="{{ old('email', $vendor->email) }}"
                                        autocomplete="new-email">
                                    <small id="email_check_msg" class="text-muted"></small>
                                </div>

                                <!-- Phone -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control"
                                        value="{{ old('phone', $vendor->phone) }}"
                                        autocomplete="off">
                                </div>



                            </div>

                            <!-- Address -->
                            <div class="mb-3">
                                <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                                <input type="text"
                                    name="address"
                                    id="address"
                                    class="form-control"
                                    value="{{ old('address', $vendor->address) }}">
                            </div>

                            <!-- City / State / Zip -->
                            <div class="row">
                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="country_id">Country <span class="text-danger">*</span></label>
                                    <select name="country_id" id="country_id" class="form-select @error('country') is-invalid @enderror country_id" data-selected="{{ $vendor->country_id }}">
                                        <option value="" selected disabled>Select country</option>
                                        @foreach($countries as $country)
                                        <option @if($country->id == old('country_id', $vendor->country_id)) selected @endif value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="state_id">State <span class="text-danger">*</span></label>
                                    <select name="state_id" id="state_id" class="form-select @error('state') is-invalid @enderror state_id" data-selected="{{ $vendor->state_id }}">
                                        <option value="" selected disabled>Select state</option>
                                        @foreach ($states as $value)
                                        <option @if($value->id == old('state_id', $vendor->state_id)) selected @endif value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_id')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- City / State / Zip -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="city_id">City <span class="text-danger">*</span></label>
                                    <select name="city_id" id="city_id" class="form-select @error('city') is-invalid @enderror city_id" data-selected="{{$vendor->city_id }}">
                                        <option value="" selected disabled>Select city</option>
                                        @foreach ($cities as $value)
                                        <option @if($value->id == old('city_id', $vendor->city_id)) selected @endif value="{{ $value->id }}">{{ $value->name }}</option>

                                        @endforeach
                                    </select>
                                    @error('city_id')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                           
                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="zip">Zip Code <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="zip"
                                        id="zip"
                                        class="form-control"
                                        value="{{ old('zip', $vendor->zip) }}">
                                </div>

                               
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="pan_no">PAN Number <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="pan_no"
                                        id="pan_no"
                                        class="form-control @error('pan_no') is-invalid @enderror"
                                        value="{{ old('pan_no', $vendor->pan_no) }}"
                                        placeholder="Enter PAN Number">
                                    @error('pan_no')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vat_or_tax">VAT / Tax Number <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="vat_or_tax"
                                        id="vat_or_tax"
                                        class="form-control @error('vat_or_tax') is-invalid @enderror"
                                        value="{{ old('vat_or_tax', $vendor->vat_or_tax) }}"
                                        placeholder="Enter VAT or Tax Number">
                                    @error('vat_or_tax')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="bank_name">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="bank_name"
                                        id="bank_name"
                                        class="form-control @error('bank_name') is-invalid @enderror"
                                        value="{{ old('bank_name', $vendor->bank_name) }}"
                                        placeholder="Enter Bank Name">
                                    @error('bank_name')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="account_number">Account Number <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="account_number"
                                        id="account_number"
                                        class="form-control @error('account_number') is-invalid @enderror"
                                        value="{{ old('account_number', $vendor->account_number) }}"
                                        placeholder="Enter Account Number">
                                    @error('account_number')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="account_holder_name">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="account_holder_name"
                                        id="account_holder_name"
                                        class="form-control @error('account_holder_name') is-invalid @enderror"
                                        value="{{ old('account_holder_name', $vendor->account_holder_name) }}"
                                        placeholder="Enter Account Holder Name">
                                    @error('account_holder_name')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                            </div>

                           

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('vendors.list') }}" class="btn border-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Vendor</button>
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

