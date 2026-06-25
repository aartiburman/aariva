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
                                     <h5 class="mb-0">Accepted Orders List</h5>
                                 </div>
                             </div>
                             <div class="col-md-12">
                                 <ul class="breadcrumb mb-0">
                                     <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                                     <li class="breadcrumb-item"><a href="javascript: void(0)">List</a></li>
                                     <li class="breadcrumb-item" aria-current="page">Accepted Orders List</li>
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
                 <!-- <div class="card">
                     <div class="card-header">
                         <h5>Inline Form</h5>
                     </div>
                     <div class="card-body">
                         <form class="row row-cols-md-auto g-3 align-items-center">
                             <div class="col-12">
                                 <label class="visually-hidden" for="inlineFormInputName">Name</label>
                                 <input type="text" class="form-control" id="inlineFormInputName" placeholder="Jane Doe">
                             </div>
                             <div class="col-12">
                                 <label class="visually-hidden" for="inlineFormInputGroupUsername">Username</label>
                                 <div class="input-group">
                                     <div class="input-group-text">@</div>
                                     <input type="text" class="form-control" id="inlineFormInputGroupUsername" placeholder="Username">
                                 </div>
                             </div>
                             <div class="col-12">
                                 <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
                                 <select class="form-select" id="inlineFormSelectPref">
                                     <option selected="">Choose...</option>
                                     <option value="1">One</option>
                                     <option value="2">Two</option>
                                     <option value="3">Three</option>
                                 </select>
                             </div>
                             <div class="col-12">
                                 <div class="form-check">
                                     <input class="form-check-input" type="checkbox" id="inlineFormCheck">
                                     <label class="form-check-label" for="inlineFormCheck"> Remember me </label>
                                 </div>
                             </div>
                             <div class="col-12">
                                 <button type="submit" class="btn btn-primary">Submit</button>
                             </div>
                         </form>
                     </div>
                 </div> -->

                 <div class="card">
                     <div class="card-header">
                         <h5 style="display:inline-block;">Accepted Orders List</h5>


                         <a href="{{route('add.subcategory')}}" class="btn btn-success d-inline-flex" style="float:right;color: white;">
                             <i class="ti ti-plus  me-1"></i>Add Accepted Orders</a>
                     </div>
                     <div class="card-body table-border-style">
                         <div class="table-responsive">
                            <table class="table datatables" id="pc-dt-filter">
                                 <thead>
                                     <tr>
                                         <th>#</th>
                                         <th>Order Ref</th>
                                         <th>User</th>
                                         <th>Total</th>
                                         <th>Payment</th>
                                         <th>Order Status</th>
                                         <th>Order Date</th>
                                         <th>Action</th>
                                     </tr>
                                 </thead>

                                 <tbody>
                                     <tr>
                                         <td>1</td>
                                         <td>ORD-10254</td>
                                         <td>Aarti Burman</td>
                                         <td>₹12,450</td>
                                         <td>
                                             <span class="badge bg-success">Paid</span>
                                         </td>
                                         <td>
                                             <span class="badge bg-info">Shipped</span>
                                         </td>
                                         <td>2025-05-05</td>
                                         <td>
                                             <a href="#" class="btn btn-sm btn-primary">
                                                 <i class="ti ti-eye"></i>
                                             </a>
                                         </td>
                                     </tr>
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