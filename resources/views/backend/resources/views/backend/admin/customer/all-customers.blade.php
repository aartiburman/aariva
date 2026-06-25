@extends('backend.layouts.app')

@section('content')

<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <!-- Filter and Search Row -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4">
                <form action="{{ route('all.customers') }}" method="GET" id="customer-filter-form">
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
                            <a href="{{ route('export.customers', request()->all()) }}" class="btn btn-sm btn-outline-secondary">
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
                                                <input type="checkbox" class="form-check-input" id="customCheck1">
                                                <label class="form-check-label" for="customCheck1"></label>
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
                                    @foreach ($customers as $value)
                                    <tr id="row_{{ $value->id }}">
                                        <td>
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input" id="customCheck{{ $value->id }}">
                                                <label class="form-check-label" for="customCheck{{ $value->id }}"></label>
                                            </div>
                                        </td>
                                        <td>{{ $value->name }}</td>
                                        <td>{{ $value->email }}</td>
                                        <td>{{ $value->phone }}</td>
                                        <td>{{ $value->gender ?? 'N/A' }}</td>
                                        <td>
                                            <div class="customer-status-container" data-id="{{ $value->id }}" style="cursor: pointer;">
                                                @if($value->status == 1)
                                                <span class="badge bg-success-subtle text-success py-1 px-2 fs-11 text-uppercase customer-status-badge">Active</span>
                                                @elseif($value->status == 0)
                                                <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-11 text-uppercase customer-status-badge">Pending</span>
                                                @else
                                                <span class="badge bg-danger-subtle text-danger py-1 px-2 fs-11 text-uppercase customer-status-badge">Rejected</span>
                                                @endif

                                                <select class="form-select form-select-sm customer-status-select d-none">
                                                    <option value="1" @selected($value->status == 1)>Active</option>
                                                    <option value="0" @selected($value->status == 0)>Pending</option>
                                                    <option value="2" @selected($value->status == 2)>Reject</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('customer.detail', $value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Detail">
                                                    <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-customer" data-id="{{ $value->id }}" data-bs-toggle="tooltip" title="Delete">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-top">
                        {{ $customers->links('pagination::bootstrap-5') }}
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
    const countrySelect = $('#country_id');
    const stateSelect = $('#state_id');
    const citySelect = $('#city_id');

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
            loadStates(countryId);
        }
    });

    stateSelect.on('change', function() {
        const stateId = $(this).val();
        citySelect.html('<option value="">All Cities</option>');
        
        if (stateId) {
            loadCities(stateId);
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
});
</script>
@endsection

