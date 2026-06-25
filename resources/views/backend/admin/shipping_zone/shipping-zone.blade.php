 @extends('backend.layouts.app')
 @section('content')

<!-- [ Main Content ] start -->
 <section class="pc-container">
     <div class="pc-content">
         <!-- [ breadcrumb ] start -->
         <div class="card">
             <div class="card-header">
                 <div class="page-header">
                     <div class="page-block">
                         <div class="row align-items-center">
                             <div class="col-md-12">
                                 <div class="page-header-title">
                                     <h5 class="mb-0">Shipping Zone</h5>
                                 </div>
                             </div>
                             <div class="col-md-12">
                                 <ul class="breadcrumb mb-0">
                                     <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                                     <li class="breadcrumb-item"><a href="javascript: void(0)">Shipping & Tax</a></li>
                                     <li class="breadcrumb-item" aria-current="page">Shipping Zone</li>
                                 </ul>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <!-- [ breadcrumb ] end -->



         <div class="row">
             <!-- [ basic-table ] start -->
             <div class="col-xl-12">
               

                 <div class="card">
                     <div class="card-header">
                         <h5 style="display:inline-block;">Shipping Zone</h5>
                         <a href="{{route('add.shipping.zone')}}" class="btn btn-success d-inline-flex" style="float:right;color: white;">
                            <iconify-icon icon="solar:plus-linear" class="me-1"></iconify-icon>Add Shipping Zone</a>
                     </div>
                     <div class="card-body table-border-style">
                        <div class="table-responsive">
                           <table class="table datatables" id="pc-dt-filter">
                                <thead>
                                    <tr>
                                        <th>
                                            <label class="custom-checkbox">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                            </label>
                                        </th>
                                        <th>Zone Name</th>
                                        <th>Slug</th>
                                        <th>Delivery Charge</th>
                                        <th>Free Shipping Above</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {{-- Example Row --}}
                                    <tr>
                                        <td>
                                            <label class="custom-checkbox">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                            </label>
                                        </td>

                                        <td>North India</td>
                                        <td>north-india</td>

                                        <td>₹100.00</td>

                                        <td>₹999.00</td>

                                        <td>
                                            <span class="badge bg-success">Active</span>
                                        </td>

                                        <td>2025-05-05</td>

                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="#" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Shipping Zone">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="#" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Delete Shipping Zone">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- @foreach($shippingZones as $zone) --}}
                                </tbody>
                            </table>

                        </div>
                    </div>
                 </div>
             </div>
             <!-- [ basic-table ] end -->
         </div>
         <!-- [ Main Content ] end -->
     </div>
 </section>
 <!-- [ Main Content ] end -->

 @endsection

