@extends('backend.layouts.app')

@section('content')
<div class="page-content">

     <!-- Start Container Fluid -->
     <div class="container-fluid">
          <!-- Featured Stats Cards -->
          <div class="row g-2 mb-4">
   
            <div class="col-12 col-md-12">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">Change Password</div>

                                <div class="card-body">
                                    @if(session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
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

                                    <form method="POST" action="{{ route('vendor.update.password') }}">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <label for="current_password">Current Password</label>
                                            <div class="input-group">
                                                <input id="current_password" type="password" class="form-control" name="current_password" required autofocus>
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#current_password">
                                                    <iconify-icon icon="solar:eye-linear" class="align-middle"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="new_password">New Password</label>
                                            <div class="input-group">
                                                <input id="new_password" type="password" class="form-control" name="new_password" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password">
                                                    <iconify-icon icon="solar:eye-linear" class="align-middle"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="new_password_confirmation">Confirm New Password</label>
                                            <div class="input-group">
                                                <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password_confirmation">
                                                    <iconify-icon icon="solar:eye-linear" class="align-middle"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Change Password</button>
                                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
