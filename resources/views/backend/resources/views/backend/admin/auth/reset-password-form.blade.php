<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100" data-bs-theme="dark">


<!-- Mirrored from techzaa.in/larkon/admin/auth-signin.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Jan 2026 10:00:14 GMT -->

<head>
    <!-- Title Meta -->
    <meta charset="utf-8" />
    <title>Reset Password | Admin Dashboard </title>
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

                <div class="text-center mb-4">
   <img class="logo auth-logo logo-dark-theme " src="{{ asset('backend/assets/images/logo.png') }}" alt="logo" height="50">
                  <img class="logo auth-logo logo-light-theme " src="{{ asset('backend/assets/images/logo-dark.png') }}" alt="logo" height="50">
                              </div>

              <div class="mb-5 ">
                                    <form action="{{ route('reset.password') }}" method="POST">
                            @csrf

                            <div class="card-body">
                              
                                <h4 class="text-center f-w-500 mb-4">Reset Password</h4>

                                <!-- Email (readonly from session) -->
                                <div class="mb-3">
                                    <input type="email"
                                        class="form-control"
                                        name="email"
                                        value="{{ session('reset_email') }}"
                                        readonly>
                                </div>

                               
                                <!-- New Password -->
                                <div class="mb-3">
                                    <input type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        name="password"
                                        placeholder="New Password">
                                    @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <input type="password"
                                        class="form-control"
                                        name="password_confirmation"
                                        placeholder="Confirm Password">
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary shadow px-sm-4">
                                        Reset Password
                                    </button>
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
