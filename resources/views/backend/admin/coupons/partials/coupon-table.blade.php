@foreach($coupons as $coupon)
<tr class="coupon-row" id="row_{{ $coupon->id }}" data-id="{{ $coupon->id }}">
    <td>
        <div class="form-check">
            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $coupon->id }}" data-id="{{ $coupon->id }}">
        </div>
    </td>
    <td><strong>{{ $coupon->code }}</strong></td>
    <td>
        @if($coupon->type == 1)
            <span class="badge bg-soft-success text-success">{{ $coupon->value }}%</span>
        @else
            <span class="badge bg-soft-info text-info">Fixed {{ $coupon->value }}</span>
        @endif
    </td>
    <td>
        <div class="fs-13">
            From: {{ $coupon->valid_from ? $coupon->valid_from->format('d M, Y') : 'N/A' }}<br>
            To: {{ $coupon->valid_until ? $coupon->valid_until->format('d M, Y') : 'N/A' }}
        </div>
    </td>
    <td>
        <div class="fs-12">
            @if($coupon->products->isEmpty() && $coupon->categories->isEmpty() && $coupon->vendors->isEmpty())
                <span class="badge bg-soft-secondary text-secondary">Global (All)</span>
            @else
                @if($coupon->products->isNotEmpty())
                    <div><strong>Products:</strong> 
                        {{ $coupon->products->pluck('name')->implode(', ') }}
                    </div>
                @endif
                @if($coupon->categories->isNotEmpty())
                    <div><strong>Categories:</strong> 
                        {{ $coupon->categories->pluck('name')->implode(', ') }}
                    </div>
                @endif
                @if($coupon->vendors->isNotEmpty())
                    <div><strong>Vendors:</strong> 
                        {{ $coupon->vendors->pluck('name')->implode(', ') }}
                    </div>
                @endif
            @endif
        </div>
    </td>
    <td>{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</td>
    <td>
        <div class="form-check form-switch">
            <input class="form-check-input change-status" type="checkbox" data-id="{{ $coupon->id }}" {{ $coupon->status ? 'checked' : '' }}>
        </div>
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-sm btn-soft-primary"> <iconify-icon icon="solar:pen-linear"></iconify-icon></a>
            <button class="btn btn-sm btn-soft-danger delete-coupon" data-id="{{ $coupon->id }}"><iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon></button>

        </div>
    </td>
</tr>
@endforeach