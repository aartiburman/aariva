@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">WhatsApp Message Log</h4>
                    <div class="page-title-right">
                        <a href="{{ route('whatsapp.settings') }}" class="btn btn-sm btn-outline-secondary">Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" id="filter-form" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Range</label>
                        <input type="text" name="date_range" class="form-control" id="date_range" value="{{ request('date_range') }}" placeholder="Select date range">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Recipient or message..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr><th>#</th><th>Recipient</th><th>Message</th><th>Template</th><th>Order</th><th>Status</th><th>Sent At</th></tr>
                        </thead>
                        <tbody id="table-body">
                            @include('backend.admin.whatsapp.partials.messages-table')
                        </tbody>
                    </table>
                </div>
            </div>
            @if($messages->hasPages())<div class="card-footer">{{ $messages->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if (typeof initAjaxFilter === 'function') {
        initAjaxFilter('#filter-form', '#table-body', null, 'messages');
    }
    if ($('#date_range').length && typeof moment !== 'undefined') {
        $('#date_range').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        });
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });
        $('#date_range').on('cancel.daterangepicker', function() { $(this).val(''); });
    }
});
</script>
@endpush
