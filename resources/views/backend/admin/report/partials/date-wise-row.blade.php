<tr>
    <td class="ps-4 py-3 fw-medium text-dark">
        {{ \Carbon\Carbon::parse($sale->order_date)->format('M d, Y') }}
    </td>
    <td class="py-3 text-center fw-medium text-primary">
        {{ $sale->total_orders }}
    </td>
    <td class="py-3 text-center fw-medium">
        {{ $sale->total_qty }}
    </td>
    <td class="py-3 text-end text-muted">
        {{ $currency }} {{ $sale->formatted_sub_total }}
    </td>
    <td class="py-3 text-end text-muted">
        {{ $currency }} {{ $sale->formatted_tax }}
    </td>
    <td class="py-3 text-end text-muted">
        {{ $currency }} {{ $sale->formatted_delivery }}
    </td>
    <td class="py-3 text-end fw-bold text-dark">
        {{ $currency }} {{ $sale->formatted_amount }}
    </td>
</tr>