@extends('backend.layouts.app')

@section('content')
<div class="page-content">
<div class="container-fluid">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Active Campaigns</h4>
                <div class="d-flex align-items-center gap-2">
                    @if($optIn)
                        <form method="POST" action="{{ route('vendor.campaigns.optout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Opt-Out Promotions</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('vendor.campaigns.optin') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">Opt-In Promotions</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body table-responsive">
                @if(!$optIn)
                    <div class="alert alert-warning">
                        You are currently opted out of promotions. Opt-in to participate and apply discounts for your orders.
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <table class="table table-striped align-middle">
                    <thead>
                        <tr class="text-nowrap">
                            <th>No.</th>
                            <th>Name</th>
                            <th>Discount %</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Slots</th>
                            <th>Budget</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $now = \Carbon\Carbon::now() @endphp
                        @if($campaigns->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center">No campaigns available</td>
                            </tr>
                        @else
                            @foreach($campaigns as $key => $c)
                                @php
                                    $isClosed = !$c->status;
                                    $isExpired = $c->status && \Carbon\Carbon::parse($c->end_date) < $now;
                                    $isUpcoming = $c->status && \Carbon\Carbon::parse($c->start_date) > $now;
                                    $isActive = $c->status && !$isExpired && !$isUpcoming;
                                    $vendorsCount = $c->vendors_count ?? 0;
                                    $maxVendors = $c->max_vendors;
                                    $maxApplications = $maxVendors ? (int) ceil(((int) $maxVendors) * 10) : null;
                                    if ($maxApplications !== null && $maxApplications < 1) $maxApplications = 1;
                                    $isFull = $maxApplications !== null && $vendorsCount >= $maxApplications;
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $c->name }}</td>
                                    <td>{{ number_format($c->discount_percent, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($c->start_date)->format('Y-m-d H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($c->end_date)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $isClosed ? 'bg-secondary' : ($isExpired ? 'bg-danger' : ($isUpcoming ? 'bg-warning' : 'bg-success')) }}">
                                            {{ $isClosed ? 'Closed' : ($isExpired ? 'Expired' : ($isUpcoming ? 'Upcoming' : 'Active')) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($maxVendors)
                                            <span class="badge {{ $isFull ? 'bg-danger' : 'bg-info' }}">
                                                {{ $vendorsCount }} / {{ $maxVendors }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Unlimited</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($c->budget_per_vendor > 0)
                                            {{ number_format($c->budget_per_vendor, 2) }} (Fixed)
                                        @else
                                            <span class="text-muted">Dynamic</span>
                                        @endif
                                    </td>
                                    <td style="min-width:260px;">
                                        @php($pivot = $joinedMap[$c->id] ?? null)
                                        @php($status = $pivot['status'] ?? null)
                                        @php($joined = $status !== null)
                                        @php($isApproved = $joined && ($status === 'approved' || $status === '1'))
                                        @php($isRejected = $joined && ($status === 'rejected' || $status === '2'))
                                        @php($isPending = $joined && ($status === 'pending' || $status === '0'))
                                        @php($isExhausted = $joined && $status === 'exhausted')
                                        
                                        <button type="button" class="btn btn-outline-secondary btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#marginCalcModal"
                                            data-campaign-name="{{ $c->name }}"
                                            data-discount-percent="{{ number_format($c->discount_percent, 2) }}"
                                            data-commission="{{ number_format($commissionPercent ?? 0, 2) }}"
                                            data-pg-fee="{{ number_format($pgFeePercent ?? 0, 2) }}">
                                            Calculate Margin
                                        </button>
                                        <br>
                                        @if(!$optIn)
                                            <button class="btn btn-secondary btn-sm" disabled>Opt-In Required</button>
                                        @elseif($isClosed)
                                            <button class="btn btn-secondary btn-sm" disabled>Closed</button>
                                        @elseif($isExpired)
                                            <button class="btn btn-secondary btn-sm" disabled>Expired</button>
                                        @elseif($isApproved)
                                            @if($isUpcoming)
                                                <button class="btn btn-warning btn-sm mb-1" disabled>Joined (Upcoming)</button>
                                            @else
                                                <button class="btn btn-success btn-sm mb-1" disabled>Joined</button>
                                            @endif
                                            <a href="{{ route('vendor.campaign.manage.products', $c->id) }}" class="btn btn-outline-primary btn-sm mb-1">Manage Products</a>
                                        @elseif($isExhausted)
                                            <button class="btn btn-secondary btn-sm" disabled>Budget Exhausted</button>
                                        @elseif($isPending)
                                            <button class="btn btn-warning btn-sm" disabled>Joined – Needs Approval</button>
                                        @elseif($isRejected)
                                            @if($c->budget_per_vendor > 0)

                                            <button class="btn btn-danger btn-sm" disabled>Rejected</button>


                                            @else
                                                <form method="POST" action="{{ route('vendor.campaigns.join') }}" class="d-flex gap-2 align-items-center">
                                                    @csrf
                                                    <input type="hidden" name="campaign_id" value="{{ $c->id }}">
                                                    <input type="text" name="budget" class="form-control" placeholder="Budget" required style="max-width:140px;" value="{{ $pivot['budget_total'] }}">
                                                    <button class="btn btn-sm btn-danger" type="submit">Rejected (Update)</button>
                                                </form>
                                            @endif
                                        @elseif(!$isExpired && !$isClosed)
                                            @if(!$joined && $isFull)
                                                <button class="btn btn-secondary btn-sm" disabled>Full</button>
                                            @elseif($c->budget_per_vendor > 0)
                                                <form method="POST" action="{{ route('vendor.campaigns.join') }}" class="d-flex gap-2 align-items-center">
                                                    @csrf
                                                    <input type="hidden" name="campaign_id" value="{{ $c->id }}">
                                                    <button class="btn btn-sm btn-primary" type="submit">Join @ {{ number_format($c->budget_per_vendor, 2) }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('vendor.campaigns.join') }}" class="d-flex gap-2 align-items-center">
                                                    @csrf
                                                    <input type="hidden" name="campaign_id" value="{{ $c->id }}">
                                                    <input type="text" name="budget" class="form-control" placeholder="Budget" required style="max-width:140px;">
                                                    <button class="btn btn-sm btn-primary" type="submit">Join / Update</button>
                                                </form>
                                            @endif
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled>Unavailable</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <div class="mt-3 d-flex justify-content-end">
                    {{ $campaigns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Vendor Margin Calculator Modal --}}
<div class="modal fade" id="marginCalcModal" tabindex="-1" aria-labelledby="marginCalcModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marginCalcModalLabel">Margin Calculator — <span id="calcCampaignName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Selling Price (NPR)</label>
                    <input type="number" step="0.01" min="0" id="calcSellingPrice" class="form-control" placeholder="e.g. 1000">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Vendor Cost (NPR)</label>
                    <input type="number" step="0.01" min="0" id="calcVendorCost" class="form-control" placeholder="e.g. 600">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Commission %</label>
                    <input type="number" step="0.01" min="0" id="calcCommission" class="form-control" placeholder="e.g. 10">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">PG %</label>
                    <input type="number" step="0.01" min="0" id="calcPgFee" class="form-control" placeholder="e.g. 2">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Campaign Discount %</label>
                    <input type="number" step="0.01" min="0" id="calcCampaignDiscount" class="form-control" placeholder="e.g. 15">
                </div>
                <hr>
                <div class="mb-2">
                    <strong>Standard Sale:</strong>
                    <div class="text-muted small">Selling Price − Commission − PG Fee − Vendor Cost = Net Profit</div>
                    <div id="calcStandardResult" class="mt-1 fw-bold"></div>
                </div>
                <div class="mb-2">
                    <strong>Campaign Sale:</strong>
                    <div class="text-muted small">Discounted Price − Commission − PG Fee − Vendor Cost = Net Profit</div>
                    <div id="calcCampaignResult" class="mt-1 fw-bold"></div>
                </div>
                <div id="calcWarning" class="alert alert-danger mt-2 d-none" role="alert">
                    This campaign will result in loss.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('marginCalcModal');
    if (!modal) return;
    const inputs = ['calcSellingPrice', 'calcVendorCost', 'calcCommission', 'calcPgFee', 'calcCampaignDiscount'];
    
    modal.addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        if (btn && btn.dataset.campaignName) {
            document.getElementById('calcCampaignName').textContent = btn.dataset.campaignName;
            document.getElementById('calcCommission').value = btn.dataset.commission || '';
            document.getElementById('calcPgFee').value = btn.dataset.pgFee || '';
            document.getElementById('calcCampaignDiscount').value = btn.dataset.discountPercent || '';
        }
        runMarginCalc();
    });
    
    inputs.forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', runMarginCalc);
    });
    
    function runMarginCalc() {
        const selling = parseFloat(document.getElementById('calcSellingPrice').value) || 0;
        const cost = parseFloat(document.getElementById('calcVendorCost').value) || 0;
        const commPct = parseFloat(document.getElementById('calcCommission').value) || 0;
        const pgPct = parseFloat(document.getElementById('calcPgFee').value) || 0;
        const discPct = parseFloat(document.getElementById('calcCampaignDiscount').value) || 0;
        
        const commission = (selling * commPct) / 100;
        const pgFee = (selling * pgPct) / 100;
        const standardNet = selling - commission - pgFee - cost;
        
        const discountedPrice = selling * (1 - discPct / 100);
        const campCommission = (discountedPrice * commPct) / 100;
        const campPgFee = (discountedPrice * pgPct) / 100;
        const campaignNet = discountedPrice - campCommission - campPgFee - cost;
        
        document.getElementById('calcStandardResult').textContent = selling.toFixed(2) + ' − ' + commission.toFixed(2) + ' − ' + pgFee.toFixed(2) + ' − ' + cost.toFixed(2) + ' = NPR ' + standardNet.toFixed(2);
        document.getElementById('calcCampaignResult').textContent = discountedPrice.toFixed(2) + ' − ' + campCommission.toFixed(2) + ' − ' + campPgFee.toFixed(2) + ' − ' + cost.toFixed(2) + ' = NPR ' + campaignNet.toFixed(2);
        
        const warn = document.getElementById('calcWarning');
        if (campaignNet < 0) {
            warn.classList.remove('d-none');
        } else {
            warn.classList.add('d-none');
        }
    }
});
</script>
@endpush
@endsection
