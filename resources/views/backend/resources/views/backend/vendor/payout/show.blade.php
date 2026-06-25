@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-6">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Payout Detail — #VP-{{ str_pad($payout->id, 4, '0', STR_PAD_LEFT) }}</h4>
                </div>
            </div>
            <div class="col-6 text-end">
                <a href="{{ route('vendor.payouts') }}" class="btn btn-sm btn-outline-secondary">Back to Payouts</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Order Amount</div>
                                <div class="fs-16 fw-bold">{{ number_format($payout->order_amount, 2) }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Commission</div>
                                <div class="fs-16">{{ number_format($payout->commission_amount, 2) }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Payout Amount</div>
                                <div class="fs-16 fw-bold">{{ number_format($payout->payout_amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Status</div>
                                @php
                                  $s = $payout->status;
                                  $cls = 'bg-danger-subtle text-danger';
                                  if ($s === 'paid') $cls = 'bg-success-subtle text-success';
                                  elseif ($s === 'pending') $cls = 'bg-warning-subtle text-warning';
                                  
                                @endphp
                                <span class="badge px-2 py-1 {{ $cls }}">{{ ucfirst($payout->status) }}</span>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Payment Method</div>
                                <div class="fs-14">{{ $payout->payment_method ?: 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Payment Date</div>
                                <div class="fs-14">
                                    @if($payout->paid_at)
                                        {{ \Carbon\Carbon::parse($payout->paid_at)->format('Y-m-d') }}
                                    @elseif($payout->created_at)
                                        {{ \Carbon\Carbon::parse($payout->created_at)->format('Y-m-d') }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th>Qty</th>
                                        <th class="pe-4 text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $it)
                                        <tr>
                                            <td class="ps-4">{{ $it->product->name ?? 'Product #'.$it->product_id }}</td>
                                            <td>{{ (int) ($it->quantity ?? $it->qty ?? 0) }}</td>
                                            <td class="pe-4 text-end">{{ number_format($it->total_actual_price ?? ($it->price * $it->quantity), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No items found for this order.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">Wallet Transactions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">Reference</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $tx)
                                    <tr>
                                        <td class="ps-4">{{ $tx->reference_id }}</td>
                                        <td>{{ ucfirst($tx->type) }}</td>
                                        <td>{{ number_format($tx->amount, 2) }}</td>
                                        <td><span class="badge bg-success-subtle text-success px-2 py-1">{{ is_string($tx->status) ? ucfirst($tx->status) : ($tx->status ? 'Completed' : 'Completed') }}</span></td>
                                        <td class="pe-4 text-end">{{ \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No wallet transactions linked to this payout.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">Linked Order</h5>
                    </div>
                    <div class="card-body">
                        @if($payout->order)
                            <div class="mb-2 fs-14">
                                <span class="text-muted">Order:</span>
                                <span>#{{ $payout->order->order_reference_id ?? $payout->order_id }}</span>
                            </div>
                            <div class="mb-2 fs-14">
                                <span class="text-muted">Customer:</span>
                                <span>{{ $payout->order->user->name ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-2 fs-14">
                                <span class="text-muted">Order Date:</span>
                                <span>
                                    @if($payout->order->created_at)
                                        {{ \Carbon\Carbon::parse($payout->order->created_at)->format('Y-m-d') }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        @else
                            <div class="text-muted">No order linked.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
