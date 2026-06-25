<tr>
    <td class="ps-4 py-3 fw-medium text-dark">
        @if($sale->payout_id)
            <span class="text-primary">#{{ $sale->payout_id }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="py-3">
        <span class="text-primary fw-medium">#{{ $sale->order_reference_id ?? 'N/A' }}</span>
          @if($sale->vendor)
            @if($sale->vendor->role == 1)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Admin</span>
            @else
                <a href="{{ route('vendor.detail', $sale->vendor->id) }}" class="badge bg-light text-dark border px-2 py-1">{{ $sale->vendor->store_name ?? $sale->vendor->name }}</a>
            @endif
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="py-3">
        <div class="d-flex align-items-center gap-2">
            <div>
                <h6 class="mb-1 fs-14 fw-bold">
                    @if(optional($sale->order->user)->id)
                        <a href="{{ route('customer.detail', $sale->order->user->id) }}" class="text-dark hover-primary">
                            {{ $sale->order->user->name }}
                        </a>
                    @else
                        Guest User
                    @endif
                </h6>
                <div class="d-flex flex-column gap-0">
                    <p class="mb-0 fs-12 text-muted d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:letter-linear" class="fs-14"></iconify-icon>
                        {{ optional($sale->order->user)->email ?? 'N/A' }}
                    </p>
                    <p class="mb-0 fs-12 text-muted d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:phone-linear" class="fs-14"></iconify-icon>
                        {{ optional($sale->order->user)->phone ?? 'N/A' }}
                    </p>
                    <p class="mb-0 fs-12 text-muted d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:map-point-linear" class="fs-14"></iconify-icon>
                        {{ optional($sale->order->user)->address ?? 'No Address' }}
                    </p>
                </div>
            </div>
        </div>
    </td>
    
   
    <td class="py-3 fs-13" style="min-width: 180px;">
        <div class="d-flex flex-column gap-1">
            <div class="row g-0">
                <div class="col-6 text-muted">Qty:</div>
                <div class="col-6 text-end text-dark fw-medium">{{ $sale->total_qty }}</div>
            </div>
            <div class="row g-0">
                <div class="col-6 text-muted">Sub Total:</div>
                <div class="col-6 text-end text-dark fw-medium">{{ $sale->formatted_sub_total }}</div>
            </div>
            <div class="row g-0">
                <div class="col-6 text-muted">Tax:</div>
                <div class="col-6 text-end text-dark fw-medium">{{ $sale->formatted_tax }}</div>
            </div>
            <div class="row g-0">
                <div class="col-6 text-muted">Delivery:</div>
                <div class="col-6 text-end text-dark fw-medium">{{ $sale->formatted_delivery }}</div>
            </div>
            @if($sale->total_discount > 0)
            <div class="row g-0">
                <div class="col-6 text-muted">Coupon:</div>
                <div class="col-6 text-end text-danger fw-medium">{{ $sale->formatted_discount }}</div>
            </div>
            @endif
            @if($sale->campaign_discount > 0)
            <div class="row g-0">
                <div class="col-6 text-muted">Campaign:</div>
                <div class="col-6 text-end text-danger fw-medium">{{ $sale->formatted_campaign_discount }}</div>
            </div>
            @endif
        </div>
    </td>
    <td class="py-3 text-end fw-bold text-dark fs-14">
        {{ $sale->currency }} {{ $sale->formatted_amount }}
    </td>
    <td class="py-3 text-center">
        @php
            $payStatus = $sale->payment_status;
            $payLabel = 'Unpaid';
            $payClass = 'bg-danger-subtle text-danger';
            
            if($payStatus == 1 || $payStatus == 'paid' || $payStatus == 'Paid') {
                $payLabel = 'Paid';
                $payClass = 'bg-success-subtle text-success';
            } elseif($payStatus == 2) {
                $payLabel = 'Pending';
                $payClass = 'bg-warning-subtle text-warning';
            }
        @endphp
        <span class="badge {{ $payClass }} px-2 py-1 fs-11 text-uppercase">{{ $payLabel }}</span>
    </td>
    <td class="py-3 text-center">
        @php
            $orderStatus = $sale->order_status;
            $statusLabel = 'Pending';
            $statusClass = 'bg-warning-subtle text-warning';
            
            switch($orderStatus) {
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
                    $statusLabel = 'Returned';
                    $statusClass = 'bg-danger-subtle text-danger';
                    break;
                case 6:
                    $statusLabel = 'In Dispute';
                    $statusClass = 'bg-secondary-subtle text-secondary';
                    break;
            }
        @endphp
        <span class="badge {{ $statusClass }} px-2 py-1 fs-11 text-uppercase">{{ $statusLabel }}</span>
    </td>
    <td class="pe-4 py-3 fw-medium text-dark text-end">
        {{ \Carbon\Carbon::parse($sale->order_date)->format('M d, Y') }}<br>
        <span class="text-muted fs-11">{{ \Carbon\Carbon::parse($sale->order_date)->format('h:i A') }}</span>
    </td>
</tr>