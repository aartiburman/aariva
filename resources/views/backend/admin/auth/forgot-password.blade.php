<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100" data-bs-theme="dark">


<!-- Mirrored from techzaa.in/larkon/admin/auth-signin.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Jan 2026 10:00:14 GMT -->

<head>
  <!-- Title Meta -->
  <meta charset="utf-8" />
  <title>Sign In | Larkon - Responsive Admin Dashboard Template</title>
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
            <div class="col-lg-6 py-lg-3  card">
              <div class="d-flex flex-column h-100 justify-content-center">

                <div class="text-center">
                    <img class="logo auth-logo logo-light-theme" src="{{ $darkLogo }}" alt="logo" height="50">
                    <img class="logo auth-logo logo-dark-theme" src="{{ $lightLogo }}" alt="logo" height="50">
                              </div>

                <!-- <h2 class="fw-bold fs-24 text-center">Forgot Password</h2> -->

                <div class="mb-5 ">
                    @if(session('error'))
                        <div class="alert alert-danger text-center">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger text-center">
                            @foreach($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                    @endif
                  <form action="{{route('send.otp')}}" method="post">
                    @csrf
                    <div class="card-body">

                      <h4 class="text-center f-w-500 mt-4 mb-3">Enter Your Register Email Address</h4>
                      <div class="mb-3">
                        <input type="email" name="username" class="form-control" placeholder="Email Address" autocomplete="off" value="{{ old('username') }}">
                      </div>


                      <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('login') }}" class="btn btn-secondary shadow px-sm-4">Back to Login</a>
                        <button type="submit" class="btn btn-primary shadow px-sm-4">Send Otp</button>
                      </div>
                      <!-- <div class="d-flex justify-content-between align-items-end mt-4">
                  <h6 class="f-w-500 mb-0">Don't have an Account?</h6>
                  <a href="#" class="link-primary">Create Account</a> -->
                    </div>
                </div>
                </form>
              </div>

              <!-- <p class="text-danger text-center">Don't have an account? <a href="auth-signup.html" class="text-dark fw-bold ms-1">Sign Up</a></p> -->
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

</body>


<!-- Mirrored from techzaa.in/larkon/admin/auth-signin.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Jan 2026 10:00:14 GMT -->

</html>
