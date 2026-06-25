@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Refund Requests</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ url()->current() }}?export=1&{{ http_build_query(request()->except('export')) }}" id="export-link" class="btn btn-primary">
                            <iconify-icon icon="solar:download-linear" class="align-middle"></iconify-icon> Export CSV
                        </a>
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
        const filterForm = $('#filter-form');
        const searchInput = $('input[name="search"]');
        const tableContainer = $('#refunds-table-container');
        let debounceTimer;

        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }

        function fetchRefunds(url = null) {
            $.ajax({
                url: url || filterForm.attr('action'),
                type: 'POST',
                data: filterForm.serialize(),
                beforeSend: function() {
                    tableContainer.addClass('opacity-50');
                },
                success: function(response) {
                    if (response.table !== undefined) {
                        tableContainer.html(response.table);
                        // Assuming the pagination links are inside the partial table or we need to update them separately
                        // Looking at your previous code, it seems the table partial contains everything.
                    } else {
                        // Fallback if partial is returned as string
                        tableContainer.html(response);
                    }
                    tableContainer.removeClass('opacity-50');
                    updateExportLink(filterForm.serialize());
                },
                error: function() {
                    tableContainer.removeClass('opacity-50');
                }
            });
        }

        function updateExportLink(params) {
            var baseUrl = "{{ url()->current() }}";
            var exportUrl = baseUrl + '?export=1';
            if (params) {
                params = params.replace(/&?export=1&?/, '');
                if (params) exportUrl += '&' + params;
            }
            $('#export-link').attr('href', exportUrl);
        }

        filterForm.on('submit', function(e) {
            e.preventDefault();
            fetchRefunds();
        });

        $('select[name="status"]').on('change', function() {
            fetchRefunds();
        });

        searchInput.on('keyup', debounce(function() {
            fetchRefunds();
        }, 3000));

        $('.range-datepicker').on('change', function() {
            fetchRefunds();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            fetchRefunds($(this).attr('href'));
        });

        $('#reset-filter').on('click', function() {
            filterForm[0].reset();
            fetchRefunds();
        });
    });
</script>
@endpush
