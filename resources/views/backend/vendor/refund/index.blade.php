@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Refund Requests</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover table-centered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Admin Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($refunds as $refund)
                                        <tr>
                                            <td>{{ $refund->id }}</td>
                                            <td>{{ $refund->user->name ?? 'N/A' }}</td>
                                            <td>{{ $refund->orderItem->product->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($refund->amount, 2) }}</td>
                                            <td>
                                                @if($refund->vendor_status == 0)
                                                    <span class="badge bg-warning">New</span>
                                                @elseif($refund->vendor_status == 1)
                                                    <span class="badge bg-info">Initiated to Admin</span>
                                                @elseif($refund->vendor_status == 2)
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($refund->admin_status == 0)
                                                    <span class="badge bg-secondary">Pending</span>
                                                @elseif($refund->admin_status == 1)
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($refund->admin_status == 2)
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $refund->created_at->format('d M, Y') }}</td>
                                            <td>
                                                <a href="{{ route('vendor.refund.show', $refund->id) }}" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No refund requests found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $refunds->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
