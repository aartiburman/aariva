@extends('backend.layouts.app')

@section('content')
 <div class="page-content">
               <!-- Start Container Fluid -->
               <div class="container">
                    <div class="row">
                         <div class="col-xl-12">
                              <div class="card">
                                   <div class="card-body">
                                        <h5 class="card-title mb-1 anchor mb-4"  id="basic">
                                             Change Password
                                        </h5>
                                       
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
                                            <input id="current_password" type="password" class="form-control" name="current_password" required autofocus>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="new_password">New Password</label>
                                            <input id="new_password" type="password" class="form-control" name="new_password" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="new_password_confirmation">Confirm New Password</label>
                                            <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" required>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Change Password</button>
                                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                   </div>
                              </div>

                          
                         </div> <!-- end col -->

                        
                    </div> <!-- end row -->
               </div>
               <!-- End Container Fluid -->

             
          </div>

@endsection
