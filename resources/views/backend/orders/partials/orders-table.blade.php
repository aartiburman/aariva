@foreach($orders as $key=> $item)
<tr>
    <td>{{ $key+1 }}</td>
    <td> <a href="javascript:void(0);" class="fw-medium">#{{ $item->order->order_reference_id ?? 'N/A' }}</a>
        <br>
        <span>Vendor: {{ $item->vendor->name ?? 'N/A' }}</span><br>
        <span>Store Name: {{ $item->vendor->store_name ?? 'N/A' }}</span>
    </td>


    <td>
        <div class="d-flex align-items-center">
            <div class="avatar-xs me-2">
                <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                    {{ substr($item->order->user->name ?? 'C', 0, 1) }}
                </div>
            </div>
            {{ $item->order->user->name ?? 'Customer' }}
        </div>
    </td>
    <td>{{ $item->order->payment_mode ?? 'N/A' }}</td>
    <td>
        @php
            $is_delivered = ($item->status ?? 0) == 3;
        @endphp
        <!-- Badge -->
        <span
            class="badge payment-status-badge {{ ($item->payment_status ?? 0) == 1 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}"
            data-order-id="{{ $item->id ?? '' }}"
            @if(!$is_delivered) style="cursor:pointer" @endif>
            {{ ($item->payment_status ?? 0) == 1 ? 'Paid' : 'Unpaid' }}
        </span>
           
        @if(!$is_delivered)
        <!-- Select -->
        <select
            class="form-select form-select-sm payment-status-select d-none"
            data-order-id="{{ $item->id ?? '' }}">
            <option value="0" {{ ($item->payment_status ?? 0) == 0 ? 'selected' : '' }}>Unpaid</option>
            <option value="1" {{ ($item->payment_status ?? 0) == 1 ? 'selected' : '' }}>Paid</option>
        </select>
        @endif
    </td>
    

    <td>{{ $item->product->name ?? 'Product' }} (Qty: {{ $item->quantity }})</td>
    <td class="fw-bold text-dark">
        {{ optional(optional($item->vendor)->country)->currency_code ?? 'INR' }} 
        {{ number_format($item->total_actual_price, 2) }}
    </td>
    <td>
        @php
        $order_status_val = $item->status ?? '0';
        $statusClass = 'bg-soft-primary text-primary';
        if($order_status_val == '0') $statusClass = 'bg-soft-warning text-warning';
        if($order_status_val == '1') $statusClass = 'bg-soft-info text-info';
        if($order_status_val == '2') $statusClass = 'bg-soft-primary text-primary';
        if($order_status_val == '3') $statusClass = 'bg-soft-success text-success';
        if($order_status_val == '4') $statusClass = 'bg-soft-secondary text-secondary';
        if($order_status_val == '5') $statusClass = 'bg-soft-warning text-warning';
        if($order_status_val == '6') $statusClass = 'bg-soft-danger text-danger';

        $order_status_text = "Unknown";
        if($order_status_val == '0') $order_status_text = "Pending";
        if($order_status_val == '1') $order_status_text = "Confirmed";
        if($order_status_val == '2') $order_status_text = "Shipped";
        if($order_status_val == '3') $order_status_text = "Delivered";
        if($order_status_val == '4') $order_status_text = "Cancelled";
        if($order_status_val == '5') $order_status_text = "Returned";
        if($order_status_val == '6') $order_status_text = "In Dispute";
        @endphp

        <!-- Badge -->
        <span
            class="badge {{ $statusClass }} order-status-badge"
            data-order-id="{{ $item->id ?? '' }}"
            @if(!$is_delivered) style="cursor:pointer" @endif>
            {{ ucfirst($order_status_text) }}
        </span>

        @if(!$is_delivered)
        <!-- Select (hidden) -->
        <select
            class="form-select form-select-sm order-status-select d-none"
            data-order-id="{{ $item->id ?? '' }}">
            @if($order_status_val == '6')
                <!-- Only Return option available for disputed orders -->
                <option value="5" {{ $order_status_val == '5' ? 'selected' : '' }}>Returned</option>
            @else
                <option value="0" {{ $order_status_val == '0' ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ $order_status_val == '1' ? 'selected' : '' }}>Confirmed</option>
                <option value="2" {{ $order_status_val == '2' ? 'selected' : '' }}>Shipped</option>
                <option value="3" {{ $order_status_val == '3' ? 'selected' : '' }}>Delivered</option>
                <option value="4" {{ $order_status_val == '4' ? 'selected' : '' }}>Cancelled</option>
                <option value="5" {{ $order_status_val == '5' ? 'selected' : '' }}>Returned</option>
                <option value="6" {{ $order_status_val == '6' ? 'selected' : '' }}>In Dispute</option>
            @endif
        </select>
        @endif
    </td>
    <td>{{ $item->created_at->format('M d, Y') }}</td>
    <td>
        <div class="d-flex gap-2">
            
                <a href="{{ route('orders.details', $item->order->order_reference_id ?? '') }}" class="btn btn-soft-primary btn-sm"><i class="bx bx-show fs-16"></i></a>
          
        </div>
    </td>
</tr>
@endforeach
