 @extends('backend.layouts.app')
 @section('content')
<div class="page-content">
    <div class="container-fluid">
         <!-- [ breadcrumb ] start -->
         <div class="card">
             <div class="card-header">
                 <div class="page-header">
                     <div class="page-block">
                         <div class="row align-items-center">
                             <div class="col-md-12">

                                 <div class="page-header-title">
                                     <h5 class="mb-0">Tax Rate List</h5>
                                 </div>
                             </div>
                             <div class="col-md-12">
                                 <ul class="breadcrumb mb-0">
                                     <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                                     <li class="breadcrumb-item"><a href="javascript: void(0)">List</a></li>
                                     <li class="breadcrumb-item" aria-current="page">Tax Rate List</li>
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
                        <h5 style="display:inline-block;">Tax Rate List</h5>
                        <a href="{{route('add.tax.rate')}}" class="btn btn-success d-inline-flex" style="float:right;color: white;">
                            <iconify-icon icon="solar:plus-linear" class="me-1"></iconify-icon>Add Tax Rate</a>
                     </div>
                     <div class="card-body table-border-style">
                         <div class="table-responsive">
                             <table class="table datatables" id="pc-dt-filter">
                                 <thead>
                                     <tr>
                                         <th>#</th>
                                         <th>Tax Name</th>
                                         <th>Slug</th>
                                         <th>Tax %</th>
                                         <th>Country</th>
                                         <th>State</th>
                                         <th>Status</th>
                                         <th>Created At</th>
                                         <th>Action</th>
                                     </tr>
                                 </thead>

                                 <tbody>
                                     @forelse($taxRates as $tax)
                                     <tr>
                                         <td>{{ $loop->iteration }}</td>
                                         <td>{{ $tax->name }}</td>
                                         <td>{{ $tax->slug }}</td>
                                         <td>{{ $tax->tax_percentage }}%</td>
                                         <td>{{ $tax->country ?? '—' }}</td>
                                         <td>{{ $tax->state ?? '—' }}</td>
                                         <td>
                                             <span class="badge bg-{{ $tax->is_active ? 'success' : 'danger' }}">
                                                 {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                             </span>
                                         </td>
                                         <td>{{ $tax->created_at->format('d M Y') }}</td>
                                         <td>
                                             <div class="d-flex align-items-center gap-3">
                                                 <a href="{{ route('edit.tax.rate', $tax->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Tax Rate">
                                                     <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                 </a>
                                                 <form action="{{ route('delete.tax.rate') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tax rate?')">
                                                     @csrf
                                                     <input type="hidden" name="id" value="{{ $tax->id }}">
                                                     <button type="submit" class="btn btn-link text-purple p-0 hover-opacity-100" data-bs-toggle="tooltip" title="Delete Tax Rate" style="background: none; border: none;">
                                                         <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                     </button>
                                                 </form>
                                             </div>
                                         </td>
                                     </tr>
                                     @empty
                                     <tr>
                                         <td colspan="9" class="text-center">No tax rates found.</td>
                                     </tr>
                                     @endforelse
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
 <!-- [ Main Content ] end -->
</div>
 @endsection
