@forelse($sales as $sale)
    @if($report_type == 'date_wise')
        @include('backend.admin.report.partials.date-wise-row', ['sale' => $sale, 'currency' => $currency])
    @else
        @include('backend.admin.report.partials.sales-row', ['sale' => $sale, 'currency' => $sale->currency])
    @endif
@empty
<tr>
    <td colspan="{{ $report_type == 'date_wise' ? 7 : 8 }}" class="text-center py-5">
        <div class="text-muted">
            <iconify-icon icon="solar:clipboard-list-linear" class="fs-48 mb-3 opacity-25"></iconify-icon>
            <p class="fs-14">No sales records found</p>
        </div>
    </td>
</tr>
@endforelse