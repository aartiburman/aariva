@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Vendor Requests – {{ $campaign->name }}</h4>
                <a href="{{ route('campaign.list') }}" class="btn btn-secondary btn-sm">Back to Campaigns</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Vendor</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $key => $v)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $v->store_name ?? $v->name }}</td>
                                    <td>{{ number_format($v->budget_total, 2) }}</td>
                                    <td>
                                        @if(($v->status ?? '') === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif(($v->status ?? '') === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif(($v->status ?? '') === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $v->status ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(($v->status ?? '') === 'pending')
                                            <form action="{{ route('campaign.vendor.approve', ['campaignId' => $campaign->id, 'vendorId' => $v->vendor_id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form action="{{ route('campaign.vendor.reject', ['campaignId' => $campaign->id, 'vendorId' => $v->vendor_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Reject this request?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No vendor requests.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    {{ $vendors->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
