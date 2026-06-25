@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Refund Requests</h4>
                    <div class="d-flex gap-2">
                       
                        <form action="{{ url()->current() }}" method="GET" class="d-inline">
                            @foreach(request()->except('export') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" name="export" value="1" class="btn btn-primary">
                                <iconify-icon icon="solar:download-linear" class="align-middle"></iconify-icon> Export CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class=" {{ (request('search') || request('date_range') || request('status') !== null) ? 'show' : '' }}">
            <div class="card border-0 shadow-sm mb-4 bg-light-subtle">
                <div class="card-body">
                    <form action="{{ url()->current() }}" method="POST" id="filter-form">
                        @csrf
                        <div class="row align-items-end g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by ID, User, Vendor, Product..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date Range</label>
                                <input type="text" name="date_range" class="form-control range-datepicker" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Approved</option>
                                    <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <!-- <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button> -->
                                <button type="button" id="reset-filter" class="btn btn-sm btn-outline-secondary w-100">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-primary">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div id="refunds-table-container">
                            @include('backend.admin.refund.partials.table')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var typingTimer;
        var doneTypingInterval = 500;
        var $input = $('input[name="search"]');

        // Handle filter form submission
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            fetchRefunds($(this).attr('action'), $(this).serialize());
        });

        // Handle pagination clicks
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            fetchRefunds($(this).attr('href'));
        });

        // Handle status select change
        $('select[name="status"]').on('change', function() {
            $('#filter-form').submit();
        });

        // Handle search input typing
        $input.on('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        });

        $input.on('keydown', function () {
            clearTimeout(typingTimer);
        });

        function doneTyping () {
            $('#filter-form').submit();
        }

        // Handle reset button
        $('#reset-filter').on('click', function() {
            // Reset form fields
            $('#filter-form')[0].reset();
            $('input[name="search"]').val('');
            $('input[name="date_range"]').val('');
            $('select[name="status"]').val('');
            
            // Clear URL parameters
            var url = window.location.href.split('?')[0];
            window.history.pushState({}, document.title, url);
            
            // Fetch initial data
            fetchRefunds(url);
        });

        function fetchRefunds(url, data = {}) {
            // Include CSRF token
            if (typeof data === 'string') {
                if (data.indexOf('_token') === -1) {
                    data += '&_token=' + $('meta[name="csrf-token"]').attr('content');
                }
            } else {
                data._token = $('meta[name="csrf-token"]').attr('content');
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                beforeSend: function() {
                    $('#refunds-table-container').addClass('opacity-50');
                },
                success: function(response) {
                    $('#refunds-table-container').html(response).removeClass('opacity-50');
                },
                error: function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Something went wrong. Please try again.');
                    }
                    $('#refunds-table-container').removeClass('opacity-50');
                }
            });
        }
    });
</script>
@endpush
