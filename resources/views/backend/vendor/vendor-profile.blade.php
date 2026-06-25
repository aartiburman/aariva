@extends('backend.layouts.app')
@section('content')

<div class="page-content">

     <!-- Start Container Fluid -->
     <div class="container-fluid">

          <!-- Start here.... -->
          <div class="row">
               <div class="col-xl-4 col-lg-5">
                    <div class="card text-center shadow-sm border-0 rounded-4">
                         <div class="card-body p-4">
                              <div class="position-relative d-inline-block mb-3">
                                   <img src="{{ $vendor_data->image ? asset($vendor_data->image) : asset('backend/assets/images/users/avatar-1.jpg') }}" alt="logo" class="img-thumbnail rounded-circle avatar-xl" style="width: 120px; height: 120px; object-fit: cover;">
                                   <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-2 border-white rounded-circle">
                                        <span class="visually-hidden">Online</span>
                                   </span>
                              </div>
                              <h4 class="mb-1 fw-bold">{{ $vendor_data->store_name ?? $vendor_data->name ?? 'Vendor' }}</h4>
                              <p class="text-muted mb-3 fs-13"><iconify-icon icon="solar:shop-linear" class="align-middle me-1"></iconify-icon> {{ $vendor_data->business_name ?? 'Retail Store' }}</p>
                              <p class="text-muted mb-3 fs-13">{{ $vendor_data->vendor_description ?? 'No description available.' }}</p>
                              <div class="row g-2 mb-4">
                                   <div class="col-4">
                                        <div class="p-2 border border-dashed rounded-3 bg-light-subtle">
                                             <h5 class="mb-0 fw-bold fs-14">{{ $products->count() ?? 0 }}</h5>
                                             <small class="text-muted fs-11">Products</small>
                                        </div>
                                   </div>
                                   <div class="col-4">
                                        <div class="p-2 border border-dashed rounded-3 bg-light-subtle">
                                             <h5 class="mb-0 fw-bold fs-14">-</h5>
                                             <small class="text-muted fs-11">Orders</small>
                                        </div>
                                   </div>
                                   <div class="col-4">
                                        <div class="p-2 border border-dashed rounded-3 bg-light-subtle">
                                             <h5 class="mb-0 fw-bold fs-14">4.5</h5>
                                             <small class="text-muted fs-11">Rating</small>
                                        </div>
                                   </div>
                              </div>

                              <div class="text-start border-top pt-3">
                                   <h6 class="text-uppercase fs-12 fw-bold text-muted mb-3">Contact Information</h6>
                                   <p class="mb-2 fs-13 d-flex align-items-center"><iconify-icon icon="solar:user-linear" class="me-2 text-primary fs-18"></iconify-icon> <strong>Owner:</strong> <span class="ms-auto">{{ $vendor_data->owner_name ?? $vendor_data->name ?? '-' }}</span></p>
                                   <p class="mb-2 fs-13 d-flex align-items-center"><iconify-icon icon="solar:letter-linear" class="me-2 text-primary fs-18"></iconify-icon> <strong>Email:</strong> <span class="ms-auto text-truncate" style="max-width: 150px;">{{ $vendor_data->email ?? '-' }}</span></p>
                                   <p class="mb-2 fs-13 d-flex align-items-center"><iconify-icon icon="solar:phone-linear" class="me-2 text-primary fs-18"></iconify-icon> <strong>Phone:</strong> <span class="ms-auto">{{ $vendor_data->phone ?? '-' }}</span></p>
                                   <p class="mb-0 fs-13 d-flex align-items-center"><iconify-icon icon="solar:map-point-linear" class="me-2 text-primary fs-18"></iconify-icon> <strong>Address:</strong> <span class="ms-auto text-end">{{ $vendor_data->address }}, {{ $vendor_data->city->name ?? '' }}, {{ $vendor_data->state->name ?? '' }} {{ $vendor_data->zip }}</span></p>
                              </div>

                              <div class="mt-4 d-grid">
                                   <a href="{{ route('edit.vendor.profile') }}" class="btn btn-primary rounded-3 shadow-sm">
                                        <iconify-icon icon="solar:pen-2-linear" class="align-middle me-1"></iconify-icon> Update Profile
                                   </a>
                              </div>
                         </div>
                    </div>
               </div>

               <div class="col-xl-8 col-lg-7">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                         <div class="card-header bg-light-subtle py-3 px-4">
                              <h5 class="card-title mb-0 fw-bold">Store Overview</h5>
                         </div>
                         <div class="card-body p-4">
                              <div class="row mb-4">
                                   <div class="col-md-6">
                                        <label class="text-muted fs-12 text-uppercase fw-bold mb-1">Business Address</label>
                                        <p class="text-dark fw-medium">{{ $vendor_data->address }}, {{ $vendor_data->city->name ?? '' }}, {{ $vendor_data->state->name ?? '' }} {{ $vendor_data->zip }}</p>
                                   </div>
                                   <div class="col-md-6">
                                        <label class="text-muted fs-12 text-uppercase fw-bold mb-1">Join Date</label>
                                        <p class="text-dark fw-medium">{{ optional($vendor_data->created_at)->format('d M, Y') ?? 'N/A' }}</p>
                                   </div>
                              </div>

                              <hr class="my-4 opacity-50">

                              <div class="d-flex justify-content-between align-items-center mb-3">
                                   <h5 class="fw-bold mb-0">Store Products</h5>
                                   <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $products->count() ?? 0 }} Items</span>
                              </div>

                              @if(!empty($products) && $products->count())
                                   <div class="row g-3">
                                        @foreach($products as $product)
                                             <div class="col-sm-6 col-md-4 col-xl-3">
                                                  <div class="card h-100 border rounded-3 overflow-hidden shadow-none hover-shadow transition-all">
                                                       <div class="position-relative">
                                                            <img src="{{ $product->display_image }}" class="card-img-top" alt="{{ $product->name }}" style="height: 150px; object-fit: cover;">
                                                            @if(!empty($product->discount_percent) && $product->discount_percent > 0)
                                                                 <span class="position-absolute top-0 end-0 m-2 badge bg-danger rounded-pill">-{{ $product->discount_percent }}%</span>
                                                            @elseif(!empty($product->discount_amount) && (float)$product->discount_amount > 0)
                                                                 <span class="position-absolute top-0 end-0 m-2 badge bg-danger rounded-pill">-{{ $currencySymbol }} {{ $product->discount_amount }}</span>
                                                            @endif
                                                       </div>
                                                       <div class="card-body p-2">
                                                            <h6 class="card-title mb-1 text-truncate fs-13" title="{{ $product->name }}">{{ $product->name }}</h6>
                                                            <div class="d-flex align-items-center gap-2">
                                                                 <span class="text-primary fw-bold fs-14">{{ $currencySymbol }} {{ number_format($product->display_price ?? $product->price ?? 0, 2) }}</span>
                                                            </div>
                                                       </div>
                                                  </div>
                                             </div>
                                        @endforeach
                                   </div>
                              @else
                                   <div class="text-center py-5 bg-light rounded-3">
                                        <iconify-icon icon="solar:box-linear" class="fs-48 text-muted mb-2 opacity-25"></iconify-icon>
                                        <p class="text-muted mb-0">No products found for this vendor.</p>
                                   </div>
                              @endif

                         </div>
                    </div>
               </div>
          </div>
		</div>
	</div>


@endsection

