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

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Default VAT (%)</label>
                                        <input type="number" step="0.01" name="vat_percent" class="form-control" placeholder="e.g. 5" value="{{ $vatPercent->value ?? '0' }}">
                                        <p class="text-muted small">Standard VAT percentage applied to orders.</p>
                                    </div>

                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-primary">Dynamic Shipping Rates (INR)</h5>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Local (Same City)</label>
                                        <input type="number" step="0.01" name="shipping_local" class="form-control" placeholder="50" value="{{ $shippingLocal->value ?? '50' }}">
                                        <p class="text-muted small">Shipping within the same city.</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Within State</label>
                                        <input type="number" step="0.01" name="shipping_within_state" class="form-control" placeholder="100" value="{{ $shippingWithinState->value ?? '100' }}">
                                        <p class="text-muted small">Shipping within the same state.</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Inter-State / Remote</label>
                                        <input type="number" step="0.01" name="shipping_interstate" class="form-control" placeholder="200" value="{{ $shippingInterstate->value ?? '200' }}">
                                        <p class="text-muted small">Shipping to other states or remote areas.</p>
                                    </div>

                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-primary">Free Delivery Thresholds (Min Order Amount)</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Free Delivery Min Amount (INR)</label>
                                        <input type="number" step="0.01" name="free_delivery_min" class="form-control" placeholder="500" value="{{ $freeDeliveryMin->value ?? '500' }}">
                                        <p class="text-muted small">Free shipping for orders above this amount.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Free Delivery for Metro Cities (INR)</label>
                                        <input type="number" step="0.01" name="free_delivery_min_metro" class="form-control" placeholder="300" value="{{ $freeDeliveryMetro->value ?? '300' }}">
                                        <p class="text-muted small">Free shipping for orders above this amount in metro cities.</p>
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
        </div>
    </div>
</div>

@endsection
