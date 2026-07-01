@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">WhatsApp Settings</h4>
                    <div class="page-title-right">
                        <a href="{{ route('whatsapp.messages') }}" class="btn btn-sm btn-outline-primary">Message Log</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">API Configuration</h5></div>
                    <div class="card-body">
                        <form action="{{ route('whatsapp.settings.update') }}" method="POST">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">WhatsApp Cloud API URL</label>
                                    <input type="text" name="api_url" class="form-control" value="{{ $settings['api_url'] ?? '' }}" placeholder="https://graph.facebook.com/v18.0/">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">API Token</label>
                                    <input type="password" name="api_token" class="form-control" value="{{ $settings['api_token'] ?? '' }}" placeholder="Permanent or temporary token">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number ID</label>
                                    <input type="text" name="phone_number_id" class="form-control" value="{{ $settings['phone_number_id'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Business Account ID</label>
                                    <input type="text" name="business_account_id" class="form-control" value="{{ $settings['business_account_id'] ?? '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="wa_active" {{ ($settings['is_active'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium" for="wa_active">Enable WhatsApp Automation</label>
                                </div>
                            </div>
                            <hr>
                            <h6>Notification Templates</h6>
                            <p class="text-muted small">These will be used when sending order notifications via WhatsApp.</p>
                            <div class="mb-3">
                                <label class="form-label">Order Confirmation Template</label>
                                <input type="text" name="order_confirmation_template" class="form-control" value="{{ $settings['order_confirmation_template'] ?? '' }}" placeholder="e.g. order_confirmation">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order Shipped Template</label>
                                <input type="text" name="order_shipped_template" class="form-control" value="{{ $settings['order_shipped_template'] ?? '' }}" placeholder="e.g. order_shipped">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order Delivered Template</label>
                                <input type="text" name="order_delivered_template" class="form-control" value="{{ $settings['order_delivered_template'] ?? '' }}" placeholder="e.g. order_delivered">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Test Connection</h5></div>
                    <div class="card-body">
                        <form action="{{ route('whatsapp.test.send') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Recipient Phone</label>
                                <input type="text" name="recipient" class="form-control" placeholder="e.g. 97798XXXXXXXX" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Test Message</label>
                                <textarea name="message" class="form-control" rows="3" required>Test message from Aariva admin</textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100">Send Test Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
