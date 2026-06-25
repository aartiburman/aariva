@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Refund Detail #{{ $refund->id }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('vendor.refund.list') }}" class="btn btn-primary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Refund Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">User:</div>
                            <div class="col-sm-8">{{ $refund->user->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Order ID:</div>
                            <div class="col-sm-8">#{{ $refund->order->reference_id ?? $refund->order_id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Product:</div>
                            <div class="col-sm-8">
                                {{ $refund->orderItem->product->name ?? 'N/A' }}
                                @if($refund->orderItem->variant)
                                    <br><small class="text-muted">Variant: {{ $refund->orderItem->variant->variant_name }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Refund Reason:</div>
                            <div class="col-sm-8">{{ $refund->refund_reason }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Description:</div>
                            <div class="col-sm-8">{{ $refund->description ?? 'No description' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Amount:</div>
                            <div class="col-sm-8 text-primary fw-bold">{{ number_format($refund->amount, 2) }}</div>
                        </div>
                        @if($refund->images)
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Images:</div>
                            <div class="col-sm-8">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($refund->images as $image)
                                        <a href="{{ \App\Helpers\ImageHelper::getRefundImage($image) }}" target="_blank">
                                            <img src="{{ \App\Helpers\ImageHelper::getRefundImage($image) }}" alt="Refund Image" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($refund->vendor_status == 0)
                <div class="card border-warning">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title mb-0 text-white">Vendor Action Required</h5>
                    </div>
                    <div class="card-body">
                        <p>Please review the refund request and decide whether to initiate it to the admin for final approval or reject it.</p>
                        <form action="{{ route('vendor.refund.action') }}" method="POST">
                            @csrf
                            <input type="hidden" name="refund_id" value="{{ $refund->id }}">
                            
                            <div class="mb-3">
                                <label class="form-label">Action</label>
                                <select name="action" class="form-select" id="vendor_action">
                                    <option value="initiate">Initiate to Admin</option>
                                    <option value="reject">Reject</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Message (Optional for initiate, required for reject)</label>
                                <textarea name="message" class="form-control" rows="3" placeholder="Enter message..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Action</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Status Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="fw-bold d-block">Vendor Status:</label>
                            @if($refund->vendor_status == 0)
                                <span class="badge bg-warning">Pending Review</span>
                            @elseif($refund->vendor_status == 1)
                                <span class="badge bg-info">Initiated to Admin</span>
                            @elseif($refund->vendor_status == 2)
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                            @if($refund->vendor_message)
                                <div class="mt-2 p-2 bg-light rounded small">
                                    <strong>Message:</strong> {{ $refund->vendor_message }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="fw-bold d-block">Admin Status:</label>
                            @if($refund->admin_status == 0)
                                <span class="badge bg-secondary">Waiting for Admin</span>
                            @elseif($refund->admin_status == 1)
                                <span class="badge bg-success">Approved</span>
                            @elseif($refund->admin_status == 2)
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                            @if($refund->admin_message)
                                <div class="mt-2 p-2 bg-light rounded small">
                                    <strong>Admin Message:</strong> {{ $refund->admin_message }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
