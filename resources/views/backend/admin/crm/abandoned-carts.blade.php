@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Abandoned Carts</h4>
                    <div class="page-title-right">
                        <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr><th>#</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Created</th><th>Notified</th></tr>
                        </thead>
                        <tbody>
                            @forelse($carts as $cart)
                            <tr>
                                <td>{{ $loop->iteration + ($carts->currentPage() - 1) * $carts->perPage() }}</td>
                                <td>
                                    @if($cart->user)
                                    <a href="{{ route('crm.customer.detail', $cart->user_id) }}" class="text-decoration-none fw-medium">{{ $cart->user->name }}</a>
                                    @else
                                    <span class="text-muted">Guest ({{ $cart->ip_address }})</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cart->cart_data)
                                    <span class="badge bg-info">{{ count((array)$cart->cart_data) }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ number_format($cart->total, 2) }}</td>
                                <td>
                                    @php
                                    $st = $cart->status;
                                    $cls = $st == 'active' ? 'warning' : ($st == 'recovered' ? 'success' : 'secondary');
                                    @endphp
                                    <span class="badge bg-{{ $cls }}">{{ ucfirst($st) }}</span>
                                </td>
                                <td><small class="text-muted">{{ $cart->created_at->format('d M Y H:i') }}</small></td>
                                <td><small>{{ $cart->notified_at ? $cart->notified_at->format('d M Y H:i') : '—' }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No abandoned carts</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($carts->hasPages())<div class="card-footer">{{ $carts->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
