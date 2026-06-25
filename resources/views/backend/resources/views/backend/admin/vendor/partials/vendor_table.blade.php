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
                <th>Commission</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($vendors) && $vendors->count() > 0)
                @foreach($vendors as $key => $vendor)
                <tr id="row_{{ $vendor->id }}">
                    <td class="ps-4">
                        <div class="form-check">
                            <input class="form-check-input vendor-checkbox" type="checkbox" value="{{ $vendor->id }}">
                        </div>
                    </td>
                    <td>
                            <div class="avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center p-0 overflow-hidden" style="width: 40px; height: 40px;">
                                <img src="{{ $vendor->logo }}" alt="" class="img-fluid">
                            </div>
                            <div>
                                <h5 class="fs-14 mb-0 text-body fw-bold mn-1">
                                    {{ Str::limit($vendor->store_name, 20) }}
                                    @if($vendor->areRequiredDocumentsVerified())
                                        <iconify-icon icon="solar:verified-check-bold" class="text-success fs-16 align-middle ms-1" data-bs-toggle="tooltip" title="Verified Vendor"></iconify-icon>
                                    @endif
                                </h5>
                                <p class="text-muted mb-0 fs-12">{{ Str::limit($vendor->name, 20) }}</p>

                                  @php
                                    $addedOn = $vendor->created_at ? strtolower(\Carbon\Carbon::parse($vendor->created_at)->format('d-M-Y')) : null;
                                @endphp
                                    @if($addedOn)
                                        <span class="text-muted fs-12">Addon :{{ $addedOn }}</span>
                                    @endif

                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="mb-0 fs-13 text-muted">{{ $vendor->email }}</p>
                        <p class="text-muted mb-0 fs-12">{{ $vendor->phone }}</p>
                    </td>
                    <td>
                        <p class="mb-0 fs-13 text-muted text-wrap" style="max-width: 250px;">
                             {{Str::limit($vendor->address, 20)}}
                        <p class="text-muted mb-0 fs-12">{{ $vendor->city_name }}, {{ $vendor->country_name }}</p>  
                    </td>
                    <td>
                        @if($vendor->from_web == 1)
                            <span class="badge bg-info-subtle text-info fs-12">Web</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary fs-12">Admin</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-dark fw-medium">{{ $common_commission ?? '0' }}%</span>
                    </td>
                    <td>
                    <div class="status-container">
                        @php
                            $vendor_status = $vendor->status ?? 0;
                            $badgeClass = 'bg-danger-subtle text-danger';
                            $statusText = 'Rejected';
                            if($vendor_status == 1) {
                                $badgeClass = 'bg-success-subtle text-success';
                                $statusText = 'Approved';
                            } elseif($vendor_status == 0 || $vendor_status == 4) {
                                $badgeClass = 'bg-warning-subtle text-warning';
                                $statusText = 'Pending';
                            } elseif($vendor_status == 3) {
                                $badgeClass = 'bg-dark-subtle text-dark';
                                $statusText = 'Blocked';
                            }
                        @endphp
                        
                        <span class="badge {{ $badgeClass }} fs-12 vendor-status-badge" 
                              data-id="{{ $vendor->id }}" 
                              style="cursor: pointer;">
                            {{ $statusText }}
                        </span>

                        <select class="form-select form-select-sm vendor-status-select d-none" 
                                data-id="{{ $vendor->id }}"
                                style="width: 120px;">
                            <option value="0" {{ $vendor_status == 0 ? 'selected' : '' }}>Pending</option>
                            <option value="1" {{ $vendor_status == 1 ? 'selected' : '' }}>Approved</option>
                            <option value="2" {{ $vendor_status == 2 ? 'selected' : '' }}>Rejected</option>
                            <option value="3" {{ $vendor_status == 3 ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-3">
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
                    <td colspan="7" class="text-center py-4 text-muted">No vendors found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@if($vendors instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="card-footer border-top bg-white">
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

