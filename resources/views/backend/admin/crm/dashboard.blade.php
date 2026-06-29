@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">CRM Dashboard</h4>
                    <div class="page-title-right">
                        <a href="{{ route('crm.customers') }}" class="btn btn-sm btn-outline-primary">All Customers</a>
                        <a href="{{ route('crm.groups') }}" class="btn btn-sm btn-outline-secondary">Groups</a>
                        <a href="{{ route('crm.abandoned.carts') }}" class="btn btn-sm btn-outline-warning">Abandoned Carts</a>
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
                                <p class="text-muted mb-1">Total Customers</p>
                                <h4 class="mb-0">{{ $totalCustomers }}</h4>
                            </div>
                            <div class="avatar-sm bg-soft-primary rounded">
                                <iconify-icon icon="solar:users-group-rounded-linear" class="avatar-title fs-24 text-primary"></iconify-icon>
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
                                <p class="text-muted mb-1">Active</p>
                                <h4 class="mb-0">{{ $activeCustomers }}</h4>
                            </div>
                            <div class="avatar-sm bg-soft-success rounded">
                                <iconify-icon icon="solar:user-check-linear" class="avatar-title fs-24 text-success"></iconify-icon>
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
                                <p class="text-muted mb-1">Customer Groups</p>
                                <h4 class="mb-0">{{ $totalGroups }}</h4>
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
                                <p class="text-muted mb-1">Abandoned Carts</p>
                                <h4 class="mb-0 text-warning">{{ $abandonedCarts }}</h4>
                            </div>
                            <div class="avatar-sm bg-soft-warning rounded">
                                <iconify-icon icon="solar:cart-cross-linear" class="avatar-title fs-24 text-warning"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Customer Groups</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle"><tr><th>Group</th><th>Customers</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($customerGroups as $g)
                                    <tr>
                                        <td>{{ $g->name }}</td>
                                        <td>{{ $g->customers_count }}</td>
                                        <td><span class="badge {{ $g->status ? 'bg-success' : 'bg-secondary' }}">{{ $g->status ? 'Active' : 'Inactive' }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted">No groups created</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Top Customers</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle"><tr><th>Customer</th><th>Email</th><th>Orders</th></tr></thead>
                                <tbody>
                                    @forelse($topCustomers as $c)
                                    <tr>
                                        <td>
                                            <a href="{{ route('crm.customer.detail', $c->id) }}" class="text-decoration-none fw-medium">
                                                {{ $c->name }}
                                            </a>
                                        </td>
                                        <td><small>{{ $c->email }}</small></td>
                                        <td><span class="badge bg-info">{{ $c->orders_count }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted">No customers yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Recent Customers</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCustomers as $c)
                                    <tr>
                                        <td><a href="{{ route('crm.customer.detail', $c->id) }}" class="text-decoration-none fw-medium">{{ $c->name }}</a></td>
                                        <td><small>{{ $c->email }}</small></td>
                                        <td>{{ $c->phone ?? '—' }}</td>
                                        <td><span class="badge {{ $c->status == 1 ? 'bg-success' : 'bg-danger' }}">{{ $c->status == 1 ? 'Active' : 'Inactive' }}</span></td>
                                        <td><small class="text-muted">{{ $c->created_at->format('d M Y') }}</small></td>
                                        <td><a href="{{ route('crm.customer.detail', $c->id) }}" class="btn btn-sm btn-soft-primary">View</a></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center py-3 text-muted">No recent customers</td></tr>
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
@endsection
