@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">
                     NCM Logistics Settings
                </h4>
            </div>
            <div class="col-md-6 text-end">
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                            <!-- NCM Settings Form -->
                            <form id="ncmSettingsForm" action="{{ route('ncm.setting.update') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-primary">API Configuration</h5>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">NCM Environment</label>
                                        <select name="ncm_mode" class="form-select">
                                            <option value="sandbox" {{ ($ncmMode->value ?? '') == 'sandbox' ? 'selected' : '' }}>Sandbox / Demo</option>
                                            <option value="production" {{ ($ncmMode->value ?? '') == 'production' ? 'selected' : '' }}>Production</option>
                                        </select>
                                        <p class="text-muted small">Choose 'Sandbox' for testing with demo credentials.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">NCM Demo Token</label>
                                        <input type="text" name="ncm_demo_token" class="form-control" placeholder="0188e3a02adb5d735535830bff20849d54b967ab" value="{{ $ncmDemoToken->value ?? '0188e3a02adb5d735535830bff20849d54b967ab' }}">
                                        <p class="text-muted small">Token for the NCM demo server.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">NCM Production Token</label>
                                        <input type="text" name="ncm_prod_token" class="form-control" placeholder="Enter production token" value="{{ $ncmProdToken->value ?? '' }}">
                                        <p class="text-muted small">Token for the NCM live server.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">NCM Demo URL</label>
                                        <input type="text" name="ncm_demo_url" class="form-control" placeholder="https://demo.nepalcanmove.com" value="{{ $ncmDemoUrl->value ?? 'https://demo.nepalcanmove.com' }}">
                                        <p class="text-muted small">Base URL for NCM demo environment.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">NCM Production URL</label>
                                        <input type="text" name="ncm_prod_url" class="form-control" placeholder="https://nepalcanmove.com" value="{{ $ncmProdUrl->value ?? 'https://nepalcanmove.com' }}">
                                        <p class="text-muted small">Base URL for NCM production environment.</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">NCM Auth Prefix</label>
                                        <select name="ncm_auth_prefix" class="form-control">
                                            <option value="Token" {{ ($ncmAuthPrefix->value ?? 'Token') == 'Token' ? 'selected' : '' }}>Token (Recommended)</option>
                                            <option value="Bearer" {{ ($ncmAuthPrefix->value ?? '') == 'Bearer' ? 'selected' : '' }}>Bearer</option>
                                        </select>
                                        <p class="text-muted small">Choose between 'Token ' or 'Bearer ' prefix for the API.</p>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">NCM Webhook URL</label>
                                        <input type="text" name="ncm_webhook_url" class="form-control" placeholder="https://nepoora.com/admin?orders_status" value="{{ $ncmWebhookUrl->value ?? 'https://nepoora.com/admin?orders_status' }}">
                                        <p class="text-muted small">URL where NCM will send shipment status updates. Recommended: <code>https://nepoora.com/admin?orders_status</code></p>  
                                    </div>

                                    <div class="col-md-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            Update NCM Settings
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
                            <h5 class="mb-0 fw-bold text-dark">NCM Integration</h5>
                        </div>
                        
                        <p class="text-muted fs-14">
                            Configure your Nepal Can Move (NCM) API credentials here to enable automated shipping and tracking.
                        </p>
                        <ul class="text-muted fs-13 ps-3">
                            <li class="mb-2"><strong>Sandbox:</strong> Use for testing. Tracking IDs generated here are not real.</li>
                            <li class="mb-2"><strong>Production:</strong> Live environment. Orders created here will be picked up by NCM.</li>
                            <li class="mb-2"><strong>Auth Prefix:</strong> Most NCM integrations use 'Token'.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection