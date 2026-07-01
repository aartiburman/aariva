@extends('layouts.app')

@section('content')

<style>
    .btn-login {
        background-color: #0F0F10 !important;
        border-color: #0F0F10 !important;
        color: #fff !important;
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        background-color: #C6A75E !important;
        border-color: #C6A75E !important;
        color: #fff !important;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('Login') }}
                </div>

                <div class="card-body">

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        <!-- Hidden fields for Firebase token -->
                        <input type="hidden" name="device_token" id="device_token">
                        <input type="hidden" name="device_type" id="device_type" value="web">

                        <!-- Email -->
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">
                                {{ __('Email Address') }}
                            </label>

                            <div class="col-md-6">
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">
                                {{ __('Password') }}
                            </label>

                            <div class="col-md-6">
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Remember -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="remember"
                                        id="remember"
                                    >

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">

                                <button type="submit" class="btn btn-login px-4">
                                    Login
                                </button>

                                @if (Route::has('password.request'))
                                    <a
                                        class="btn btn-link"
                                        href="{{ route('password.request') }}"
                                    >
                                        Forgot Your Password?
                                    </a>
                                @endif

                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-8 offset-md-4">
                                <hr>
                                <p class="text-muted">Or login with</p>
                                <button type="button" id="googleLoginBtn" class="btn btn-outline-danger px-4">
                                    <i class="fab fa-google me-2"></i> Google Login
                                </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')

<!-- Firebase SDK -->
<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging.js";
import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const googleLoginBtn = document.getElementById("googleLoginBtn");
    const deviceTokenInput = document.getElementById("device_token");
    const deviceTypeInput = document.getElementById("device_type");

    if (!loginForm) return;

    let firebaseApp, messaging, auth;

    // Fetch dynamic config
    fetch('{{ url("api/get-fcm-config") }}')
        .then(response => response.json())
        .then(data => {
            if (data.status && data.firebase_config) {
                firebaseApp = initializeApp(data.firebase_config);
                messaging = getMessaging(firebaseApp);
                auth = getAuth(firebaseApp);

                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register("{{ url('firebase-messaging-sw.js') }}")
                        .then((registration) => {
                            return navigator.serviceWorker.ready.then(() => {
                                return getToken(messaging, { 
                                    vapidKey: data.vapidKey,
                                    serviceWorkerRegistration: registration
                                });
                            });
                        })
                        .then((currentToken) => {
                            if (currentToken) {
                                console.log("FCM TOKEN:", currentToken);
                                deviceTokenInput.value = currentToken;
                                deviceTypeInput.value = "web";
                            }
                        })
                        .catch((err) => console.log("FCM Token error:", err));
                }
            }
        })
        .catch(err => console.log("Failed to get FCM config:", err));

    // Google Login Logic
    if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', function() {
            if (!auth) {
                alert("Firebase not initialized yet. Please wait a moment.");
                return;
            }

            const provider = new GoogleAuthProvider();
            signInWithPopup(auth, provider)
                .then((result) => {
                    const user = result.user;
                    
                    // Send user info to our backend
                    fetch('{{ route("google.login") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            email: user.email,
                            uid: user.uid,
                            name: user.displayName,
                            device_token: deviceTokenInput.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'Google login failed.');
                        }
                    })
                    .catch(err => {
                        console.error('Backend error:', err);
                        alert('Error communicating with server.');
                    });
                })
                .catch((error) => {
                    console.error('Google Auth Error:', error);
                    alert('Google login failed: ' + error.message);
                });
        });
    }

    // AJAX Login Logic
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => {
            if (response.status === 422) {
                return response.json().then(errData => {
                    const message = errData.errors ? Object.values(errData.errors).flat().join('\n') : errData.message;
                    throw new Error(message || 'Validation failed');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Login failed.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(err => {
            console.error('Login error:', err);
            alert(err.message || 'An unexpected error occurred.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
});
</script>

@endsection