@extends('backend.layouts.app')

@section('content')



<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Payment Gateways</h4>
                    </div>

                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">GATEWAY</th>
                                        <th>MODE</th>
                                        <th>STATUS</th>
                                        <th class="pe-4 text-center">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($gateways as $gateway)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                @if($gateway->logo)
                                                <div class="rounded-3 p-1 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <img src="{{ $gateway->logo }}" alt="{{ $gateway->name }}" style="max-width: 40px; max-height: 40px;">
                                                </div>
                                                @elseif($gateway->image)
                                                <div class="rounded-3 p-1 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <img src="{{ $gateway->image }}" alt="{{ $gateway->name }}" style="max-width: 40px; max-height: 40px;">
                                                </div>
                                                @else
                                                <div class="avatar-sm bg-primary-subtle rounded-3 p-1 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <iconify-icon icon="solar:card-2-linear" width="24" class="text-primary"></iconify-icon>
                                                </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark fs-14">{{ $gateway->name }}</h6>
                                                    <small class="text-muted fs-12 text-uppercase">{{ $gateway->slug }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($gateway->mode == 'live')
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-11 text-uppercase">Live</span>
                                            @else
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fs-11 text-uppercase">Sandbox</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($gateway->status)
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-11 text-uppercase">Enabled</span>
                                            @else
                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-11 text-uppercase">Disabled</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-center">
                                            <a href="{{ route('payment.getway.edit', $gateway->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Settings">
                                                <iconify-icon icon="solar:settings-linear" class="fs-20"></iconify-icon>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <iconify-icon icon="solar:card-transfer-linear" width="64" class="text-muted mb-3"></iconify-icon>
                                                <h6 class="text-muted">No payment gateways configured</h6>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($gateways instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer border-top">
                        {{ $gateways->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection