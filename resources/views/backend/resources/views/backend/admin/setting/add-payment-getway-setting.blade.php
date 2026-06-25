@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">Edit {{ $gateway->name }} Settings</h4>
            </div>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ route('payment.getway.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $gateway->id }}">

                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Gateway Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $gateway->name }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="1" {{ $gateway->status ? 'selected' : '' }}>Enabled</option>
                                            <option value="0" {{ !$gateway->status ? 'selected' : '' }}>Disabled</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Environment Mode</label>
                                        <select name="mode" class="form-select">
                                            <option value="sandbox" {{ $gateway->mode == 'sandbox' ? 'selected' : '' }}>Sandbox / Test</option>
                                            <option value="live" {{ $gateway->mode == 'live' ? 'selected' : '' }}>Live / Production</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <hr class="my-2 opacity-10">
                                        <h5 class="mb-3 text-purple">API Credentials</h5>
                                    </div>

                                    @if($gateway->slug == 'khalti')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Live Public Key</label>
                                            <input type="text" name="live_public_key" class="form-control" value="{{ $gateway->live_public_key }}" placeholder="Live Public Key">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Live Secret Key</label>
                                            <input type="password" name="live_secret_key" class="form-control" value="{{ $gateway->live_secret_key }}" placeholder="Live Secret Key">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Test Public Key</label>
                                            <input type="text" name="test_public_key" class="form-control" value="{{ $gateway->test_public_key }}" placeholder="Test Public Key">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Test Secret Key</label>
                                            <input type="password" name="test_secret_key" class="form-control" value="{{ $gateway->test_secret_key }}" placeholder="Test Secret Key">
                                        </div>
                                    </div>
                                    @elseif($gateway->slug == 'esewa')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Live Merchant ID</label>
                                            <input type="text" name="live_public_key" class="form-control" value="{{ $gateway->live_public_key }}" placeholder="Live Merchant ID">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Live Secret Key</label>
                                            <input type="password" name="live_secret_key" class="form-control" value="{{ $gateway->live_secret_key }}" placeholder="Live Secret Key">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Test Merchant ID</label>
                                            <input type="text" name="test_public_key" class="form-control" value="{{ $gateway->test_public_key }}" placeholder="Test Merchant ID (e.g. EPAYTEST)">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Test Secret Key</label>
                                            <input type="password" name="test_secret_key" class="form-control" value="{{ $gateway->test_secret_key }}" placeholder="Test Secret Key">
                                        </div>
                                    </div>
                                    @elseif($gateway->slug == 'uae_payment')
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Merchant ID</label>
                                        <input type="text" name="merchant_id" class="form-control" value="{{ $gateway->merchant_id }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">App ID / Store ID</label>
                                        <input type="text" name="app_id" class="form-control" value="{{ $gateway->app_id }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Secret Key / API Key</label>
                                        <input type="password" name="secret_key" class="form-control" value="{{ $gateway->secret_key }}">
                                    </div>
                                    @elseif($gateway->slug == 'paypal')
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">PayPal Client ID</label>
                                        <input type="text" name="public_key" class="form-control" value="{{ $gateway->public_key }}" placeholder="PayPal Client ID">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">PayPal Secret Key</label>
                                        <input type="password" name="secret_key" class="form-control" value="{{ $gateway->secret_key }}" placeholder="PayPal Secret Key">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">PayPal App ID (Optional)</label>
                                        <input type="text" name="app_id" class="form-control" value="{{ $gateway->app_id }}" placeholder="PayPal App ID">
                                    </div>
                                    @elseif($gateway->slug == 'applepay')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Live Merchant Identifier</label>
                                            <input type="text" name="live_public_key" class="form-control" value="{{ $gateway->live_public_key }}" placeholder="merchant.com.your.bundle">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Live Merchant Name</label>
                                            <input type="text" name="live_secret_key" class="form-control" value="{{ $gateway->live_secret_key }}" placeholder="Your Store Name">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Test Merchant Identifier</label>
                                            <input type="text" name="test_public_key" class="form-control" value="{{ $gateway->test_public_key }}" placeholder="merchant.test.your.bundle">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Test Merchant Name</label>
                                            <input type="text" name="test_secret_key" class="form-control" value="{{ $gateway->test_secret_key }}" placeholder="Test Store Name">
                                        </div>
                                    </div>
                                    @else
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Public Key</label>
                                        <input type="text" name="public_key" class="form-control" value="{{ $gateway->public_key }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Secret Key</label>
                                        <input type="password" name="secret_key" class="form-control" value="{{ $gateway->secret_key }}">
                                    </div>
                                    @endif

                                    <div class="col-md-12">
                                        <hr class="my-2 opacity-10">
                                        <h5 class="mb-3 text-purple">Base URLs</h5>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Sandbox Base URL</label>
                                        <input type="text" name="sandbox_base_url" class="form-control" value="{{ $gateway->sandbox_base_url }}" placeholder="Sandbox API Base URL">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Live Base URL</label>
                                        <input type="text" name="live_base_url" class="form-control" value="{{ $gateway->live_base_url }}" placeholder="Live API Base URL">
                                    </div>
                                    <div class="col-md-12">
                                        <hr class="my-2 opacity-10">
                                        <h5 class="mb-3 text-purple">Callback URLs</h5>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Success URL</label>
                                        <input type="text" name="success_url" class="form-control" value="{{ $gateway->success_url }}" placeholder="Success Callback URL">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Failure URL</label>
                                        <input type="text" name="failure_url" class="form-control" value="{{ $gateway->failure_url }}" placeholder="Failure Callback URL">
                                    </div>

                                    <div class="col-md-12 mt-4 text-end">
                                        <a href="{{ route('payment.getway.setting') }}" class="btn btn-light px-4 me-2">Cancel</a>
                                        <button type="submit" class="btn btn-primary px-4">
                                            Update Settings
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
                                <h5 class="mb-0 fw-bold text-dark">Integration Info</h5>
                            </div>
                            <p class="text-muted fs-14">
                                Ensure you are using the correct credentials for the selected <strong>Environment Mode</strong>.
                            </p>
                            <ul class="text-muted fs-13 ps-3">
                                <li class="mb-2"><strong>Sandbox:</strong> Use test keys provided by the gateway for development.</li>
                                <li><strong>Live:</strong> Use production keys for real transactions.</li>
                            </ul>
                            <div class="mt-4 pt-3 border-top border-purple border-opacity-10">
                                <small class="text-muted d-block mb-2 text-uppercase fw-bold fs-11">Documentation Links</small>
                                <div class="d-flex flex-column gap-2">
                                    @if($gateway->slug == 'khalti')
                                    <a href="https://docs.khalti.com/" target="_blank" class="text-purple fs-13 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:link-linear"></iconify-icon> Khalti API Docs
                                    </a>
                                    @elseif($gateway->slug == 'esewa')
                                    <a href="https://developer.esewa.com.np/" target="_blank" class="text-purple fs-13 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:link-linear"></iconify-icon> eSewa Developer Portal
                                    </a>
                                    @elseif($gateway->slug == 'paypal')
                                    <a href="https://developer.paypal.com/docs/checkout/" target="_blank" class="text-purple fs-13 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:link-linear"></iconify-icon> PayPal Developer Docs
                                    </a>
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