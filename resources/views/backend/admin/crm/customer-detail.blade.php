@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Customer Details</h4>
                    <div class="page-title-right">
                        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                            <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-soft-primary rounded-circle fs-24">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                        </div>
                        <h5>{{ $customer->name }}</h5>
                        <p class="text-muted mb-1">{{ $customer->email }}</p>
                        <p class="text-muted mb-2">{{ $customer->phone ?? '—' }}</p>
                        <span class="badge {{ $customer->status == 1 ? 'bg-success' : 'bg-danger' }} fs-12">
                            {{ $customer->status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6"><h5 class="mb-0">{{ $totalOrders }}</h5><small class="text-muted">Orders</small></div>
                            <div class="col-6"><h5 class="mb-0">{{ number_format($totalSpent, 2) }}</h5><small class="text-muted">Total Spent</small></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Groups</h6></div>
                    <div class="card-body">
                        <form action="{{ route('crm.customer.assign.group') }}" method="POST">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                            @foreach(\App\Models\CustomerGroup::where('status', true)->get() as $group)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="group_ids[]" value="{{ $group->id }}"
                                    id="g{{ $group->id }}" {{ $customer->customerGroups->contains($group->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="g{{ $group->id }}">{{ $group->name }}</label>
                            </div>
                            @endforeach
                            <button type="submit" class="btn btn-sm btn-primary mt-2">Update Groups</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Address</h6></div>
                    <div class="card-body small">
                        <p class="mb-1">{{ $customer->address ?? '—' }}</p>
                        <p class="mb-0">{{ $customer->city?->name ?? '' }}{{ $customer->state?->name ? ', ' . $customer->state->name : '' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Internal Notes</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">+ Add Note</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($customer->customerNotes()->latest()->get() as $note)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">{{ $note->user->name ?? 'Staff' }} · {{ $note->created_at->diffForHumans() }}</small>
                                    <form action="{{ route('crm.note.delete', $note->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Delete this note?')">
                                            <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                                <p class="mb-0 mt-1">{{ $note->note }}</p>
                            </div>
                            @empty
                            <div class="list-group-item text-center text-muted py-3">No notes yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Recent Orders</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr><th>Order</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->orders as $order)
                                    <tr>
                                        <td><a href="{{ route('orders.details', $order->order_reference_id) }}" class="text-decoration-none fw-medium">#{{ $order->order_reference_id }}</a></td>
                                        <td>{{ $order->items->sum('quantity') }}</td>
                                        <td>{{ number_format($order->total_cost, 2) }}</td>
                                        <td>
                                            @php $s = $order->status; @endphp
                                            <span class="badge {{ $s == 3 ? 'bg-success' : ($s == 4 || $s == 5 ? 'bg-danger' : 'bg-warning') }}">
                                                {{ ['Pending','Confirmed','Shipped','Delivered','Cancelled','Returned','Dispute'][$s] ?? 'Pending' }}
                                            </span>
                                        </td>
                                        <td><small class="text-muted">{{ $order->created_at->format('d M Y') }}</small></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No orders</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Support Tickets</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle"><tr><th>Subject</th><th>Status</th><th>Priority</th><th>Date</th></tr></thead>
                                <tbody>
                                    @forelse($tickets as $t)
                                    <tr>
                                        <td>{{ $t->subject }}</td>
                                        <td><span class="badge {{ $t->status == 'open' ? 'bg-success' : ($t->status == 'closed' ? 'bg-secondary' : 'bg-warning') }}">{{ ucfirst($t->status) }}</span></td>
                                        <td><span class="badge {{ $t->priority == 'high' ? 'bg-danger' : ($t->priority == 'medium' ? 'bg-warning' : 'bg-info') }}">{{ ucfirst($t->priority) }}</span></td>
                                        <td><small class="text-muted">{{ $t->created_at->format('d M Y') }}</small></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No tickets</td></tr>
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

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('crm.note.store') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="modal-header"><h5 class="modal-title">Add Note</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <textarea name="note" class="form-control" rows="4" placeholder="Enter internal note..." maxlength="2000" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
