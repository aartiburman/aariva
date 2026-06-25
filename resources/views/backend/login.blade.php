<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100" data-bs-theme="light">


<!-- Mirrored from techzaa.in/larkon/admin/auth-signin.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Jan 2026 10:00:14 GMT -->

<head>
  <!-- Title Meta -->
  <meta charset="utf-8" />
  <title>Sign In - {{ $siteName }} | Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A fully responsive premium admin dashboard template" />
  <meta name="author" content="Techzaa" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- App favicon -->
  <link rel="shortcut icon" href="{{ $siteFavicon ? asset('uploads/settings/'.$siteFavicon) : asset('backend/assets/images/favicon.ico') }}">

  <!-- Vendor css (Require in all Page) -->
  <link href="{{ asset('backend/assets/css/vendor.min.css')}}" rel="stylesheet" type="text/css" />

  <!-- Icons css (Require in all Page) -->
  <link href="{{ asset('backend/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />

  <!-- App css (Require in all Page) -->
  <link href="{{ asset('backend/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />

  <!-- Custom css -->
  <link href="{{ asset('backend/assets/css/custom.css')}}" rel="stylesheet" type="text/css" />

  <!-- Theme Config js (Require in all Page) -->
  <script src="{{ asset('backend/assets/js/config.js')}}"></script>
</head>

<body class="h-100">
  <div class="d-flex flex-column h-100 p-3">
    <div class="d-flex flex-column flex-grow-1">
      <div class="row h-100">
        <div class="col-xxl-12">
          <div class="row justify-content-center h-100 ">
            <div class="col-lg-6 py-lg-3 card">

              <div class="d-flex flex-column h-100 justify-content-center" style="padding: 0 50px 0 50px;">

                <div class="text-center mb-4">
                  

                    <img class="logo auth-logo logo-light-theme" src="{{ $darkLogo }}" alt="logo" height="50">
                    <img class="logo auth-logo logo-dark-theme" src="{{ $lightLogo }}" alt="logo" height="50">
              
                </div>

                <!-- <h2 class="fw-bold fs-24 text-center">Sign In</h2>

                <p class="text-muted mt-1 mb-4 text-center">Enter your email address and password to access admin panel.</p> -->

                <div class="mb-5 ">
                  <form action="{{ route('do.login') }}" method="post" class="authentication-form" id="login-form">
                    @csrf
                    <input type="hidden" name="device_token" id="device_token">

                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger">
                      <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                    @endif
                    <div class="mb-3 ">
                      <label class="form-label" for="example-email">Email</label>
                      <input type="email"
                        name="username"
                        class="form-control @error('username') is-invalid @enderror"
                        placeholder="Email Address"
                        value="{{ old('username') }}">
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="example-password">Password</label>
                      <div class="position-relative">
                        <input type="password"
                          name="password"
                          id="password-input"
                          class="form-control @error('password') is-invalid @enderror"
                          placeholder="Password">
                        <span class="position-absolute top-50 end-0 translate-middle-y me-2 cursor-pointer" id="password-toggle">
                          <iconify-icon icon="solar:eye-linear" id="password-eye-icon" class="fs-18"></iconify-icon>
                        </span>
                      </div>
                                            <a href="{{ route('forgot.password') }}" class="float-end text-muted text-unline-dashed ms-1">Forgot Password?</a>

                    </div>
                    <div class="mb-3">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="checkbox-signin">
                        <label class="form-check-label" for="checkbox-signin">Remember me</label>
                      </div>
                    </div>

                     <div class="mb-1 text-center d-grid">
                      <button class="btn btn-primary" type="submit">Sign In</button>
                    </div>

                    <!-- <div class="mb-1 text-center d-flex gap-2 mt-2">
                      <button class="btn btn-outline-danger w-100" type="button" id="google-signin">
                        <iconify-icon icon="logos:google-icon" class="fs-18 me-2"></iconify-icon> Google
                      </button>
                      <button class="btn btn-outline-info w-100" type="button" onclick="loginWithFacebook()">
                        <iconify-icon icon="logos:facebook" class="fs-18 me-2"></iconify-icon> Facebook
                      </button>
                    </div> -->

                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>


      </div>
    </div>
  </div>

  <!-- Vendor Javascript (Require in all Page) -->
  <script src="{{ asset('backend/assets/js/vendor.js')}}"></script>

  <!-- App Javascript (Require in all Page) -->
  <script src="{{ asset('backend/assets/js/app.js')}}"></script>

  <script>
    // Clear cookies and cache on page load
    window.onload = function() {
      // Clear all cookies
      var cookies = document.cookie.split(";");
      for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        var eqPos = cookie.indexOf("=");
        var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
      }

      // Use Cache Storage API to clear cache if available
      if ('caches' in window) {
        caches.keys().then(function(names) {
          for (let name of names) caches.delete(name);
        });
      }

      // Force reload from server on next navigation if needed
      if (window.location.hash === '#cleared') {
        // Already cleared
      } else {
        // Optional: window.location.hash = 'cleared';
        // window.location.reload(true);
      }
    };

    // Password toggle logic
    document.getElementById('password-toggle').addEventListener('click', function() {
      const passwordInput = document.getElementById('password-input');
      const eyeIcon = document.getElementById('password-eye-icon');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.setAttribute('icon', 'solar:eye-closed-linear');
      } else {
        passwordInput.type = 'password';
        eyeIcon.setAttribute('icon', 'solar:eye-linear');
      }
    });
  </script>

</body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<!-- Mirrored from techzaa.in/larkon/admin/auth-signin.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Jan 2026 10:00:14 GMT -->
<script>
  // Base URL and CSRF for static/public scripts
  window.BaseUrl = "{{ url('') }}";
  window.csrf = "{{ csrf_token() }}";

  // Initialize the Facebook JavaScript SDK
  window.fbAsyncInit = function () { 
    FB.init({ 
      appId: "{{ config('services.facebook.client_id') }}", 
      xfbml: true, 
      version: 'v22.0', 
    }); 
  }; 

  // Load the Facebook JavaScript SDK asynchronously 
  (function (d, s, id) { 
    var js, fjs = d.getElementsByTagName(s)[0]; 
    if (d.getElementById(id)) return; 
    js = d.createElement(s); js.id = id; 
    js.src = "https://connect.facebook.net/en_US/sdk.js"; 
    fjs.parentNode.insertBefore(js, fjs); 
  })(document, "script", "facebook-jssdk"); 

  // Function to handle Facebook Login using the SDK
  function loginWithFacebook() { 
    FB.login(function (response) { 
      if (response.authResponse) { 
        console.log("Welcome! Fetching your information.... "); 
        // After successful login, fetch user information
        FB.api("/me", { fields: "name, email" }, function (apiResponse) { 
          handleSocialLogin({
            uid: response.authResponse.userID,
            email: apiResponse.email,
            displayName: apiResponse.name
          }, '/facebook-login');
        }); 
      } else { 
        console.log("User cancelled login or did not fully authorize."); 
      } 
    }, {scope: 'public_profile,email'}); 
  } 

  // Shared function to send user info to backend
  function handleSocialLogin(user, endpoint) {
    const deviceToken = document.getElementById('device_token').value;
    $.ajax({
      url: window.BaseUrl + endpoint,
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': window.csrf
      },
      data: JSON.stringify({ 
        uid: user.uid, 
        email: user.email, 
        name: user.displayName,
        device_token: deviceToken
      }),
      contentType: 'application/json',
      success: function(data) {
        if (data.success) {
          window.location.href = data.redirect;
        } else {
          alert(data.message);
        }
      },
      error: function(xhr, status, error) {
        console.error("AJAX error:", status, error);
        alert("An error occurred during login. Please try again.");
      }
    });
  }
</script>
<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.12.0/firebase-analytics.js";
  import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/12.12.0/firebase-auth.js";
  import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/12.12.0/firebase-messaging.js";

  // Your web app's Firebase configuration
  const firebaseConfig = {
    apiKey: "{{ $notificationSetting->firebase_api_key ?? '' }}",
    authDomain: "{{ $notificationSetting->firebase_auth_domain ?? '' }}",
    projectId: "{{ $notificationSetting->firebase_project_id ?? '' }}",
    storageBucket: "{{ $notificationSetting->firebase_storage_bucket ?? '' }}",
    messagingSenderId: "{{ $notificationSetting->firebase_messaging_sender_id ?? '' }}",
    appId: "{{ $notificationSetting->firebase_app_id ?? '' }}",
    measurementId: "{{ $notificationSetting->measurementId ?? '' }}",
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
  const auth = getAuth(app);
  const messaging = getMessaging(app);
  const googleProvider = new GoogleAuthProvider();

  // Get FCM Token
  async function requestPermission() {
    try {
      const permission = await Notification.requestPermission();
      if (permission === 'granted') {
        if ('serviceWorker' in navigator) {
          const registration = await navigator.serviceWorker.register("{{ url('firebase-messaging-sw.js') }}");
          // Wait for service worker to be ready
          await navigator.serviceWorker.ready;
          
          const token = await getToken(messaging, {
            vapidKey: "{{ $notificationSetting->fcm_vapid_key ?? '' }}",
            serviceWorkerRegistration: registration
          });
          if (token) {
            document.getElementById('device_token').value = token;
            console.log('FCM Token generated:', token);
          } else {
            console.warn('No registration token available. Request permission to generate one.');
          }
        }
      } else {
        console.warn('Unable to get permission to notify.');
      }
    } catch (err) {
      console.error('An error occurred while retrieving token. ', err);
    }
  }

  // Initialize token request
  requestPermission();

  // Function to handle Google Login
  async function signInWithGoogle() {
    try {
      const result = await signInWithPopup(auth, googleProvider);
      handleSocialLogin({
        uid: result.user.uid,
        email: result.user.email,
        displayName: result.user.displayName
      }, '/google-login');
    } catch (error) {
      console.error("Error during Google login:", error.code, error.message);
      if (error.code === 'auth/operation-not-allowed') {
        alert("Google Login is not enabled in Firebase Console. Please enable it under Authentication > Sign-in method.");
      } else {
        alert("Google login failed: " + error.message);
      }
    }
  }

  // Attach event listeners
  document.getElementById('google-signin').addEventListener('click', signInWithGoogle);
</script>

</html>