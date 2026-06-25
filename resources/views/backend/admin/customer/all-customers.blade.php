@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <!-- Start Container Fluid -->
    <div class="container-fluid">
        <!-- Filter and Search Row -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 p-4">
                <form action="{{ route('all.customers') }}" method="POST" id="customer-filter-form">
                    @csrf
                    <div class="row align-items-end g-2">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-13 mb-1">Search Customer</label>
                            <input type="text" name="search" id="customer-search" class="form-control form-control-sm" placeholder="Search by name, email, phone..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-13 mb-1">Country</label>
                            <select name="country_id" id="country_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Countries</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-13 mb-1">State</label>
                            <select name="state_id" id="state_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All States</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-13 mb-1">City</label>
                            <select name="city_id" id="city_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Cities</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('all.customers') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">All Customers List</h4>

                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('export.customers', request()->all()) }}" id="export-link" class="btn btn-sm btn-outline-secondary no-loader">
                                <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                                <label class="form-check-label" for="checkAll"></label>
                                            </div>
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Gender</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('backend.admin.customer.partials.customer-table')
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-top d-flex justify-content-between align-items-center">
                        <p class="text-muted mb-0 fs-13" id="pagination-info">Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries</p>
                        <div class="pagination-container" id="pagination-links">
                            {{ $customers->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Container Fluid -->

</div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    const filterForm = $('#customer-filter-form');
    const searchInput = $('#customer-search');
    const tableBody = $('tbody');
    const countrySelect = $('#country_id');
    const stateSelect = $('#state_id');
    const citySelect = $('#city_id');
    const exportLink = $('#export-link');
    let debounceTimer;

    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    function fetchCustomers(url = null) {
        let formData = filterForm.serialize();
        $.ajax({
            url: url || filterForm.attr('action'),
            type: 'POST',
            data: formData,
            beforeSend: function() {
                tableBody.html('<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
            },
            success: function(response) {
                if (response.table !== undefined) {
                    tableBody.html(response.table);
                    $('#pagination-links').html(response.pagination);
                    $('#pagination-info').text(response.info);
                    
                    // Update Export Link
                    let exportUrl = "{{ route('export.customers') }}";
                    exportLink.attr('href', exportUrl + '?' + formData);
                } else {
                    location.reload();
                }
            },
            error: function() {
                location.reload();
            }
        });
    }

    filterForm.on('submit', function(e) {
        e.preventDefault();
        fetchCustomers();
    });

    filterForm.find('select').on('change', function() {
        if (!$(this).is(countrySelect) && !$(this).is(stateSelect)) {
            fetchCustomers();
        }
    });

    searchInput.on('keyup', debounce(function() {
        fetchCustomers();
    }, 3000));

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        fetchCustomers($(this).attr('href'));
    });

    $('#reset-filter').on('click', function() {
        filterForm[0].reset();
        stateSelect.html('<option value="">All States</option>');
        citySelect.html('<option value="">All Cities</option>');
        fetchCustomers();
    });

    // Initial load of states/cities if values are preset (from request)
    const selectedCountry = "{{ request('country_id') }}";
    const selectedState = "{{ request('state_id') }}";
    const selectedCity = "{{ request('city_id') }}";

    if (selectedCountry) {
        loadStates(selectedCountry, selectedState).then(() => {
            if (selectedState) {
                loadCities(selectedState, selectedCity);
            }
        });
    }

    countrySelect.on('change', function() {
        const countryId = $(this).val();
        stateSelect.html('<option value="">All States</option>');
        citySelect.html('<option value="">All Cities</option>');
        
        if (countryId) {
            loadStates(countryId).then(() => fetchCustomers());
        } else {
            fetchCustomers();
        }
    });

    stateSelect.on('change', function() {
        const stateId = $(this).val();
        citySelect.html('<option value="">All Cities</option>');
        
        if (stateId) {
            loadCities(stateId).then(() => fetchCustomers());
        } else {
            fetchCustomers();
        }
    });

    function loadStates(countryId, selectedId = null) {
        let url = "{{ route('get.states', ':country_id') }}";
        url = url.replace(':country_id', countryId);
        return $.ajax({
            url: url,
            type: "GET",
            success: function(response) {
                let options = '<option value="">All States</option>';
                if (response.status && response.data) {
                    response.data.forEach(state => {
                        const selected = (selectedId && selectedId == state.id) ? 'selected' : '';
                        options += `<option value="${state.id}" ${selected}>${state.name}</option>`;
                    });
                }
                stateSelect.html(options);
            }
        });
    }

    function loadCities(stateId, selectedId = null) {
        let url = "{{ route('get.cities', ':state_id') }}";
        url = url.replace(':state_id', stateId);
        return $.ajax({
            type: "GET",
            url: url,
            success: function(response) {
                let options = '<option value="">All Cities</option>';
                if (response.status && response.data) {
                    response.data.forEach(city => {
                        const selected = (selectedId && selectedId == city.id) ? 'selected' : '';
                        options += `<option value="${city.id}" ${selected}>${city.name}</option>`;
                    });
                }
                citySelect.html(options);
            }
        });
    }

    // Status toggle logic (from previous code)
    $(document).on('click', '.customer-status-container', function(e) {
        if ($(e.target).hasClass('customer-status-select')) return;
        $(this).find('.customer-status-badge').addClass('d-none');
        $(this).find('.customer-status-select').removeClass('d-none').focus();
    });

    $(document).on('change', '.customer-status-select', function() {
        const select = $(this);
        const id = select.closest('.customer-status-container').data('id');
        const status = select.val();
        
        $.post("{{ route('change.customer.status') }}", {
            id: id,
            status: status,
            _token: "{{ csrf_token() }}"
        }, function(res) {
            if (res.status) {
                toastr.success(res.message);
                fetchCustomers(); // Refresh to update badge correctly
            } else {
                toastr.error(res.message);
            }
        });
    });

    $(document).on('blur', '.customer-status-select', function() {
        $(this).addClass('d-none');
        $(this).siblings('.customer-status-badge').removeClass('d-none');
    });

    // Delete Logic
    $(document).on('click', '.delete-customer', function() {
        const id = $(this).data('id');
        const row = $(`#row_${id}`);
        
        if (confirm('Are you sure you want to delete this customer?')) {
            $.post("{{ route('delete.customer') }}", {
                id: id,
                _token: "{{ csrf_token() }}"
            }, function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    row.fadeOut(500, function() { $(this).remove(); });
                } else {
                    toastr.error(res.message);
                }
            });
        }
    });

    // Select All
    $(document).on('change', '#checkAll', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });
});
</script>
@endsection

