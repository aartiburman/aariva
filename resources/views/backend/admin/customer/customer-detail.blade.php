@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-xxl">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('all.customers') }}">Customers</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Customer Details</li>
                    </ol>
                </nav>
                <h3 class="mb-0 fw-bold">{{ $customer->name }} <span class="text-muted fs-14 fw-normal">#{{ $customer->uqid }}</span></h3>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Overview Row -->
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="avatar-sm bg-soft-primary rounded mb-3">
                            <iconify-icon icon="solar:bag-check-bold-duotone" class="avatar-title fs-24 text-primary"></iconify-icon>
                        </div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Orders</h6>
                        <h4 class="mb-0 fw-bold">{{ $totalOrders }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="avatar-sm bg-soft-success rounded mb-3">
                            <iconify-icon icon="solar:wallet-bold-duotone" class="avatar-title fs-24 text-success"></iconify-icon>
                        </div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Spent</h6>
                        <h4 class="mb-0 fw-bold">{{ optional($orders->first())->currency_code ?? 'INR' }} {{ number_format($totalSpent, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="avatar-sm bg-soft-info rounded mb-3">
                            <iconify-icon icon="solar:calendar-bold-duotone" class="avatar-title fs-24 text-info"></iconify-icon>
                        </div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Registered At</h6>
                        <h4 class="mb-0 fw-bold fs-16">{{ $customer->created_at->format('M d, Y') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Personal Info -->
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="avatar-lg bg-light rounded d-flex align-items-center justify-content-center">
                                @if($customer->image)
                                    <img src="{{ $profile_image }}" class="img-fluid rounded" alt="avatar">
                                @else
                                    <iconify-icon icon="solar:user-linear" class="fs-40 text-muted"></iconify-icon>
                                @endif
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">{{ $customer->name }}</h5>
                                <p class="text-muted mb-0 fs-13">{{ $customer->email }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase fs-11 fw-bold mb-3">Personal Information</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex align-items-center gap-2 mb-3">
                                    <iconify-icon icon="solar:letter-linear" class="fs-18 text-muted"></iconify-icon>
                                    <span class="fs-13">{{ $customer->email }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 mb-3">
                                    <iconify-icon icon="solar:phone-linear" class="fs-18 text-muted"></iconify-icon>
                                    <span class="fs-13">{{ $customer->phone ?? 'N/A' }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 mb-3">
                                    <iconify-icon icon="solar:user-id-linear" class="fs-18 text-muted"></iconify-icon>
                                    <span class="fs-13">Gender: {{ ucfirst($customer->gender ?? 'N/A') }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:calendar-date-linear" class="fs-18 text-muted"></iconify-icon>
                                    <span class="fs-13">DOB: {{ $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('M d, Y') : 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h6 class="text-muted text-uppercase fs-11 fw-bold mb-3">Primary Shipping Address</h6>
                            <div class="d-flex align-items-start gap-2">
                                <iconify-icon icon="solar:map-point-linear" class="fs-18 text-muted mt-1"></iconify-icon>
                                <span class="fs-13">
                                    {{ $customer->address ?? 'No address provided' }}<br>
                                    {{ optional($customer->city)->name ? $customer->city->name . ', ' : '' }}
                                    {{ optional($customer->state)->name ? $customer->state->name . ', ' : '' }}
                                    {{ optional($customer->country)->name ?? '' }}
                                    {{ $customer->zip ? ' - ' . $customer->zip : '' }}
                                </span>
                            </div>
                        </div>

                        @if($shippingAddresses->count() > 0)
                        <div class="mt-4">
                            <h6 class="text-muted text-uppercase fs-11 fw-bold mb-3">Saved Addresses ({{ $shippingAddresses->count() }})</h6>
                            <div class="vstack gap-3">
                                @foreach($shippingAddresses as $address)
                                <div class="p-2 border rounded-3 @if($address->is_default) border-primary bg-primary-subtle @endif">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold fs-12">{{ $address->name }}</span>
                                        @if($address->is_default)
                                        <span class="badge bg-primary fs-10">Default</span>
                                        @endif
                                    </div>
                                    <p class="mb-1 fs-12 text-muted">{{ $address->phone }}</p>
                                    <p class="mb-0 fs-12 text-muted">
                                        {{ $address->address }}<br>
                                        {{ optional($address->city)->name ? $address->city->name . ', ' : '' }}
                                        {{ optional($address->state)->name ? $address->state->name . ', ' : '' }}
                                        {{ optional($address->country)->name ?? '' }}
                                        {{ $address->zip ? ' - ' . $address->zip : '' }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Send Notification Card -->
                        <div class="mt-4 pt-2">
                            <div class="card border border-primary-subtle bg-light-subtle shadow-none mb-0">
                                <div class="card-body">
                                    <h6 class="text-primary text-uppercase fs-11 fw-bold mb-3 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:bell-bing-bold-duotone" class="fs-16"></iconify-icon>
                                        Send Push Notification
                                    </h6>
                                    
                                    @if($customer->device_token || $customer->onesignal_player_id)
                                        <div class="mb-3 p-2 rounded border border-dashed border-success-subtle">
                                            <p class="text-success fs-11 mb-1 fw-medium"><iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon> User is reachable</p>
                                            <code class="fs-10 text-muted text-break">Token: {{ $customer->device_token ?? $customer->onesignal_player_id }}</code>
                                        </div>
                                    @else
                                        <div class="mb-3 p-2 rounded border border-dashed border-warning-subtle">
                                            <p class="text-warning fs-11 mb-0 fw-medium"><iconify-icon icon="solar:info-circle-bold" class="me-1"></iconify-icon> No active device token found</p>
                                        </div>
                                    @endif

                                    <form action="{{ route('notifications.sendPush') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                        <input type="hidden" name="type" value="system">
                                        
                                        <div class="mb-2">
                                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Notification Title" required>
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="message" class="form-control form-control-sm" rows="3" placeholder="Notification Message..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1">
                                            <iconify-icon icon="solar:plain-2-bold-duotone" class="fs-14"></iconify-icon>
                                            Send Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order & Payment History -->
            <div class="col-xl-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header border-0 p-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold">Order & Payment History</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('orders.details', $order->order_reference_id) }}" class="fw-medium text-primary">
                                                #{{ $order->order_reference_id }}
                                            </a>
                                        </td>
                                        <td class="fs-13">{{ $order->created_at->format('M d, Y') }}</td>
                                        <td class="fw-bold">{{ $order->currency_code ?? 'INR' }} {{ number_format($order->total_cost, 2) }}</td>
                                        <td class="fs-12 text-uppercase">{{ str_replace('_', ' ', $order->payment_mode) }}</td>
                                        <td>
                                            @if($order->payment_status == 1)
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11 text-uppercase">Paid</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11 text-uppercase">Unpaid</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $status = $order->items->min('status') ?? 0;
                                                $label = 'Pending';
                                                $class = 'bg-warning-subtle text-warning';
                                                if($status == 1) { $label = 'Confirmed'; $class = 'bg-info-subtle text-info'; }
                                                elseif($status == 2) { $label = 'Shipped'; $class = 'bg-primary-subtle text-primary'; }
                                                elseif($status == 3) { $label = 'Delivered'; $class = 'bg-success-subtle text-success'; }
                                                elseif($status == 4) { $label = 'Cancelled'; $class = 'bg-danger-subtle text-danger'; }
                                                elseif($status == 5) { $label = 'Returned'; $class = 'bg-danger-subtle text-danger'; }
                                            @endphp
                                            <span class="badge {{ $class }} px-2 py-1 fs-11 text-uppercase">{{ $label }}</span>
                                        </td>
                                        <td>
                                            <a href="{{route('orders.details', $order->order_reference_id) }}" class="btn btn-sm btn-soft-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No orders found for this customer.</td>
                                    </tr>
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
