<tr>
    <td class="ps-4 py-3 fw-medium text-dark">
        @if($sale->payout_id)
            <span class="text-primary">#{{ $sale->payout_id }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="py-3 text-muted">
        <span class="text-primary fw-medium">#{{ $sale->order_reference_id ?? 'N/A' }}</span>
    </td>
    <td class="py-3">
        <div class="d-flex align-items-center gap-2">
          
            <div>
                <h6 class="mb-1 fs-13">
                    @if(optional($sale->order->user)->id)
                        <a href="{{ route('customer.detail', $sale->order->user->id) }}" class="text-dark hover-primary fw-bold">
                            {{ $sale->order->user->name }}
                        </a>
                    @else
                        Guest User
                    @endif
                </h6>
                <div class="d-flex flex-column gap-0">
                    <p class="mb-0 fs-11 text-muted d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:letter-linear" class="fs-12"></iconify-icon>
                        {{ optional($sale->order->user)->email ?? 'N/A' }}
                    </p>
                    <p class="mb-0 fs-11 text-muted d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:phone-linear" class="fs-12"></iconify-icon>
                        {{ optional($sale->order->user)->phone ?? 'N/A' }}
                    </p>
                    <p class="mb-0 fs-11 text-muted d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:map-point-linear" class="fs-12"></iconify-icon>
                        {{ optional($sale->order->user)->address ?? 'No Address' }}
                    </p>
                </div>
            </div>
        </div>
    </td>
    <td class="py-3">
        @if($sale->vendor)
            <div class="d-flex align-items-center gap-1">
                @if($sale->vendor->role == 1)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Admin</span>
                @else
                    <span class="badge bg-light text-dark border">{{ $sale->vendor->store_name ?? $sale->vendor->name }}</span>
                @endif
            </div>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="py-3 text-center fw-medium">
        {{ $sale->total_qty }}
    </td>
    <td class="py-3 text-end text-muted">
        {{ $sale->currency }} {{ $sale->formatted_sub_total }}
    </td>
    <td class="py-3 text-end text-muted">
        {{ $sale->currency }} {{ $sale->formatted_tax }}
    </td>
    <td class="py-3 text-end text-muted">
        {{ $sale->currency }} {{ $sale->formatted_delivery }}
    </td>
    <td class="py-3 text-end fw-bold text-dark">
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
            $status = $sale->item_status;
            $statusLabel = 'Pending';
            $statusClass = 'bg-warning-subtle text-warning';
            
            switch($status) {
                case 0: 
                    $statusLabel = 'Pending'; 
                    $statusClass = 'bg-warning-subtle text-warning'; 
                    break;
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
            }
        @endphp
        <span class="badge {{ $statusClass }} px-2 py-1 fs-11 text-uppercase">{{ $statusLabel }}</span>
    </td>
    <td class="ps-4 py-3 fw-medium text-dark text-end">
        {{ \Carbon\Carbon::parse($sale->order_date)->format('M d, Y') }}<br>
        <span class="text-muted fs-11">{{ \Carbon\Carbon::parse($sale->order_date)->format('h:i A') }}</span>
    </td>
</tr>