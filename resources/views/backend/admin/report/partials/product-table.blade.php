@php
$formatPrice = function($amount) {
$amount = (float) $amount;
if ($amount >= 10000000) {
return number_format($amount / 10000000, 2) . ' Cr';
} elseif ($amount >= 100000) {
return number_format($amount / 100000, 2) . ' Lakh';
} elseif ($amount >= 1000) {
return number_format($amount / 1000, 2) . ' K';
}
return number_format($amount, 2);
};
@endphp

@forelse($products as $product)
@php
$price = $product->firstVariant->price ?? 0;
$currency = optional(optional($product->vendor)->country)->currency_code ?? 'AED';
@endphp
<tr>
    <td class="ps-4 fs-13 fw-medium text-dark text-nowrap">
        <a href="{{ url('product-detail/' . $product->id) }}">
        <div class="d-flex align-items-center">
            <div class="avatar-sm me-2">
                <img src="{{ $product->image }}" alt="" class="img-fluid rounded">
            </div>
            <div>{{ \Illuminate\Support\Str::limit($product->name, 10, '..') }}</div>
        </div>
        </a>
    </td>
    <td class="fs-13 text-muted text-nowrap">{{ $product->firstVariant->sku ?? 'N/A' }}</td>
    <td class="fs-13 text-muted text-nowrap">{{ $product->vendor->store_name ?? $product->vendor->name ?? 'N/A' }}</td>
    <td class="fs-13 text-muted text-nowrap">{{ $product->category->name ?? 'N/A' }}</td>
    <td class="fs-13 text-muted text-nowrap">{{ $product->brand->name ?? 'N/A' }}</td>
    <td class="fs-13 text-nowrap">{{ $currency }} {{ $formatPrice($price) }}</td>
    <td class="fs-13 text-center">{{ $product->sold_qty }}</td>
    <td class="fs-13 text-nowrap">
        {{ $formatPrice($product->total_discount) }}
        @if($product->discount_percentage > 0)
            <small class="text-danger d-block">({{ $product->discount_percentage }}% Off)</small>
        @endif
    </td>
    <td class="fs-13 fw-bold text-dark text-nowrap">{{ $currency }} {{ $formatPrice($product->total_sales) }}</td>
    <td class="text-center">
        @if($product->status == 1)
        <span class="badge bg-success-subtle text-success px-2 py-1 text-uppercase">Active</span>
        @else
        <span class="badge bg-danger-subtle text-danger px-2 py-1 text-uppercase">Inactive</span>
        @endif
    </td>
    <td class="fs-13 text-muted text-nowrap">{{ $product->last_sold ? \Carbon\Carbon::parse($product->last_sold)->format('Y-m-d') : 'N/A' }}</td>
</tr>
@empty
<tr>
    <td colspan="12" class="text-center py-5 text-muted">
        <iconify-icon icon="solar:box-linear" class="fs-48 mb-2 d-block mx-auto opacity-25"></iconify-icon>
        No product records found.
    </td>
</tr>
@endforelse