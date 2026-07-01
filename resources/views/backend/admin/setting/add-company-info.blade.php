@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Company Information</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Aariva Store Company Details</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('company.info.update') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" value="{{ $company_name->value ?? 'Aariva Store Pvt. Ltd.' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="registration_number" class="form-label">Registration Number</label>
                                        <input type="text" class="form-control" id="registration_number" name="registration_number" value="{{ $registration_number->value ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="registered_at" class="form-label">Registered At</label>
                                <input type="text" class="form-control" id="registered_at" name="registered_at" value="{{ $registered_at->value ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label for="registered_office" class="form-label">Registered Office</label>
                                <textarea class="form-control" id="registered_office" name="registered_office" rows="2">{{ $registered_office->value ?? '' }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="branch_office" class="form-label">Branch Office</label>
                                <textarea class="form-control" id="branch_office" name="branch_office" rows="2">{{ $branch_office->value ?? '' }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pan_vat_number" class="form-label">PAN/VAT Number</label>
                                        <input type="text" class="form-control" id="pan_vat_number" name="pan_vat_number" value="{{ $pan_vat_number->value ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_support_email" class="form-label">Customer Support Email</label>
                                        <input type="email" class="form-control" id="customer_support_email" name="customer_support_email" value="{{ $customer_support_email->value ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="docscp_listing_number" class="form-label">DOCSCP Listing Number</label>
                                <input type="text" class="form-control" id="docscp_listing_number" name="docscp_listing_number" value="{{ $docscp_listing_number->value ?? '' }}">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <iconify-icon icon="solar:disk-linear" class="align-middle me-1"></iconify-icon>
                                    Save Company Information
                                </button>
                                <a href="{{ route('general.setting') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
