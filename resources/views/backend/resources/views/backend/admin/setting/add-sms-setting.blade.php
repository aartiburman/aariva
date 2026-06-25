﻿@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">
                    SMS Settings
                </h4>
            </div>
           
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                      
                            <!-- SMS Settings Form -->
                            <form action="{{ route('sms.setting.update') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-purple">SMS Gateway Configuration</h5>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">SMS Gateway</label>
                                        <select name="sms_gateway" class="form-select">
                                            <option value="twilio" {{ ($sms->sms_gateway ?? '') == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                            <option value="nexmo" {{ ($sms->sms_gateway ?? '') == 'nexmo' ? 'selected' : '' }}>Nexmo (Vonage)</option>
                                            <option value="infobip" {{ ($sms->sms_gateway ?? '') == 'infobip' ? 'selected' : '' }}>Infobip</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">API Key / SID</label>
                                        <input type="text" name="api_key" class="form-control" value="{{ $sms->api_key ?? '' }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">API Secret / Auth Token</label>
                                        <input type="password" name="api_secret" class="form-control" value="{{ $sms->api_secret ?? '' }}" required>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">From Number / Sender ID</label>
                                        <input type="text" name="from_number" class="form-control" value="{{ $sms->from_number ?? '' }}" required>
                                    </div>

                                    <div class="col-md-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            Update SMS Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm bg-soft-primary">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:info-circle-linear" width="24" class="text-primary"></iconify-icon>
                            <h5 class="mb-0 fw-bold text-dark">Setup Instructions</h5>
                        </div>
                        
                        @if(isset($email))
                            <p class="text-muted fs-14">
                                Configure your SMTP settings to enable email notifications for orders, password resets, and more.
                            </p>
                            <ul class="text-muted fs-13 ps-3">
                                <li class="mb-2">Use <strong>smtp.gmail.com</strong> for Gmail with an App Password.</li>
                                <li class="mb-2">Port <strong>587</strong> is recommended for TLS encryption.</li>
                                <li>Ensure your firewall allows outgoing connections on the specified port.</li>
                            </ul>
                        @else
                            <p class="text-muted fs-14">
                                Connect your preferred SMS gateway to send transaction alerts and OTPs to users.
                            </p>
                            <ul class="text-muted fs-13 ps-3">
                                <li class="mb-2">Get your <strong>API Credentials</strong> from your gateway provider dashboard.</li>
                                <li class="mb-2">The <strong>From Number</strong> should be in E.164 format (e.g., +1234567890).</li>
                                <li>Check your gateway balance to ensure message delivery.</li>
                            </ul>
                        @endif

                        <div class="mt-4 pt-3 border-top border-primary border-opacity-10">
                            <small class="text-muted d-block mb-2 text-uppercase fw-bold fs-11">Quick Links</small>
                            <div class="d-flex flex-column gap-2">
                                @if(isset($email))
                                    <a href="https://support.google.com/mail/answer/185833" target="_blank" class="text-purple fs-13 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:link-linear"></iconify-icon> Gmail App Passwords
                                    </a>
                                @else
                                    <a href="https://www.twilio.com/docs/sms" target="_blank" class="text-purple fs-13 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:link-linear"></iconify-icon> Twilio SMS Docs
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection

