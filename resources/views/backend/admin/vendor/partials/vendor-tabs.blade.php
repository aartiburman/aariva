@php
    $status = request('status');
@endphp
<div class="tab-content">
    <!-- All Vendors Tab -->
    <div class="tab-pane fade {{ $status === null ? 'show active' : '' }}" id="all-vendors" role="tabpanel">
        @include('backend.admin.vendor.partials.vendor_table', ['vendors' => $all_vendors_data])
    </div>

    <!-- Pending Approval Tab -->
    <div class="tab-pane fade {{ $status == '0' ? 'show active' : '' }}" id="pending-approval" role="tabpanel">
        @include('backend.admin.vendor.partials.vendor_table', ['vendors' => $pending_vendors_data])
    </div>

    <!-- Active Tab -->
    <div class="tab-pane fade {{ $status == '1' ? 'show active' : '' }}" id="active" role="tabpanel">
        @include('backend.admin.vendor.partials.vendor_table', ['vendors' => $active_vendors_data])
    </div>

    <!-- Rejected Tab -->
    <div class="tab-pane fade {{ $status == '2' ? 'show active' : '' }}" id="rejected" role="tabpanel">
        @include('backend.admin.vendor.partials.vendor_table', ['vendors' => $rejected_vendors_data])
    </div>

    <!-- Blocked Tab -->
    <div class="tab-pane fade {{ $status == '3' ? 'show active' : '' }}" id="blocked" role="tabpanel">
        @include('backend.admin.vendor.partials.vendor_table', ['vendors' => $blocked_vendors_data])
    </div>
</div>