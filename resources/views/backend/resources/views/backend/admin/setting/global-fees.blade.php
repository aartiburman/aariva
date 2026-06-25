@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">
                     Global Fees & Commissions
                </h4>
            </div>
            <div class="col-md-6 text-end">
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                            <!-- Global Fees Form -->
                            <form id="globalFeesForm" action="{{ route('global.fees.update') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-primary">Tax & Shipping Settings</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Default VAT (%)</label>
                                        <input type="number" step="0.01" name="vat_percent" class="form-control" placeholder="e.g. 5" value="{{ $vatPercent->value ?? '0' }}">
                                        <p class="text-muted small">Standard VAT percentage applied to orders.</p>
                                    </div>

                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-primary">Dynamic Shipping Rates (NPR)</h5>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Inside Butwal City</label>
                                        <input type="number" step="0.01" name="shipping_inside_butwal" class="form-control" placeholder="100" value="{{ $shippingButwal->value ?? '100' }}">
                                        <p class="text-muted small">Shipping for orders within Butwal.</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Major Cities (KTM/Pokhara)</label>
                                        <input type="number" step="0.01" name="shipping_major_cities" class="form-control" placeholder="150" value="{{ $shippingMajor->value ?? '150' }}">
                                        <p class="text-muted small">Kathmandu, Pokhara, etc.</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Remote Areas</label>
                                        <input type="number" step="0.01" name="shipping_remote_areas" class="form-control" placeholder="200" value="{{ $shippingRemote->value ?? '200' }}">
                                        <p class="text-muted small">All other regions in Nepal.</p>
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <hr>
                                    </div>

                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-primary">Vendor Commission Settings</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Common Vendor Commission (%)</label>
                                        <input type="number" step="0.01" name="vendor_commission" class="form-control" placeholder="e.g. 10" value="{{ $commission->value ?? '0' }}">
                                        <p class="text-muted small">Platform commission deducted from vendor sales.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Payment Gateway Fee (%)</label>
                                        <input type="number" step="0.01" name="pg_fee_percent" class="form-control" placeholder="e.g. 2" value="{{ $pgFeePercent->value ?? '0' }}">
                                        <p class="text-muted small">Transaction processing fee deducted from settlements.</p>
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <hr>
                                    </div>

                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-primary">NCM Logistics Settings</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">NCM API Key</label>
                                        <input type="text" name="ncm_api_key" class="form-control" placeholder="e.g. your_api_key" value="{{ $ncmApiKey->value ?? '' }}">
                                        <p class="text-muted small">Your unique API key from NCM.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">NCM API URL</label>
                                        <input type="text" name="ncm_api_url" class="form-control" placeholder="e.g. https://api.nepalcanmove.com" value="{{ $ncmApiUrl->value ?? 'https://api.nepalcanmove.com' }}">
                                        <p class="text-muted small">NCM API endpoint base URL.</p>
                                    </div>

                                    <div class="col-md-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            Update Global Fees
                                        </button>
                                    </div>
                                </div>
                            </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:info-circle-linear" width="24" class="text-primary"></iconify-icon>
                            <h5 class="mb-0 fw-bold text-dark">Pricing Information</h5>
                        </div>
                        
                        <p class="text-muted fs-14">
                            These settings define the default fees and commissions across the Nepoora platform.
                        </p>
                        <ul class="text-muted fs-13 ps-3">
                            <li class="mb-2"><strong>VAT:</strong> Applied during checkout based on the subtotal.</li>
                            <li class="mb-2"><strong>Shipping Fee:</strong> A flat rate added to every order for delivery.</li>
                            <li class="mb-2"><strong>Commission:</strong> The percentage the platform takes from each item sold.</li>
                            <li class="mb-2"><strong>PG Fee:</strong> The cost of processing payments via external gateways.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
