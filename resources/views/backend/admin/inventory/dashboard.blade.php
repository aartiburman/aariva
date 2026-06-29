@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Inventory Dashboard</h4>
                    <div class="page-title-right">
                        <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-primary">Stock Movements</a>
                        <a href="{{ route('inventory.warehouses') }}" class="btn btn-sm btn-outline-secondary">Warehouses</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Products</p>
                                <h4 class="mb-0">{{ $totalProducts }}</h4>
                            </div>
                            <div class="avatar-sm bg-soft-primary rounded">
                                <iconify-icon icon="solar:box-linear" class="avatar-title fs-24 text-primary"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Variants</p>
                                <h4 class="mb-0">{{ $totalVariants }}</h4>
                            </div>
                            <div class="avatar-sm bg-soft-info rounded">
                                <iconify-icon icon="solar:layers-linear" class="avatar-title fs-24 text-info"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Low Stock</p>
                                <h4 class="mb-0 text-warning">{{ $lowStockVariants }}</h4>
                            </div>
                            <div class="avatar-sm bg-soft-warning rounded">
                                <iconify-icon icon="solar:bell-linear" class="avatar-title fs-24 text-warning"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Out of Stock</p>
                                <h4 class="mb-0 text-danger">{{ $outOfStockVariants }}</h4>
                            </div>
                            <div class="avatar-sm bg-soft-danger rounded">
                                <iconify-icon icon="solar:close-circle-linear" class="avatar-title fs-24 text-danger"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Low Stock Alerts</h5>
                        <span class="badge bg-warning">{{ $lowStockList->total() }} items</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Warehouse</th>
                                        <th>Current Stock</th>
                                        <th>Threshold</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lowStockList as $variant)
                                    <tr>
                                        <td>
                                            <a href="{{ route('product.detail', $variant->product_id) }}" class="text-decoration-none fw-medium">
                                                {{ $variant->product->name ?? 'N/A' }}
                                            </a>
                                            @if($variant->color)
                                                <br><small class="text-muted">{{ $variant->color }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $variant->sku }}</td>
                                        <td>{{ $variant->warehouse->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge @if($variant->stock <= 0) bg-danger @else bg-warning @endif">
                                                {{ $variant->stock }}
                                            </span>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm threshold-input" style="width:80px"
                                                value="{{ $variant->low_stock_threshold }}" data-variant-id="{{ $variant->id }}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary adjust-stock-btn"
                                                data-variant-id="{{ $variant->id }}"
                                                data-product="{{ $variant->product->name ?? '' }}"
                                                data-sku="{{ $variant->sku }}"
                                                data-stock="{{ $variant->stock }}">
                                                Adjust
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No low stock items</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($lowStockList->hasPages())
                    <div class="card-footer">{{ $lowStockList->links() }}</div>
                    @endif
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Stock Movements</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr><th>Type</th><th>Product</th><th>Qty</th><th>By</th><th>When</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMovements as $m)
                                    <tr>
                                        <td>
                                            <span class="badge @if($m->type == 'in') bg-success @elseif($m->type == 'out') bg-danger @else bg-info @endif">
                                                {{ ucfirst($m->type) }}
                                            </span>
                                        </td>
                                        <td><small>{{ $m->variant->product->name ?? 'N/A' }} ({{ $m->variant->sku ?? '' }})</small></td>
                                        <td>{{ $m->quantity }}</td>
                                        <td><small>{{ $m->user->name ?? 'System' }}</small></td>
                                        <td><small class="text-muted">{{ $m->created_at->diffForHumans() }}</small></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No movements yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="adjustStockForm">
                @csrf
                <input type="hidden" name="variant_id" id="adjust_variant_id">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1"><strong>Product:</strong> <span id="adjust_product_name"></span></p>
                    <p class="mb-3"><strong>SKU:</strong> <span id="adjust_sku"></span> | <strong>Current:</strong> <span id="adjust_current_stock" class="badge bg-info"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="in">Stock In (Add)</option>
                            <option value="out">Stock Out (Remove)</option>
                            <option value="adjustment">Set Exact Quantity</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select" required>
                            <option value="Damaged">Damaged</option>
                            <option value="Lost">Lost</option>
                            <option value="Found">Found</option>
                            <option value="Returned to stock">Returned to stock</option>
                            <option value="Quality check">Quality check</option>
                            <option value="Inventory count">Inventory count</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.adjust-stock-btn').on('click', function() {
        $('#adjust_variant_id').val($(this).data('variant-id'));
        $('#adjust_product_name').text($(this).data('product'));
        $('#adjust_sku').text($(this).data('sku'));
        $('#adjust_current_stock').text($(this).data('stock'));
        $('#adjustStockModal').modal('show');
    });

    $('#adjustStockForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '{{ route("inventory.stock.adjustment") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    $('#adjustStockModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Error adjusting stock';
                toastr.error(msg);
                btn.prop('disabled', false).text('Save Adjustment');
            }
        });
    });

    $('.threshold-input').on('change', function() {
        const variantId = $(this).data('variant-id');
        const threshold = $(this).val();

        $.ajax({
            url: '{{ route("inventory.update.threshold") }}',
            method: 'POST',
            data: { variant_id: variantId, threshold: threshold, _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.status) toastr.success(res.message);
            },
            error: function() { toastr.error('Failed to update threshold'); }
        });
    });
});
</script>
@endpush
