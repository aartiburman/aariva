@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    @if(isset($vendor))
    <div class="container-xxl">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('vendors.list') }}">Vendors</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Vendor Details</li>
                    </ol>
                </nav>
                <h3 class="mb-0 fw-bold">{{ $vendor->store_name }} <span class="text-muted fs-14 fw-normal">#{{ $vendor->uqid }}</span></h3>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.edit', $vendor->uqid) }}" class="btn btn-primary d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:pen-linear" class="fs-18"></iconify-icon>
                        Edit Vendor
                    </a>
                    <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Overview Row -->
        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="avatar-sm bg-soft-primary rounded mb-3">
                            <iconify-icon icon="solar:bill-list-bold-duotone" class="avatar-title fs-24 text-primary"></iconify-icon>
                        </div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Sales</h6>
                        <h4 class="mb-0 fw-bold">{{ $vendor->currency ?? 'INR' }} {{ number_format($vendor->total_sale, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="avatar-sm bg-soft-info rounded mb-3">
                            <iconify-icon icon="solar:bag-check-bold-duotone" class="avatar-title fs-24 text-info"></iconify-icon>
                        </div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Orders</h6>
                        <h4 class="mb-0 fw-bold">{{ $orders_count }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="avatar-sm bg-soft-success rounded mb-3">
                            <iconify-icon icon="solar:box-bold-duotone" class="avatar-title fs-24 text-success"></iconify-icon>
                        </div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Products</h6>
                        <h4 class="mb-0 fw-bold">{{ $total_products_count }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="avatar-sm bg-soft-warning rounded mb-3">
                            <iconify-icon icon="solar:wallet-bold-duotone" class="avatar-title fs-24 text-warning"></iconify-icon>
                        </div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Commission</h6>
                        <h4 class="mb-0 fw-bold">{{ $vendor->currency ?? 'INR' }} {{ number_format($total_commission, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-8 col-12">
                <div class="card shadow-sm border-0 h-100 bg-primary text-white overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:graph-up-bold-duotone" class="fs-32 text-white"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="text-white text-opacity-75 text-uppercase fs-11 fw-bold mb-1">Monthly Revenue</h6>
                                <h3 class="mb-0 fw-bold">{{ $vendor->currency ?? 'INR' }} {{ number_format($monthly_revenue, 2) }}</h3>
                            </div>
                        </div>
                        <iconify-icon icon="solar:chart-2-bold-duotone" class="position-absolute bottom-0 end-0 fs-100 text-white opacity-10 mb-n4 me-n4"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Profile & Contact -->
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="{{ $vendor->logo }}" alt="{{ $vendor->store_name }}" class="avatar-xl rounded-circle border border-4 border-light shadow-sm">
                            <span class="position-absolute bottom-0 end-0 p-1 bg-white rounded-circle shadow-sm">
                                @if($vendor->status == 1)
                                <iconify-icon icon="solar:check-circle-bold" class="text-success fs-20"></iconify-icon>
                                @elseif($vendor->status == 4)
                                <iconify-icon icon="solar:clock-circle-bold" class="text-warning fs-20"></iconify-icon>
                                @else
                                <iconify-icon icon="solar:close-circle-bold" class="text-danger fs-20"></iconify-icon>
                                @endif
                            </span>
                        </div>
                        <h4 class="mb-1 fw-bold">{{ $vendor->store_name }}</h4>
                        <p class="text-muted mb-3">{{ $vendor->business_name ?? 'Professional Vendor' }}</p>

                        <div class="status-container mb-4" data-id="{{ $vendor->id }}">
                            @if($vendor->status == 1)
                            <span class="badge bg-success-subtle text-success px-3 py-2 fs-12 status-badge" style="cursor: pointer;">APPROVED</span>
                            @elseif($vendor->status == 4)
                            <span class="badge bg-warning-subtle text-warning px-3 py-2 fs-12 status-badge" style="cursor: pointer;">PENDING APPROVAL</span>
                            @elseif($vendor->status == 3)
                            <span class="badge bg-dark-subtle text-dark px-3 py-2 fs-12 status-badge" style="cursor: pointer;">BLOCKED</span>
                           @elseif($vendor->status == 2)
                            <span class="badge bg-danger-subtle text-danger px-3 py-2 fs-12 status-badge" style="cursor: pointer;">REJECTED</span>
                            @endif

                            <select class="form-select form-select-sm status-select d-none mt-2">
                                @if( $vendor->status == 4|| $vendor->status == 0)
                                <option value="4" {{ $vendor->status == 4 ? 'selected' : '' }}>Pending</option>
                                <option value="1">Approve</option>
                                <option value="2">Reject</option>
                                @elseif($vendor->status == 1)
                                <option value="1" selected>Approved</option>
                                <option value="3">Block</option>
                                <option value="4">Pending</option>
                                @elseif($vendor->status == 3)
                                <option value="3" selected>Blocked</option>
                                <option value="1">Unblock</option>
                                @else
                                <option value="2" selected>Rejected</option>
                                <option value="4">Pending</option>
                                <option value="1">Approve</option>
                                @endif
                            </select>

                            @if(($vendor->status == 2 || $vendor->status == 3) && $vendor->rejection_reason)
                            <div class="alert alert-danger-subtle mt-3 mb-0 p-2 fs-12">
                                <strong>Rejection Reason:</strong> {{ $vendor->rejection_reason }}
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <div class="px-3 border-end text-warning">
                                <ul class="d-flex list-unstyled m-0 fs-16">
                                    @php
                                    $fullStars = floor($vendor->avg_rating);
                                    $hasHalfStar = ($vendor->avg_rating - $fullStars) >= 0.5;
                                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                    @endphp
                                    @for($i = 0; $i < $fullStars; $i++)
                                        <li><iconify-icon icon="solar:star-bold"></iconify-icon></li>
                                        @endfor
                                        @if($hasHalfStar)
                                        <li><iconify-icon icon="solar:star-half-bold"></iconify-icon></li>
                                        @endif
                                        @for($i = 0; $i < $emptyStars; $i++)
                                            <li><iconify-icon icon="solar:star-linear"></iconify-icon></li>
                                            @endfor
                                </ul>
                                <p class="text-muted fs-12 mb-0">{{ $vendor->avg_rating }}/5 ({{ $vendor->review_count }})</p>
                            </div>
                            <div class="px-3 border-end">
                                <h5 class="mb-0 fw-bold">{{ $item_stock }}</h5>
                                <p class="text-muted fs-12 mb-0">Stock</p>
                            </div>
                            <div class="px-3">
                                <h5 class="mb-0 fw-bold">{{ $order_complete_percent }}%</h5>
                                <p class="text-muted fs-12 mb-0">Success</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light-subtle border-top p-0">
                        <div class="list-group list-group-flush border-0">
                            <div class="list-group-item bg-transparent d-flex align-items-center gap-3 py-3">
                                <div class="avatar-sm bg-white shadow-sm rounded d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:letter-linear" class="fs-18 text-primary"></iconify-icon>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="mb-0 fs-13 fw-bold">Email Address</h6>
                                    <p class="text-muted mb-0 fs-13 text-truncate">{{ $vendor->email }}</p>
                                </div>
                            </div>
                            <div class="list-group-item bg-transparent d-flex align-items-center gap-3 py-3">
                                <div class="avatar-sm bg-white shadow-sm rounded d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:phone-linear" class="fs-18 text-primary"></iconify-icon>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-13 fw-bold">Phone Number</h6>
                                    <p class="text-muted mb-0 fs-13">{{ $vendor->phone }}</p>
                                </div>
                            </div>
                            <div class="list-group-item bg-transparent d-flex align-items-center gap-3 py-3">
                                <div class="avatar-sm bg-white shadow-sm rounded d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:users-group-rounded-linear" class="fs-18 text-primary"></iconify-icon>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-13 fw-bold">Total Customers</h6>
                                    <p class="text-muted mb-0 fs-13">{{ $vendor_users_count }} Verified Users</p>
                                </div>
                            </div>
                            <div class="list-group-item bg-transparent d-flex align-items-center gap-3 py-3">
                                <div class="avatar-sm bg-white shadow-sm rounded d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:tag-price-linear" class="fs-18 text-primary"></iconify-icon>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-13 fw-bold">Total Discounts</h6>
                                    <p class="text-muted mb-0 fs-13">{{ $vendor->currency ?? 'INR' }} {{ number_format($total_discount, 2) }} given</p>
                                </div>
                            </div>
                            <div class="list-group-item bg-transparent d-flex align-items-center gap-3 py-3">
                                <div class="avatar-sm bg-white shadow-sm rounded d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:point-on-map-linear" class="fs-18 text-primary"></iconify-icon>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-13 fw-bold">Store Location</h6>
                                    <p class="text-muted mb-0 fs-13">{{ $vendor->address }}, {{ $vendor->city_name }}, {{ $vendor->state_name }} - {{ $vendor->zip }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Analytics Summary Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0">Category Performance</h5>
                    </div>
                    <div class="card-body">
                        @php
                        $colors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
                        $total_profit_all = $profit_by_category->sum('total_profit');
                        @endphp
                        @forelse($profit_by_category as $profit)
                        @php
                        $percent = $total_profit_all > 0 ? ($profit->total_profit / $total_profit_all) * 100 : 0;
                        $color = $colors[$loop->index % count($colors)];
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0 fs-13">{{ $profit->category_name }}</h6>
                                <span class="fw-bold fs-13 text-{{ $color }}">{{ $vendor->currency ?? 'INR' }} {{ number_format($profit->total_profit, 2) }}</span>
                            </div>
                            <div class="progress progress-sm rounded-pill" style="height: 6px;">
                                <div class="progress-bar bg-{{ $color }} rounded-pill" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3 text-muted">No category data available</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Documents & Banking -->
            <div class="col-xl-8">
                <!-- Analytics Chart -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Sales Analytics</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">Last 12 Months</button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Download Report</a>
                                <a class="dropdown-item" href="#">Export Data</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="sales_analytic_seller" class="apex-charts" style="min-height: 350px;"></div>
                    </div>
                </div>

                <!-- Banking & Documents Tab System -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent p-0 border-bottom">
                        <ul class="nav nav-tabs nav-tabs-custom nav-success border-bottom-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active py-3" data-bs-toggle="tab" href="#banking-details" role="tab">
                                    <iconify-icon icon="solar:bank-linear" class="fs-18 me-1 align-middle"></iconify-icon>
                                    Banking & Tax
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3" data-bs-toggle="tab" href="#vendor-documents" role="tab">
                                    <iconify-icon icon="solar:document-text-linear" class="fs-18 me-1 align-middle"></iconify-icon>
                                    KYC Documents
                                    @php
                                        $allVerified = $vendor_docs->count() > 0 && $vendor_docs->every(fn($doc) => $doc->is_verify == 1);
                                    @endphp
                                    <span class="badge {{ $allVerified ? 'bg-success' : 'bg-danger' }} rounded-pill ms-1">{{ $vendor_docs->count()  }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3" data-bs-toggle="tab" href="#latest-products" role="tab">
                                    <iconify-icon icon="solar:box-linear" class="fs-18 me-1 align-middle"></iconify-icon>
                                    Recent Products
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            <!-- Banking Tab -->
                            <div class="tab-pane active" id="banking-details" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded border border-dashed border-primary border-opacity-25">
                                            <p class="text-muted text-uppercase fs-11 fw-bold mb-2">Primary Bank Account</p>
                                            <h5 class="mb-1 fw-bold">{{ $vendor->bank_name ?: 'Bank Not Set' }}</h5>
                                            <p class="mb-0 text-dark">A/C: {{ $vendor->account_number ?: 'N/A' }}</p>
                                            <p class="mb-0 text-muted fs-13">Holder: {{ $vendor->account_holder_name ?: 'N/A' }}</p>
                                            <p class="mb-0 text-muted fs-13">IFSC: {{ $vendor->ifsc_code ?: 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded border border-dashed border-info border-opacity-25 h-100">
                                            <p class="text-muted text-uppercase fs-11 fw-bold mb-2">Tax Information</p>
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <span class="text-muted fs-13 d-block">PAN Number</span>
                                                    <span class="fw-bold">{{ $vendor->pan_no ?: 'N/A' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-muted fs-13 d-block">VAT / Tax ID</span>
                                                    <span class="fw-bold">{{ $vendor->vat_or_tax ?: 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6">
                                        <div class="p-3 bg-light rounded border border-dashed border-info border-opacity-25 h-100">

                                            <p class="text-muted text-uppercase fs-11 fw-bold mb-2">Cancelled Cheque Proof </p>
                                            <div class="card-body p-3 text-center">
                                                <div class="bg-light rounded p-2 mb-3">
                                                    @if(Str::endsWith($vendor->cancelled_cheque, ['.jpg', '.jpeg', '.png', '.webp']))
                                                    <img src="{{ $vendor->cancelled_cheque }}" alt="Cancelled Cheque" class="img-fluid rounded" style="max-height: 120px;">
                                                    @else
                                                    <iconify-icon icon="solar:file-bold-duotone" class="fs-48 text-primary"></iconify-icon>
                                                    @endif
                                                </div>
                                                <h6 class="mb-1 fw-bold">Cancelled Cheque</h6>
                                                <p class="text-muted fs-12 mb-3">Primary Bank Proof</p>
                                                <a href="{{ $vendor->cancelled_cheque }}" target="_blank" class="btn btn-sm btn-soft-primary w-100">View Document</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents Tab -->
                            <div class="tab-pane" id="vendor-documents" role="tabpanel">
                                <div class="row g-3">
                                    <!-- Cancelled Cheque -->


                                    @foreach($vendor_docs as $doc)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card border shadow-none mb-0 h-100">
                                            <div class="card-body p-3 text-center d-flex flex-column h-100">
                                                <div class="bg-light rounded p-2 mb-3 flex-grow-1 d-flex align-items-center justify-content-center">
                                                    @if(Str::endsWith($doc->document, ['.jpg', '.jpeg', '.png', '.webp']))
                                                    <img src="{{ $doc->document }}" alt="{{ $doc->documentType->name ?? 'Document' }}" class="img-fluid rounded" style="max-height: 120px;">
                                                    @else
                                                    <iconify-icon icon="solar:file-bold-duotone" class="fs-48 text-primary"></iconify-icon>
                                                    @endif
                                                </div>
                                                <h6 class="mb-1 fw-bold text-truncate" title="{{ $doc->documentType->name ?? 'Document' }}">
                                                    {{ $doc->documentType->name ?? 'Document' }}
                                                </h6>
                                                @if($doc->document_number)
                                                <p class="text-muted fs-11 mb-2">Number: <span class="fw-semibold text-dark">{{ $doc->document_number }}</span></p>
                                                @endif

                                                <div class="status-badge-container mb-3" data-id="{{ $doc->id }}" style="cursor: pointer;">
                                                    @if($doc->is_verify == 1)
                                                    <span class="badge bg-success-subtle text-success py-1 px-2 fs-10 text-uppercase status-badge">VERIFIED</span>
                                                    @elseif($doc->is_verify == 0)
                                                    <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-10 text-uppercase status-badge">PENDING</span>
                                                    @else
                                                    <span class="badge bg-danger-subtle text-danger py-1 px-2 fs-10 text-uppercase status-badge">REJECTED</span>
                                                    @endif

                                                    <select class="form-select form-select-sm document-status-select fs-12 d-none mt-2">
                                                        <option value="0" {{ $doc->is_verify == 0 ? 'selected' : '' }}>Pending</option>
                                                        <option value="1" {{ $doc->is_verify == 1 ? 'selected' : '' }}>Approve</option>
                                                        <option value="2" {{ $doc->is_verify == 2 ? 'selected' : '' }}>Reject</option>
                                                    </select>

                                                    @if($doc->is_verify == 2 && $doc->rejection_reason)
                                                    <p class="text-danger mt-1 mb-0 fs-10 rejection-reason-text" title="{{ $doc->rejection_reason }}">
                                                        Reason: {{ Str::limit($doc->rejection_reason, 20) }}
                                                    </p>
                                                    @endif
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <a href="{{ $doc->document }}" target="_blank" class="btn btn-sm btn-soft-primary flex-grow-1">View</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                    @if($vendor_docs->count() == 0)
                                    <div class="col-12 text-center py-4">
                                        <iconify-icon icon="solar:document-add-linear" class="fs-48 text-muted mb-2"></iconify-icon>
                                        <p class="text-muted">No additional documents uploaded.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Products Tab -->
                            <div class="tab-pane" id="latest-products" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-hover">
                                        <thead class="bg-light-subtle fs-12 text-uppercase">
                                            <tr>
                                                <th>Product</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($products as $product)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ $product->image }}" alt="" class="avatar-sm rounded border">
                                                        <div>
                                                            <a href="{{ route('product.detail', $product->id) }}" class="text-dark fw-bold mb-0 d-block fs-13">{{ Str::limit($product->name, 30) }}</a>
                                                            <span class="text-muted fs-11">Variants: {{ $product->variants->count() }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="fs-13">{{ $product->category->name ?? 'N/A' }}</span></td>
                                                <td>
                                                    <div class="status-badge-container" data-id="{{ $product->id }}" style="cursor: pointer;">
                                                        @if($product->status == 1)
                                                        <span class="badge bg-success-subtle text-success fs-10 status-badge">PUBLISHED</span>
                                                        @elseif($product->status == 0)
                                                        <span class="badge bg-warning-subtle text-warning fs-10 status-badge">PENDING</span>
                                                        @else
                                                        <span class="badge bg-danger-subtle text-danger fs-10 status-badge">REJECTED</span>
                                                        @endif

                                                        <select class="form-select form-select-sm product-status-select d-none mt-1" style="width: 100px;">
                                                            <option value="0" {{ $product->status == 0 ? 'selected' : '' }}>Pending</option>
                                                            <option value="1" {{ $product->status == 1 ? 'selected' : '' }}>Published</option>
                                                            <option value="2" {{ $product->status == 2 ? 'selected' : '' }}>Reject</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light btn-icon" type="button" data-bs-toggle="dropdown">
                                                            <iconify-icon icon="solar:menu-dots-vertical-linear"></iconify-icon>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item" href="{{ url('product-detail/'.$product->id) }}">View</a>
                                                            <a class="dropdown-item" href="{{ url('edit-product/'.$product->id) }}">Edit</a>
                                                            <a class="dropdown-item text-danger delete-product" href="javascript:void(0);" data-id="{{ $product->id }}">Delete</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No products found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($products->hasPages())
                                <div class="mt-3">
                                    {{ $products->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    var chart_income_data = @json($revenue_chart_data);
    var chart_expense_data = @json($expense_chart_data);
    var chart_labels = @json($revenue_chart_labels);

    $(document).ready(function() {
        // Toggle status selects
        $('.status-badge, .status-badge-container').on('click', function(e) {
            if ($(e.target).hasClass('status-select') || $(e.target).hasClass('document-status-select') || $(e.target).hasClass('product-status-select')) return;
            $(this).find('.status-badge').addClass('d-none');
            $(this).find('select').removeClass('d-none').focus();
        });

        $(document).on('blur', '.status-select, .document-status-select, .product-status-select', function() {
            $(this).addClass('d-none');
            $(this).closest('.status-container, .status-badge-container').find('.status-badge').removeClass('d-none');
        });

        // Status change logic would go here (AJAX calls)
    });
</script>
@endpush