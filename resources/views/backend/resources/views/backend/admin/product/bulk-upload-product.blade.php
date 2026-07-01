@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Bulk Upload Products</h4>
                    <div class="page-title-right">
                        <a href="{{ route('bulk.upload.product.template') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1 export-btn" data-export="true" download>
                            <iconify-icon icon="solar:download-minimalistic-linear" class="fs-18"></iconify-icon>
                            Download Dummy CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Product Import Wizard</h4>
                        
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:check-read-linear" class="fs-20"></iconify-icon>
                                <div>{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:danger-linear" class="fs-20"></iconify-icon>
                                <div>{{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(session('bulk_upload_errors') && is_array(session('bulk_upload_errors')))
                        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="mb-2 fw-bold d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:info-circle-linear" class="fs-20"></iconify-icon>
                                <span>Some rows could not be imported:</span>
                            </div>
                            <ul class="mb-0 ps-4">
                                @foreach(session('bulk_upload_errors') as $msg)
                                <li class="small">{{ $msg }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <form id="bulkUploadForm" method="POST" action="{{ route('bulk.upload.product.store') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <!-- Step 1: Default Config -->
                                <div class="col-lg-8">
                                    <div class="card border shadow-none mb-4 h-100">
                                        <div class="card-header bg-light-subtle py-2">
                                            <h5 class="card-title mb-0 fs-13 text-uppercase fw-bold">Step 1: Default Configuration</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fs-13 fw-medium">Category</label>
                                                    <select name="category_id" class="form-select form-select-sm category_id" data-select="true">
                                                        <option value="">Select Category</option>
                                                        @foreach($categories_data as $cat)
                                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-13 fw-medium">Sub Category</label>
                                                    <select name="subcategory_id" class="form-select form-select-sm subcategory_id"  data-select="true"></select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-13 fw-medium">Child Category</label>
                                                    <select name="child_category_id" class="form-select form-select-sm child_category_id"  data-select="true"></select>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label fs-13 fw-medium">Brand</label>
                                                    <select name="brand_id" class="form-select form-select-sm categoryBrand"  data-select="true">
                                                        <option value="">Select Brand</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <hr class="my-4 border-dashed">

                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <label class="form-label d-block fs-13 fw-bold mb-2">Apply Offers</label>
                                                    <div class="d-flex flex-wrap gap-3 p-2 border bg-light-subtle">
                                                        @foreach ($offers as $value)
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" name="offers[]" value="{{ $value->id }}" id="offer_{{ $value->id }}">
                                                            <label class="form-check-label fs-12 cursor-pointer" for="offer_{{ $value->id }}">{{ $value->code }}</label>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label d-block fs-13 fw-bold mb-2">Product Placement</label>
                                                    <div class="d-flex flex-wrap gap-3 p-2 border bg-light-subtle">
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" name="product_in[]" value="1" id="in_1">
                                                            <label class="form-check-label fs-12 cursor-pointer" for="in_1">Best Seller</label>
                                                        </div>
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" name="product_in[]" value="2" id="in_2">
                                                            <label class="form-check-label fs-12 cursor-pointer" for="in_2">Trending</label>
                                                        </div>
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" name="product_in[]" value="3" id="in_3">
                                                            <label class="form-check-label fs-12 cursor-pointer" for="in_3">Popular</label>
                                                        </div>
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" name="product_in[]" value="4" id="in_4">
                                                            <label class="form-check-label fs-12 cursor-pointer" for="in_4">Deal</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="d-flex flex-wrap gap-3 p-2 border bg-light-subtle">
                                                        <div class="form-check form-switch m-0">
                                                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured">
                                                            <label class="form-check-label fs-13 fw-medium cursor-pointer" for="featured">Mark all products as Featured</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Upload -->
                                <div class="col-lg-4">
                                    <div class="card border shadow-none mb-4 h-100">
                                        <div class="card-header bg-light-subtle py-2">
                                            <h5 class="card-title mb-0 fs-13 text-uppercase fw-bold">Step 2: Upload File</h5>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <div class="mb-4">
                                                <label class="form-label fs-13 fw-medium">Upload CSV File <span class="text-danger">*</span></label>
                                                <div class="upload-container p-4 border border-dashed rounded text-center bg-light-subtle position-relative">
                                                    <iconify-icon icon="solar:cloud-upload-linear" class="fs-40 text-primary mb-2"></iconify-icon>
                                                    <h6 class="fs-14 mb-1">Click to browse or drag file</h6>
                                                    <p class="text-muted fs-12 mb-0">Only CSV files allowed (Max 5MB)</p>
                                                    <input type="file" name="csv_file" class="form-control position-absolute top-0 start-0 opacity-0 w-100 h-100 cursor-pointer" accept=".csv,text/csv" required>
                                                </div>
                                                <div id="file-name-preview" class="mt-2 fs-12 text-primary fw-medium d-none"></div>
                                            </div>

                                            <div class="mt-auto">
                                                <div class="alert alert-info border-0 bg-info-subtle p-2 mb-3">
                                                    <div class="d-flex gap-2">
                                                        <iconify-icon icon="solar:info-circle-linear" class="fs-18 text-info"></iconify-icon>
                                                        <p class="mb-0 fs-11">Bulk upload can take up to 10 minutes for large files. Please do not close the window.</p>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" id="submit-btn">
                                                    <span class="spinner-border spinner-border-sm d-none" id="btn-spinner" role="status" aria-hidden="true"></span>
                                                    <iconify-icon icon="solar:upload-linear" id="btn-icon" class="fs-20"></iconify-icon>
                                                    <span id="btn-text" class="fw-bold">START IMPORT</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Filter and Search Row -->
                        <div class="mt-4 {{ (request('vendor_id') || request('is_active') !== null || request('date_range')) ? 'show' : '' }}" >
                                <div class="card border shadow-none mb-4">
                                    <div class="card-body">
                                        <form action="{{ route('bulk.upload.product') }}" method="GET" id="filter-form">
                                            <div class="row align-items-end g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Vendor</label>
                                                    <select name="vendor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="">All Vendors</option>
                                                        @foreach($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Status</label>
                                                    <select name="is_active" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="">All Status</option>
                                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Pending</option>
                                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Approved</option>
                                                        <option value="2" {{ request('is_active') === '2' ? 'selected' : '' }}>Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Date Range</label>
                                                    <input type="text" name="date_range" class="form-control range-datepicker" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                                                </div>
                                                <div class="col-md-3 d-flex gap-2">
                                                    <a href="{{ route('bulk.upload.product')}}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Table Section -->
                            <div class="card border shadow-none mt-4">
                                <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center py-2">
                                    <h4 class="card-title mb-0">Recent Uploaded Products</h4>
                                    <div class="d-flex align-items-center gap-2">
                                      
                                        <a href="{{ route('export.bulk.products', request()->all()) }}" class="btn btn-sm btn-outline-secondary
 gap-1">
                                            <iconify-icon icon="solar:download-linear" class="fs-18"></iconify-icon> Export
                                        </a>

                                        <button class="btn btn-sm btn-outline-danger d-none" id="bulk-delete-btn">
                                            <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle me-1"></iconify-icon> Bulk Delete
                                        </button>
                                        <span class="badge bg-primary-subtle text-primary fs-12">{{ $products->count() }} Recently Added</span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0 table-hover table-centered">
                                            <thead class="bg-light-subtle">
                                                <tr>
                                                    <th style="width: 20px;">
                                                        <div class="form-check ms-1">
                                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                                            <label class="form-check-label" for="checkAll"></label>
                                                        </div>
                                                    </th>
                                                    <th>Product Info</th>
                                                    <th>Vendor</th>
                                                    <th>Category & Brand</th>
                                                    <th>Variant Details</th>
                                                    <th>Price & Stock</th>
                                                    <th>Status & Flags</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($products as $product)
                                                <tr  id="row_{{ $value->id }}">
                                                    <td>
                                                        <div class="form-check ms-1">
                                                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $product->id }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $firstVariant = $product->variants->first();
                                                            $firstImage = '';
                                                            if ($firstVariant) {
                                                                $images = json_decode($firstVariant->image, true);
                                                                if (is_array($images) && count($images) > 0) {
                                                                    $firstImage = $images[0];
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if($firstImage)
                                                                <img src="{{ asset('uploads/products/'.$firstImage) }}" alt="product" class="avatar-sm rounded bg-light border">
                                                            @else
                                                                <div class="avatar-sm rounded bg-light border d-flex align-items-center justify-content-center">
                                                                    <iconify-icon icon="solar:gallery-linear" class="fs-20 text-muted"></iconify-icon>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="fs-13 mb-0 fw-bold">{{ $product->name }}</h6>
                                                                <small class="text-muted">SKU: {{ $product->variants->first()->sku ?? 'N/A' }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="fs-13 mb-0 fw-bold">{{ $product->vendor->name ?? 'N/A' }}</h6>
                                                            <small class="text-muted">{{ $product->vendor->store_name ?? 'Aariva' }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <small class="text-muted">
                                                                {{ $product->category->name ?? 'N/A' }}
                                                                @if($product->subCategory) > {{ $product->subCategory->name }} @endif
                                                                @if($product->childCategory) > {{ $product->childCategory->name }} @endif
                                                            </small>
                                                            <span class="badge bg-light text-dark border w-fit fs-11 mt-1">
                                                                {{ $product->brand->name ?? 'No Brand' }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column gap-1">
                                                            <span class="badge bg-primary-subtle text-primary fs-11 w-fit">
                                                                {{ $product->variants->count() }} Variants
                                                            </span>
                                                            <small class="text-muted">Color: {{ $product->variants->first()->color ?? 'N/A' }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold text-dark fs-13">{{ number_format($product->variants->first()->price ?? 0, 2) }}</span>
                                                            @php $stock = $product->variants->first()->stock ?? 0; @endphp
                                                            <span class="fs-11 {{ $stock > 10 ? 'text-success' : 'text-danger' }}">
                                                                {{ $stock }} in stock
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-1">
                                                            @if($product->status == 1)
                                                                <span class="badge bg-success-subtle text-success py-1 px-2 fs-11 text-uppercase">Approved</span>
                                                            @else
                                                                <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-11 text-uppercase">Pending</span>
                                                            @endif

                                                            @if($product->is_featured)
                                                                <span class="badge bg-warning-subtle text-warning p-1" title="Featured">
                                                                    <iconify-icon icon="solar:star-linear" class="fs-14"></iconify-icon>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <a href="{{ url('edit-product/'.$product->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Product">
                                                                <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                            </a>
                                                            <a href="{{ url('edit-variant/'.$product->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Variant">
                                                                <iconify-icon icon="solar:tuning-square-linear" class="fs-20"></iconify-icon>
                                                            </a>
                                                            <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-product" data-id="{{ $product->id }}" data-bs-toggle="tooltip" title="Delete">
                                                                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <iconify-icon icon="solar:box-minimalistic-linear" class="fs-40 mb-2"></iconify-icon>
                                                            <p class="mb-0">No products found in recent uploads</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer border-top bg-light-subtle">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <p class="mb-0 fs-12 text-muted">Showing {{ $products->count() }} most recent uploads</p>
                                        <a href="{{ route('product.list') }}" class="btn btn-sm btn-outline-primary">View All Products</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Formatting Guide -->
                            <!-- <div class="card border shadow-none mt-4 bg-light-subtle">
                                <div class="card-body p-3">
                                    <h6 class="fs-13 mb-3 fw-bold text-dark d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:document-text-linear" class="text-primary fs-18"></iconify-icon>
                                        CSV FORMATTING INSTRUCTIONS
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="p-2 bg-white rounded-2 border shadow-sm h-100">
                                                <p class="mb-1 fw-bold fs-12 text-primary">Discount Formats</p>
                                                <p class="mb-0 fs-11 text-muted">Use <code>%</code> for percentage (e.g. 10%) or <code>off</code> for flat amounts (e.g. 100 off).</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-2 bg-white rounded-2 border shadow-sm h-100">
                                                <p class="mb-1 fw-bold fs-12 text-primary">Multi-select Fields</p>
                                                <p class="mb-0 fs-11 text-muted">For Coupons or Placements, use comma-separated IDs (e.g. <code>1,2,5</code>).</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-2 bg-white rounded-2 border shadow-sm h-100">
                                                <p class="mb-1 fw-bold fs-12 text-primary">Boolean Flags</p>
                                                <p class="mb-0 fs-11 text-muted">Use <code>1</code> for Active/Yes and <code>0</code> for Inactive/No.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                    </div>
                </div>
            </div>
        </div>
 
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
    

        // File input preview
        $('input[name="csv_file"]').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            if(fileName) {
                $('#file-name-preview').text('Selected: ' + fileName).removeClass('d-none');
            } else {
                $('#file-name-preview').addClass('d-none');
            }
        });

        // Form submission loading state
        $('#bulkUploadForm').on('submit', function () {
            const btn = $('#submit-btn');
            const spinner = $('#btn-spinner');
            const icon = $('#btn-icon');
            const text = $('#btn-text');

            btn.prop('disabled', true);
            icon.addClass('d-none');
            spinner.removeClass('d-none');
            text.text('PROCESSING UPLOAD...');
        });

        // Bulk Delete Logic
        const bulkDeleteBtn = $('#bulk-delete-btn');
        const checkAll = $('#checkAll');
        const rowCheckboxes = $('.row-checkbox');

        checkAll.on('change', function() {
            rowCheckboxes.prop('checked', this.checked);
            toggleBulkDeleteBtn();
        });

        rowCheckboxes.on('change', function() {
            checkAll.prop('checked', rowCheckboxes.length === rowCheckboxes.filter(':checked').length);
            toggleBulkDeleteBtn();
        });

        function toggleBulkDeleteBtn() {
            if (rowCheckboxes.filter(':checked').length > 0) {
                bulkDeleteBtn.removeClass('d-none');
            } else {
                bulkDeleteBtn.addClass('d-none');
            }
        }

        bulkDeleteBtn.on('click', function() {
            const ids = rowCheckboxes.filter(':checked').map(function() {
                return $(this).val();
            }).get();

            if (ids.length > 0) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this! All related variants and images will be deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete selected!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('bulk.delete.product') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: ids
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            }
                        });
                    }
                });
            }
        });
    });
</script>
@endpush

