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
                             Pending Vendor 
                         </h5>
                         <div class="table-responsive">
                             <div class="table-responsive">
                                 <table class="table align-middle table-nowrap table-hover mb-0">
                                     <thead class="bg-light-subtle">
                                        <tr>
                                            <th class="ps-4">Vendor</th>
                                            <th>Contact</th>
                                            <th style="width: 250px;">Location</th>
                                            <th>Commission</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($vendor_data) && $vendor_data->count() > 0)
                                        @foreach($vendor_data as $vendor)
                                        <tr id="row_{{ $vendor->id }}">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-md bg-light rounded d-flex align-items-center justify-content-center p-1">
                                                        <img src="{{ $vendor->logo }}" alt="" class="img-fluid rounded">
                                                    </div>
                                                    <div>
                                                        <h5 class="fs-14 mb-1"><a href="{{ route('vendor.detail', $vendor->uqid) }}" class="text-dark fw-bold">{{ Str::limit($vendor->store_name, 200) }}</a></h5>
                                                        <p class="text-muted mb-0 fs-12">{{ Str::limit($vendor->name, 200) }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1 fs-13 text-dark fw-medium">{{ $vendor->email }}</p>
                                                <p class="text-muted mb-0 fs-12">{{ $vendor->phone }}</p>
                                            </td>
                                            <td>
                                                <p class="mb-1 fs-13 text-dark fw-medium text-wrap" style="max-width: 250px;">{{ \Illuminate\Support\Str::limit($vendor->address, 200) }}</p>
                                                <p class="text-muted mb-0 fs-12">{{ $vendor->city_name }}, b {{ $vendor->country_name }}</p>
                                            </td>
                                             <td>
                                                 <span class="fw-bold text-dark">10%</span>
                                             </td>
                                             <td>
                                                 @if($vendor->status == 1)
                                                 <span class="badge bg-success-subtle text-success py-1 px-2 fs-11 text-uppercase">Approved</span>
                                                 @elseif($vendor->status == 0)
                                                 <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-11 text-uppercase">Pending</span>
                                                 @else
                                                 <span class="badge bg-danger-subtle text-danger py-1 px-2 fs-11 text-uppercase">Rejected</span>
                                                 @endif
                                             </td>
                                             <td class="text-center">
                                                 <div class="d-flex align-items-center justify-content-center gap-2">
                                                     <a href="{{ route('vendor.detail', $vendor->uqid) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Details">
                                                         <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
                                                     </a>
                                                     <a href="{{ route('vendor.edit', $vendor->uqid) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Vendor">
                                                         <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                     </a>
                                                     <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-vendor" data-id="{{ $vendor->id }}" data-bs-toggle="tooltip" title="Delete Vendor">
                                                         <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                     </a>
                                                 </div>
                                             </td>
                                         </tr>
                                         @endforeach
                                         @else
                                         <tr>
                                             <td colspan="6" class="text-center py-4 text-muted">No vendors found.</td>
                                         </tr>
                                         @endif
                                     </tbody>
                                 </table>
                             </div>

                         </div>

                     </div>
                 </div>


             </div> <!-- end col -->

         </div>
         <!-- end row -->
     </div>

     @endsection
