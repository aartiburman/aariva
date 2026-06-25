<div class="table-responsive">
    <table class="table align-middle table-nowrap mb-0">
        <thead class="bg-light-subtle">
            <tr>
                <th class="ps-4" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input select-all-vendors" type="checkbox">
                    </div>
                </th>
                <th>Vendor</th>
                <th>Contact</th>
                <th>Location</th>
                <th>Source</th>
                <th>Status</th>
                <th>Commission</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($vendors) && $vendors->count() > 0)
                @foreach($vendors as $key => $vendor)
                @php
                    $vendor_status = $vendor->status ?? 0;
                @endphp
                <tr id="row_{{ $vendor->id }}">
                    <td class="ps-4">
                        <div class="form-check">
                            <input class="form-check-input vendor-checkbox" type="checkbox" value="{{ $vendor->id }}">
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column align-items-start gap-1">
                            <div class="avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center p-0 overflow-hidden mb-1" style="width: 45px; height: 45px; border: 2px solid #f1f1f1;">
                                <img src="{{ $vendor->logo }}" alt="" class="img-fluid">
                            </div>
                            <div>
                                <h5 class="fs-15 mb-0 text-dark fw-bold">
                                    {{ Str::limit($vendor->store_name, 25) }}
                                </h5>
                                <p class="text-muted mb-0 fs-13 fw-medium">{{ $vendor->name }}</p>
                                <p class="text-secondary mb-1 fs-12">
                                    <span class="fw-semibold text-primary-emphasis">Addon :</span> 
                                    {{ $vendor->created_at->format('d-M-Y') }}
                                </p>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <span class="verified-badge-container">
                                        @if($vendor->is_verified)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-11">Verified</span>
                                        @endif
                                    </span>
                                    
                                    <div class="form-check form-switch p-0 m-0" style="min-height: auto;">
                                        <input class="form-check-input verify-vendor-toggle ms-0" type="checkbox" role="switch" 
                                               data-id="{{ $vendor->id }}" {{ $vendor->is_verified ? 'checked' : '' }}
                                               style="width: 30px; height: 15px; cursor: pointer;"
                                               data-bs-toggle="tooltip" title="Toggle Verified Status">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <p class="mb-0 fs-13 text-dark fw-medium d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:letter-linear" class="text-muted fs-16"></iconify-icon>
                                {{ $vendor->email }}
                            </p>
                            <p class="text-muted mb-0 fs-12 d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:phone-linear" class="text-muted fs-16"></iconify-icon>
                                {{ $vendor->phone }}
                            </p>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <p class="mb-0 fs-13 text-muted text-wrap d-flex align-items-start gap-1" style="max-width: 200px;">
                                <iconify-icon icon="solar:map-point-linear" class="text-muted fs-18 mt-1"></iconify-icon>
                                {{ Str::limit($vendor->address, 40) }}
                            </p>
                            <p class="text-muted mb-0 fs-12 ps-3">{{ $vendor->city_name }}, {{ $vendor->country_name }}</p>
                        </div>
                    </td>
                    <td>
                        @if($vendor->from_web == 1)
                            <span class="badge bg-info-subtle text-info fs-12">Web</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary fs-12">Admin</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $badgeClass = 'bg-warning-subtle text-warning';
                            $statusText = 'Pending';
                            
                            if($vendor_status == 1) {
                                $badgeClass = 'bg-success-subtle text-success';
                                $statusText = 'Approved';
                            } elseif($vendor_status == 2) {
                                $badgeClass = 'bg-danger-subtle text-danger';
                                $statusText = 'Rejected';
                            } elseif($vendor_status == 3) {
                                $badgeClass = 'bg-dark-subtle text-dark';
                                $statusText = 'Blocked';
                            } elseif($vendor_status == 4) {
                                $badgeClass = 'bg-warning-subtle text-warning';
                                $statusText = 'Pending';
                            }
                        @endphp
                        <div class="status-container" data-id="{{ $vendor->id }}">
                            <span class="badge {{ $badgeClass }} fs-12 status-badge" style="cursor: pointer;">{{ $statusText }}</span>
                            <select class="form-select form-select-sm status-select d-none" style="min-width: 100px;">
                                <option value="0" {{ $vendor_status == 0 ? 'selected' : '' }}>Pending</option>
                                <option value="1" {{ $vendor_status == 1 ? 'selected' : '' }}>Approved</option>
                                <option value="2" {{ $vendor_status == 2 ? 'selected' : '' }}>Rejected</option>
                                <option value="3" {{ $vendor_status == 3 ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <span class="text-dark fw-medium">{{ $common_commission ?? '0' }}%</span>
                    </td>
                   
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-soft-purple dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('vendor.detail', $vendor->uqid) }}">
                                        <iconify-icon icon="solar:eye-linear" class="fs-18"></iconify-icon> View Details
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('vendor.edit', $vendor->uqid) }}">
                                        <iconify-icon icon="solar:pen-linear" class="fs-18"></iconify-icon> Edit Vendor
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @if($vendor_status != 1)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-success action-change-status" href="javascript:void(0);" data-id="{{ $vendor->id }}" data-status="1">
                                        <iconify-icon icon="solar:check-circle-linear" class="fs-18"></iconify-icon> Approve Vendor
                                    </a>
                                </li>
                                @endif
                                @if($vendor_status != 2 && $vendor_status != 0 && $vendor_status != 4)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-warning action-change-status" href="javascript:void(0);" data-id="{{ $vendor->id }}" data-status="2">
                                        <iconify-icon icon="solar:close-circle-linear" class="fs-18"></iconify-icon> Reject Vendor
                                    </a>
                                </li>
                                @endif
                                @if($vendor_status != 3)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger action-change-status" href="javascript:void(0);" data-id="{{ $vendor->id }}" data-status="3">
                                        <iconify-icon icon="solar:forbidden-circle-linear" class="fs-18"></iconify-icon> Block Vendor
                                    </a>
                                </li>
                                @endif
                                @if($vendor_status == 3)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-success action-change-status" href="javascript:void(0);" data-id="{{ $vendor->id }}" data-status="1">
                                        <iconify-icon icon="solar:check-circle-linear" class="fs-18"></iconify-icon> Unblock Vendor
                                    </a>
                                </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger delete-vendor" href="javascript:void(0);" data-id="{{ $vendor->id }}">
                                        <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-18"></iconify-icon> Delete Vendor
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No vendors found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@if($vendors instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="card-footer border-top">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted fs-12">
            Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }}
        </div>
        <div>
            {{ $vendors->appends(request()->except('_token'))->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endif

