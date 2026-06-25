@if($report_type == 'date_wise')
    @forelse($transactions as $item)
    <tr>
        <td class="ps-4 fw-medium">{{ \Carbon\Carbon::parse($item->order_date)->format('d M, Y') }}</td>
        <td class="text-center">{{ $item->total_orders }}</td>
        <td class="text-center">{{ $item->total_qty }}</td>
        <td class="text-end text-muted">{{ $currency }} {{ number_format($item->total_sales, 2) }}</td>
        <td class="text-end text-muted">{{ $currency }} {{ number_format($item->tax, 2) }}</td>
        <td class="text-end text-muted">{{ $currency }} {{ number_format($item->delivery_charge, 2) }}</td>
        <td class="text-end fw-bold pe-4">{{ $currency }} {{ number_format($item->total_sales + $item->tax + $item->delivery_charge, 2) }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <iconify-icon icon="solar:box-minimalistic-linear" class="fs-48 mb-2 opacity-25"></iconify-icon>
            <p class="mb-0">No sales records found for the selected criteria.</p>
        </td>
    </tr>
    @endforelse
@else
    @forelse($transactions as $item)
    <tr>
        <td class="ps-4 fw-medium text-primary">#{{ $item->order->order_reference_id  ?? 'N/A' }}</td>
        <td>
            <div class="d-flex align-items-center gap-2">
                @if($item->product && $item->product->thumbnail)
                <img src="{{ $item->product_image_url }}" alt="" class="avatar-xs rounded">
                @endif
                <span class="text-truncate" style="max-width: 150px;">{{ $item->product->name ?? 'Product Deleted' }}</span>
            </div>
        </td>
        <td>{{ $item->order->user->name ?? 'Guest' }}</td>
        <td class="fw-bold">{{ $currency }} {{ number_format($item->total_actual_price, 2) }}</td>
        <td>
            @php
                $status = $item->status;
                $statusLabel = 'Pending';
                $statusClass = 'bg-warning-subtle text-warning';
                
                switch($status) {
                    case 1:
                        $statusLabel = 'Confirmed';
                        $statusClass = 'bg-info-subtle text-info';
                        break;
                    case 2:
                        $statusLabel = 'Shipped';
                        $statusClass = 'bg-primary-subtle text-primary';
                        break;
                    case 3:
                        $statusLabel = 'Delivered';
                        $statusClass = 'bg-success-subtle text-success';
                        break;
                    case 4:
                        $statusLabel = 'Cancelled';
                        $statusClass = 'bg-danger-subtle text-danger';
                        break;
                    case 5:
                        $statusLabel = 'Rejected';
                        $statusClass = 'bg-danger-subtle text-danger';
                        break;
                    case 6:
                        $statusLabel = 'Returned';
                        $statusClass = 'bg-secondary-subtle text-secondary';
                        break;
                }
            @endphp
            <span class="badge {{ $statusClass }} px-2 py-1 fs-11 text-uppercase">{{ $statusLabel }}</span>
        </td>
        <td>{{ \Carbon\Carbon::parse($item->order_date)->format('d M, Y') }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="text-center py-5 text-muted">
            <iconify-icon icon="solar:box-minimalistic-linear" class="fs-48 mb-2 opacity-25"></iconify-icon>
            <p class="mb-0">No transactions found for the selected criteria.</p>
        </td>
    </tr>
    @endforelse
@endif