@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Kyc Varification Report</h4>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
            <div class="card-body p-3">
                <form action="{{ route('kyc.report') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Search Name/Email</label>
                            <input type="text" name="search" class="form-control shadow-sm py-2" placeholder="Search name/email" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Business Name</label>
                            <input type="text" name="business_name" class="form-control shadow-sm py-2" placeholder="Business / Store Name" value="{{ request('business_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Date Range</label>
                            <div class="position-relative">
                                <input type="text" name="date_range" class="form-control range-datepicker shadow-sm py-2" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                                <iconify-icon icon="solar:calendar-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Status</label>
                            <select name="status" class="form-select shadow-sm py-2">
                                <option value="">All Status</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pending</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Verified</option>
                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary shadow-sm py-2 w-100 fw-medium">Search</button>
                                <a href="{{ route('kyc.report') }}" class="btn btn-secondary shadow-sm py-2 w-100 fw-medium">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center g-2">
                            <div class="col-lg-3">
                                <h4 class="card-title">Vendor KYC List</h4>
                            </div>

                        </div>
                    </div>

                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">VENDOR NAME</th>
                                        <th>DOCUMENTS</th>
                                        <th>STATUS</th>
                                        <th class="text-nowrap">LAST UPLOAD</th>
                                        <th>TAX ID / BUSINESS NAME</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $vendor)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm">
                                                    <img src="{{ $vendor->image }}" alt="" class="rounded-circle" width="32" height="32">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark fs-14">{{ $vendor->store_name ?? $vendor->name }}</h6>
                                                    <small class="text-muted fs-12">{{ $vendor->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($vendor->documents->count() > 0)
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($vendor->documents as $doc)
                                                <a href="{{ asset($doc->document) }}" target="_blank" class="btn btn-xs btn-soft-info d-flex align-items-center gap-1 py-1 px-2 fs-11" style="width: fit-content;">
                                                    <iconify-icon icon="solar:document-linear" width="14"></iconify-icon>
                                                    Doc #{{ $loop->iteration }}
                                                </a>
                                                @endforeach
                                            </div>
                                            @else
                                            <span class="text-muted fs-12 italic">No documents</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($vendor->status == 1)
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-11 text-uppercase">Verified</span>
                                            @elseif($vendor->status == 0)
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fs-11 text-uppercase">Pending</span>
                                            @else
                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-11 text-uppercase">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="text-muted text-nowrap">
                                            {{ $vendor->last_upload ? $vendor->last_upload->created_at->format('M d, Y') : 'No uploads' }}
                                        </td>
                                        <td>
                                            <div class="text-dark fw-medium">{{ $vendor->tax_id ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $vendor->business_name ?? 'N/A' }}</small>
                                        </td>
                                      
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <iconify-icon icon="solar:folder-error-linear" width="64" class="text-muted mb-3"></iconify-icon>
                                                <h6 class="text-muted">No KYC records found</h6>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($vendors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer border-top">
                        {{ $vendors->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    @endsection

    @push('scripts')
    <script>
        function approveVendor(id, name) {
            if (confirm('Are you sure you want to approve ' + name + '?')) {
                updateVendorStatus(id, 1, null);
            }
        }

        function rejectVendor(id, name) {
            const reason = prompt('Please enter rejection reason for ' + name + ':');
            if (reason !== null && reason.trim() !== "") {
                updateVendorStatus(id, 2, reason);
            } else if (reason !== null) {
                alert('Rejection reason is required.');
            }
        }

        // function updateVendorStatus(id, status, reason) {
        //     fetch("{{ route('change.vendor.status') }}", {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
        //         },
        //         body: JSON.stringify({
        //             id: id,
        //             status: status,
        //             rejection_reason: reason
        //         })
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.status) {
        //             // alert(data.message);
        //             location.reload();
        //         } else {
        //             alert('Error: ' + data.message);
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error:', error);
        //         alert('An error occurred. Please try again.');
        //     });
        // }
    </script>
    @endpush