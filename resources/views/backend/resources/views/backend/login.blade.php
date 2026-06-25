<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100" data-bs-theme="light">


<!-- Mirrored from techzaa.in/larkon/admin/auth-signin.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Jan 2026 10:00:14 GMT -->

<head>
  <!-- Title Meta -->
  <meta charset="utf-8" />
  <title>Sign In - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A fully responsive premium admin dashboard template" />
  <meta name="author" content="Techzaa" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- App favicon -->
  <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico')}}">

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
                  

                    <img class="logo auth-logo logo-dark-theme " src="{{ asset('backend/assets/images/logo.png') }}" alt="logo" height="50">
                  <img class="logo auth-logo logo-light-theme " src="{{ asset('backend/assets/images/logo-dark.png') }}" alt="logo" height="50">
              
                </div>

                <!-- <h2 class="fw-bold fs-24 text-center">Sign In</h2>

                <p class="text-muted mt-1 mb-4 text-center">Enter your email address and password to access admin panel.</p> -->

                <div class="mb-5 ">
                  <form action="{{ route('do.login') }}" method="post" class="authentication-form">
                    @csrf

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

                    <!-- <div class="mb-1 text-center d-grid mt-2">
                      <button class="btn btn-outline-danger" type="button" id="google-signin">
                        <iconify-icon icon="logos:google-icon" class="fs-18 me-2"></iconify-icon> Sign in with Google
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
</script>
<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.10.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.10.0/firebase-analytics.js";
  import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/12.10.0/firebase-auth.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyCBozqKSO6IqmmHVlRvTVQYtQV7RIgGpUY",
    authDomain: "nepoora-auth.firebaseapp.com",
    projectId: "nepoora-auth",
    storageBucket: "nepoora-auth.firebasestorage.app",
    messagingSenderId: "288333381789",
    appId: "1:288333381789:web:e8d02fd0f0f899cb729474",
    measurementId: "G-W0MZC761Q3"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
  const auth = getAuth(app);
  const provider = new GoogleAuthProvider();

  // Function to handle Google Login
  async function signInWithGoogle() {
    try {
      const result = await signInWithPopup(auth, provider);
      // The signed-in user info.
      const user = result.user;
      console.log("Firebase user:", user);
      
      // Send user info to backend with jQuery
      $.ajax({
        url: BaseUrl + '/google-login',
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf
        },
        data: JSON.stringify({ 
          uid: user.uid, 
          email: user.email, 
          name: user.displayName 
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

    } catch (error) {
      console.error("Error during Google login:", error.message);
      alert("Google login failed: " + error.message);
    }
  }

  // Attach event listener to the button
  document.getElementById('google-signin').addEventListener('click', signInWithGoogle);
</script>

</html>