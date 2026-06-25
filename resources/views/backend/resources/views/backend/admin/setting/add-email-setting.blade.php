@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">
                     Email Settings
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                            <!-- Email Settings Form -->
                            <form action="{{ route('email.setting.update') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <h5 class="mb-3 text-purple">SMTP Configuration</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mail Driver</label>
                                        <input type="text" name="mail_driver" class="form-control" value="{{ $email->mail_driver ?? 'smtp' }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mail Host</label>
                                        <input type="text" name="mail_host" class="form-control" value="{{ $email->mail_host ?? '' }}" placeholder="smtp.gmail.com" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mail Port</label>
                                        <input type="text" name="mail_port" class="form-control" value="{{ $email->mail_port ?? '' }}" placeholder="587" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mail Encryption</label>
                                        <select name="mail_encryption" class="form-select">
                                            <option value="tls" {{ ($email->mail_encryption ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ ($email->mail_encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mail Username</label>
                                        <input type="text" name="mail_username" class="form-control" value="{{ $email->mail_username ?? '' }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mail Password</label>
                                        <div class="input-group">
                                            <input type="password" name="mail_password" class="form-control" id="mail_password" value="{{ $email->mail_password ?? '' }}" required>
                                            <button class="btn btn-outline-secondary password-toggle-btn" type="button" data-target="#mail_password" aria-label="Toggle password visibility">
                                                <iconify-icon icon="solar:eye-linear"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <hr class="my-2 opacity-10">
                                        <h5 class="mb-3 text-purple">Sender Information</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">From Address</label>
                                        <input type="email" name="mail_from_address" class="form-control" value="{{ $email->mail_from_address ?? '' }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">From Name</label>
                                        <input type="text" name="mail_from_name" class="form-control" value="{{ $email->mail_from_name ?? '' }}" required>
                                    </div>

                                    <div class="col-md-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            Update Email Settings
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

