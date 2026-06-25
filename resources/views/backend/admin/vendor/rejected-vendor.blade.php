 @extends('backend.layouts.app')
 @section('content')
 <div class="page-content">

     <!-- Start Container Fluid -->
     <div class="container">
         <div class="row mb-4">
             <div class="col-12">
                 <div class="d-flex align-items-center gap-2">
                     <a href="{{ url()->previous() }}" class="btn btn-sm btn-light d-flex align-items-center gap-1">
                         <iconify-icon icon="solar:arrow-left-linear" class="fs-18"></iconify-icon> Back
                     </a>
                     <h4 class="mb-0 text-dark fw-bold">Vendor Management</h4>
                 </div>
             </div>
         </div>
         <div class="row">

             <div class="col-xl-12">
                 <div class="card">
                     <div class="card-body">
                         <h5 class="card-title mb-1 anchor" id="basic">
                             Rejected Vendor 
                         </h5>
                         <div class="table-responsive">
                            @include('backend.admin.vendor.partials.vendor_table', ['vendors' => $vendor_data])
                        </div>

                     </div>
                 </div>


             </div> <!-- end col -->

         </div>
         <!-- end row -->
     </div>

     @endsection
