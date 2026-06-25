 @extends('backend.layouts.app')
 @section('content')
 <div class="page-content">
               <!-- Start Container Fluid -->
               <div class="container">
                    <div class="row">
                         <div class="col-xl-12">
                              <div class="card">
                                   <div class="card-body">
                                        <h5 class="card-title mb-1 anchor" id="basic">
                                             Basic Example
                                        </h5>
                                       
                                         <div class="alert alert-warning" role="alert">
                  <h5 class="alert-heading"><i class="ph ph-warning-circle me-2"></i>Alert!</h5>
                  <p>Your Password will expire in every 3 months. So change it periodically.</p>
                  <hr>
                  <p class="mb-0">Do not share your password</p>
                </div>
                <div class="card">
                  <div class="card-header">
                    <h5><i class="ph ph-lock-key align-text-bottom text-primary f-20"></i><span class="p-l-5">Change Password</span></h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="mb-3">
                          <label class="form-label">Current Password <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" placeholder="Enter Your Current password">
                          <small class="form-text text-muted">Forgot password? <a href="#!">Click here</a></small>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="mb-3">
                          <label class="form-label">New Password <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" placeholder="Enter New password">
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-3">
                          <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" placeholder="Enter your password again">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card-footer text-end">
                    <button class="btn btn-danger">Change Password</button>
                    <button class="btn btn-outline-dark ms-2">Clear</button>
                  </div>
                </div>
             </div>
             <!-- [ basic-table ] end -->
         </div>
                                   </div>
                              </div>

                          
                         </div> <!-- end col -->

                        
                    </div> <!-- end row -->
               </div>
               <!-- End Container Fluid -->

             
          </div>
 @endsection
