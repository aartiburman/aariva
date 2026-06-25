<div class="table-responsive">
    <table class="table table-hover table-centered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Vendor</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Vendor Status</th>
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
                    <td>{{ $refund->vendor->name ?? 'N/A' }}</td>
                    <td>{{ $refund->orderItem->product->name ?? 'N/A' }}</td>
                    <td>{{ number_format($refund->amount, 2) }}</td>
                    <td>
                        @if($refund->vendor_status == 0)
                            <span class="badge bg-warning">Pending</span>
                        @elseif($refund->vendor_status == 1)
                            <span class="badge bg-info">Initiated</span>
                        @elseif($refund->vendor_status == 2)
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </td>
                    <td>
                        @if($refund->admin_status == 0)
                            <span class="badge bg-warning">Pending</span>
                        @elseif($refund->admin_status == 1)
                            <span class="badge bg-primary">Approved</span>
                        @elseif($refund->admin_status == 2)
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </td>
                    <td>{{ $refund->created_at->format('d M, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.refund.show', $refund->id) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No refund requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">
    {{ $refunds->links() }}
</div>
