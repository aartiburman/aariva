 @extends('backend.layouts.app')
 @section('content')
 <div class="page-content">

     <div class="container-xxl">

         <div class="row">
             <div class="card">
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">

                         <h5 class="card-title mb-0">
                             Vendor Request
                         </h5>

                     </div>
                 </div>
             </div>


             @if(!empty($vendor_data->toArray()))

             @foreach($vendor_data as $key => $value)
             <div class="col-xl-4 col-md-6">
                 <div class="card">
                     <div class="card-body">
                         <div class="position-relative bg-light p-2 rounded text-center">
                             <img src="{{ $value->logo }}" alt="" class="avatar-xxl">
                             <div class="position-absolute top-0 end-0 m-1">
                                 <div class="dropdown">
                                     <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                         <iconify-icon icon="iconamoon:menu-kebab-vertical-circle-duotone" class="fs-20 align-middle text-muted"></iconify-icon>
                                     </a>
                                     <div class="dropdown-menu dropdown-menu-end">
                                         <a href="{{ route('vendor.detail', $value->uqid) }}" class="dropdown-item">View Details</a>
                                         <a href="{{ route('vendor.edit', $value->uqid) }}" class="dropdown-item">Edit Vendor</a>
                                         <a href="javascript:void(0);" class="dropdown-item delete-vendor" data-id="{{ $value->id }}">Delete Vendor</a>
                                     </div>
                                 </div>
                             </div>
                         </div>
                         <div class="d-flex flex-wrap justify-content-between my-3">
                             <div>
                                <h4 class="mb-1">{{ mb_strimwidth($value->store_name, 0, 10, "...") }}<span class="text-muted fs-13 ms-1">({{ Str::limit($value->business_name, 200) }}) </span></h4>
                                <div>
                                     <a href="#!" class="link-primary fs-16 fw-medium">{{ $value->uqid }}</a>
                                 </div>
                             </div>
                             <div>
                                <div class="status-container" data-id="{{ $value->id }}">
                                    @if($value->status == 1)
                                        <span class="badge bg-success-subtle text-success fs-12 status-badge" style="cursor: pointer;">Approved</span>
                                    @elseif($value->status == 0 || $value->status == 4)
                                        <span class="badge bg-warning-subtle text-warning fs-12 status-badge" style="cursor: pointer;">Pending</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fs-12 status-badge" style="cursor: pointer;">Rejected</span>
                                    @endif
                                    
                                    <select class="form-select form-select-sm status-select d-none">
                                        <option value="4" {{ $value->status == 4 ? 'selected' : '' }}>Pending</option>
                                        <option value="1" {{ $value->status == 1 ? 'selected' : '' }}>Approved</option>
                                        <option value="2" {{ $value->status == 2 ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="">
                           @php
                               $fullAddress = $value->address . ', ' . $value->city_name . ', ' . $value->state_name . ', ' . $value->zip;
                                $displayAddress = strlen($fullAddress) > 20 ? substr($fullAddress, 0, 20) . '...' : $fullAddress;
                            @endphp
                            <p class="d-flex align-items-center gap-2 mb-1"><iconify-icon icon="solar:point-on-map-linear" class="fs-18 text-primary"></iconify-icon>{{ $displayAddress }}</p>
                            <p class="d-flex align-items-center gap-2 mb-1"><iconify-icon icon="solar:letter-linear" class="fs-18 text-primary"></iconify-icon>{{ $value->email }}</p>
                             <p class="d-flex align-items-center gap-2 mb-0"><iconify-icon icon="solar:outgoing-call-rounded-linear" class="fs-20 text-primary"></iconify-icon>{{ $value->phone }}</p>
                         </div>
                         <div class="d-flex align-items-center justify-content-between mt-3 mb-1">
                             <p class="mb-0 fs-15 fw-medium text-dark">{{ $value->business_name }}</p>
                             <div>
                                 <p class="mb-0 fs-15 fw-medium text-dark">ID: {{ $value->uqid }}</p>
                             </div>
                         </div>

                         <div class="p-2 pb-0 mx-n3 mt-2">
                             <div class="row text-center g-2">
                                 <div class="col-lg-6 col-6 border-end">
                                     <h5 class="mb-1">{{ $value->country_name }}</h5>
                                     <p class="text-muted mb-0">Country</p>
                                 </div>
                                 <div class="col-lg-6 col-6">
                                     <h5 class="mb-1">{{ $value->state_name }}</h5>
                                     <p class="text-muted mb-0">State</p>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="card-footer border-top gap-1 hstack">
                          <a href="{{ route('vendor.detail', $value->uqid) }}" class="btn btn-sm btn-primary w-100">View Profile</a>
                          <a href="{{ route('vendor.edit', $value->uqid) }}" class="btn btn-sm btn-light w-100">Edit Profile</a>
                      </div>
                 </div>
             </div>
             @endforeach
             @endif

         </div>


     </div>



     @endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Status Change Logic
        $(document).on('click', '.status-badge', function() {
            let container = $(this).closest('.status-container');
            $(this).addClass('d-none');
            container.find('.status-select').removeClass('d-none').focus();
        });

        $(document).on('change', '.status-select', function() {
            let select = $(this);
            let container = select.closest('.status-container');
            let badge = container.find('.status-badge');
            let vendorId = container.data('id');
            let newStatus = select.val();
            
            // Get old status based on badge text
            let oldStatusText = badge.text().trim();
            let oldStatus = '0'; // Default Pending
            if (oldStatusText === 'Approved') oldStatus = '1';
            else if (oldStatusText === 'Rejected') oldStatus = '2';

            if (newStatus === '2') { // Rejected
                 Swal.fire({
                    title: 'Reject Vendor?',
                    text: "Please provide a reason for rejection:",
                    input: 'text',
                    inputPlaceholder: 'Reason for rejection',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Reject',
                    preConfirm: (reason) => {
                        if (!reason) {
                            Swal.showValidationMessage('Reason is required');
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateVendorStatus(vendorId, newStatus, result.value, select, badge);
                    } else {
                        // Revert select
                        select.val(oldStatus);
                        select.addClass('d-none');
                        badge.removeClass('d-none');
                    }
                });
            } else {
                 Swal.fire({
                    title: 'Are you sure?',
                    text: "Change vendor status?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateVendorStatus(vendorId, newStatus, null, select, badge);
                    } else {
                        // Revert select
                        select.val(oldStatus);
                        select.addClass('d-none');
                        badge.removeClass('d-none');
                    }
                });
            }
        });
        
        // Hide select on blur if not changed (optional UX improvement)
        $(document).on('blur', '.status-select', function() {
             // Delay to check if it's not just a click on the dropdown itself
             // But usually blur happens when clicking outside
             // We'll keep it simple: if user clicks away without changing, revert view
             // However, we need to handle the case where 'change' event fires first.
             // If change fires, it opens Swal.
             
             // Let's rely on change event or manual revert via ESC or clicking elsewhere? 
             // Actually, if they click away, it should just close.
             // But if they changed the value, the change event fires BEFORE blur.
             
             setTimeout(() => {
                 if (!$(this).is(':focus') && !Swal.isVisible()) {
                     $(this).addClass('d-none');
                     $(this).siblings('.status-badge').removeClass('d-none');
                 }
             }, 200);
        });

        function updateVendorStatus(id, status, reason, select, badge, force = false) {
            let data = {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status,
                rejection_reason: reason
            };
            if (force) {
                data.force = 1;
            }

            $.ajax({
                url: "{{ route('vendor.change.status') }}",
                type: "POST",
                data: data,
                global: false, // Prevent global loader
                success: function(response) {
                    if (response.status === 'confirm') {
                        Swal.fire({
                            title: 'Warning',
                            text: response.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, approve anyway!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                updateVendorStatus(id, status, reason, select, badge, true);
                            } else {
                                // Revert select
                                let oldStatusText = badge.text().trim();
                                let oldStatus = '0'; 
                                if (oldStatusText === 'Approved') oldStatus = '1';
                                else if (oldStatusText === 'Rejected') oldStatus = '2';
                                
                                select.val(oldStatus);
                                select.addClass('d-none');
                                badge.removeClass('d-none');
                            }
                        });
                        return;
                    }

                    if (response.status) {
                        toastr.success(response.message);
                        // Update badge text and class
                        let statusText = status == '1' ? 'Approved' : (status == '0' ? 'Pending' : 'Rejected');
                        let statusClass = status == '1' ? 'bg-success-subtle text-success' : (status == '0' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                        
                        badge.text(statusText)
                             .removeClass('bg-success-subtle text-success bg-warning-subtle text-warning bg-danger-subtle text-danger')
                             .addClass(statusClass);
                        
                        select.addClass('d-none');
                        badge.removeClass('d-none');
                    } else {
                        toastr.error(response.message);
                         // Revert
                         select.addClass('d-none');
                         badge.removeClass('d-none');
                    }
                },
                error: function() {
                    toastr.error('Something went wrong');
                     select.addClass('d-none');
                     badge.removeClass('d-none');
                }
            });
        }
    });
</script>
@endpush
