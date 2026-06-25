@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Wallet — {{ $vendor->store_name ?? $vendor->name }}</h4>
                    <div class="text-muted small">Current Balance: {{ number_format($vendor->wallet_balance ?? 0, 2) }}</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.wallet') }}" class="btn btn-sm btn-outline-secondary">All</a>
                    <a href="{{ route('vendor.wallet', ['type' => 'payout']) }}" class="btn btn-sm btn-primary">Payout History</a>
                    @if(($vendor->wallet_balance ?? 0) > 0)
                    <form action="{{ route('vendor.withdraw.request') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">Request Withdrawal</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">{{ (isset($mode) && $mode === 'payout') ? 'Payout History' : 'Transactions' }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    @if(isset($mode) && $mode === 'payout')
                    <table class="table mb-0 align-middle">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-4">Payout</th>
                                <th>Order</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Paid At</th>
                                <th class="pe-4 text-end">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $p)
                            <tr>
                                <td class="ps-4">#VP-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    @if($p->order_id)
                                        <span>#ORD-{{ $p->order_id }}</span>
                                    @else
                                        <span>Withdrawal</span>
                                    @endif
                                </td>
                                <td>{{ number_format($p->payout_amount, 2) }}</td>
                                <td>
                                    @php
                                        $s = (string)($p->status ?? 'pending');
                                        $cls = 'bg-warning-subtle text-warning';
                                        if ($s === 'paid') $cls = 'bg-success-subtle text-success';
                                        elseif ($s === 'failed') $cls = 'bg-danger-subtle text-danger';
                                    @endphp
                                    <span class="badge px-2 py-1 {{ $cls }}">{{ ucfirst($s) }}</span>
                                </td>
                                <td>{{ $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->format('Y-m-d H:i') : '-' }}</td>
                                <td class="pe-4 text-end">{{ \Carbon\Carbon::parse($p->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No payout records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @else
                    <table class="table mb-0 align-middle">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-4">Reference</th>
                                <th>Type</th>
                                <th>Description</th>
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
                                <td>{{ $tx->description }}</td>
                                <td>{{ number_format($tx->amount, 2) }}</td>
                                <td>
                                    @php
                                        $statusText = is_string($tx->status) ? ucfirst($tx->status) : ($tx->status ? 'Completed' : 'Completed');
                                        $cls = 'bg-success-subtle text-success';
                                    @endphp
                                    <span class="badge px-2 py-1 {{ $cls }}">{{ $statusText }}</span>
                                </td>
                                <td class="pe-4 text-end">{{ \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No wallet transactions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
            @if(isset($mode) && $mode === 'payout' && ($payouts instanceof \Illuminate\Pagination\LengthAwarePaginator))
            <div class="card-footer bg-transparent border-0">
                <div class="d-flex justify-content-end">
                    {{ $payouts->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @elseif(isset($transactions) && ($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator))
            <div class="card-footer bg-transparent border-0">
                <div class="d-flex justify-content-end">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
