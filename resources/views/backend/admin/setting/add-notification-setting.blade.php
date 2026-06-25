@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <h4 class="fw-bold mb-0">
                    Notification Settings
                </h4>
            </div>
          
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                      
                            <!-- Notification Settings Form -->
                            <form action="{{ route('notification.setting.update') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-12 mt-4">
                                        <h5 class="mb-3 text-purple">Firebase / FCM Configuration</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">FCM Server Key</label>
                                        <input type="password" name="fcm_server_key" class="form-control" value="{{ $notification->fcm_server_key ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">FCM Sender ID</label>
                                        <input type="text" name="fcm_sender_id" class="form-control" value="{{ $notification->fcm_sender_id ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Firebase API Key</label>
                                        <input type="text" name="firebase_api_key" class="form-control" value="{{ $notification->firebase_api_key ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Firebase Auth Domain</label>
                                        <input type="text" name="firebase_auth_domain" class="form-control" value="{{ $notification->firebase_auth_domain ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Firebase Project ID</label>
                                        <input type="text" name="firebase_project_id" class="form-control" value="{{ $notification->firebase_project_id ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Firebase Storage Bucket</label>
                                        <input type="text" name="firebase_storage_bucket" class="form-control" value="{{ $notification->firebase_storage_bucket ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Firebase Messaging Sender ID</label>
                                        <input type="text" name="firebase_messaging_sender_id" class="form-control" value="{{ $notification->firebase_messaging_sender_id ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Firebase App ID</label>
                                        <input type="text" name="firebase_app_id" class="form-control" value="{{ $notification->firebase_app_id ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Firebase Measurement ID (Optional)</label>
                                        <input type="text" name="measurementId" class="form-control" value="{{ $notification->measurementId ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">FCM VAPID Key</label>
                                        <input type="text" name="fcm_vapid_key" class="form-control" value="{{ $notification->fcm_vapid_key ?? '' }}">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Firebase Service Account JSON (For FCM v1)</label>
                                        <textarea name="firebase_service_account" class="form-control" rows="5" placeholder='{"type": "service_account", ...}'>{{ $notificationSetting->firebase_service_account ?? '' }}</textarea>
                                        <p class="text-muted small">Paste the content of your Firebase service account JSON file here. This is required for sending push notifications using the modern FCM v1 API.</p>
                                    </div>

                                    <div class="col-md-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            Update Notification Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-soft-purple">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:info-circle-linear" width="24" class="text-purple"></iconify-icon>
                            <h5 class="mb-0 fw-bold text-dark">Setup Instructions</h5>
                        </div>
                        
                        <p class="text-muted fs-14">
                            Configure your Push Notification settings to enable real-time alerts for users on web and mobile.
                        </p>
                        <ul class="text-muted fs-13 ps-3">
                            <li class="mb-2"><strong>Firebase (FCM)</strong> is required for Android/iOS push notifications.</li>
                            <li>Ensure you have the correct API keys from your service provider dashboards.</li>
                        </ul>

                        <div class="mt-4 pt-3 border-top border-purple border-opacity-10">
                            <small class="text-muted d-block mb-2 text-uppercase fw-bold fs-11">Quick Links</small>
                            <div class="d-flex flex-column gap-2">
                                <a href="https://console.firebase.google.com/" target="_blank" class="text-purple fs-13 d-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:link-linear"></iconify-icon> Firebase Console
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection

