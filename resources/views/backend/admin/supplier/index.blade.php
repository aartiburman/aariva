@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Suppliers</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                            <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add Supplier
                        </button>
                        <a href="{{ route('supplier.purchase.orders') }}" class="btn btn-sm btn-outline-primary">Purchase Orders</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" id="filter-form" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, company, email, phone..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr><th>Name</th><th>Company</th><th>Contact</th><th>Email</th><th>Phone</th><th>Products</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody id="table-body">
                            @include('backend.admin.supplier.partials.suppliers-table')
                        </tbody>
                    </table>
                </div>
            </div>
            @if($suppliers->hasPages())<div class="card-footer">{{ $suppliers->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('supplier.store') }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Name</label>
                            <input type="text" name="name" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" maxlength="255">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" maxlength="20">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country</label>
                            <select name="country_id" class="form-select country-select">
                                <option value="">Select Country</option>
                                @foreach($countries as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <select name="state_id" class="form-select state-select">
                                <option value="">Select Country First</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <select name="city_id" class="form-select city-select">
                                <option value="">Select State First</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">GST/VAT Number</label>
                            <input type="text" name="gst_number" class="form-control" maxlength="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" maxlength="255">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="editSupplierForm">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Edit Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body" id="editSupplierBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if (typeof initAjaxFilter === 'function') {
        initAjaxFilter('#filter-form', '#table-body', null, 'suppliers');
    }

    const countries = @json($countries);

    function loadStates(countryId, selectedStateId) {
        const stateSelect = $('.edit-state-select');
        stateSelect.html('<option value="">Loading...</option>');
        if (!countryId) {
            stateSelect.html('<option value="">Select Country First</option>');
            return;
        }
        $.get('{{ url("get-states") }}/' + countryId, function(states) {
            let html = '<option value="">Select State</option>';
            states.forEach(s => {
                html += `<option value="${s.id}" ${s.id == selectedStateId ? 'selected' : ''}>${s.name}</option>`;
            });
            stateSelect.html(html);
            if (selectedStateId) loadCities(selectedStateId);
        });
    }

    function loadCities(stateId, selectedCityId) {
        const citySelect = $('.edit-city-select');
        citySelect.html('<option value="">Loading...</option>');
        if (!stateId) {
            citySelect.html('<option value="">Select State First</option>');
            return;
        }
        $.get('{{ url("get-cities") }}/' + stateId, function(cities) {
            let html = '<option value="">Select City</option>';
            cities.forEach(c => {
                html += `<option value="${c.id}" ${c.id == selectedCityId ? 'selected' : ''}>${c.name}</option>`;
            });
            citySelect.html(html);
        });
    }

    $('.edit-supplier').on('click', function() {
        const data = $(this).data('supplier');
        const baseFields = ['name', 'company_name', 'email', 'phone', 'address', 'gst_number', 'contact_person', 'notes'];
        let html = '';
        baseFields.forEach(f => {
            const label = f.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const required = f === 'name' ? 'required' : '';
            html += `<div class="mb-2">
                <label class="form-label${f === 'name' ? ' required' : ''}">${label}</label>
                <input type="text" name="${f}" class="form-control" value="${data[f] || ''}" ${required} maxlength="255">
            </div>`;
        });
        html += `<div class="mb-2">
            <label class="form-label">Country</label>
            <select name="country_id" class="form-select edit-country-select">
                <option value="">Select Country</option>
                ${countries.map(c => `<option value="${c.id}" ${c.id == data.country_id ? 'selected' : ''}>${c.name}</option>`).join('')}
            </select>
        </div>
        <div class="mb-2">
            <label class="form-label">State</label>
            <select name="state_id" class="form-select edit-state-select">
                <option value="">${data.state_id ? 'Loading...' : 'Select Country First'}</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="form-label">City</label>
            <select name="city_id" class="form-select edit-city-select">
                <option value="">${data.city_id ? 'Loading...' : 'Select State First'}</option>
            </select>
        </div>`;
        $('#editSupplierBody').html(html);
        $('#editSupplierForm').attr('action', '{{ url("suppliers") }}/' + data.id);
        $('#editSupplierModal').modal('show');

        if (data.country_id) loadStates(data.country_id, data.state_id);
        if (data.state_id) loadCities(data.state_id, data.city_id);

        $('.edit-country-select').on('change', function() {
            loadStates($(this).val());
        });
        $(document).on('change', '.edit-state-select', function() {
            loadCities($(this).val());
        });
    });

    // Add form dependent dropdowns
    $('.country-select').on('change', function() {
        const countryId = $(this).val();
        const stateSelect = $(this).closest('.row').find('.state-select');
        const citySelect = $(this).closest('.row').find('.city-select');
        stateSelect.html('<option value="">Loading...</option>');
        citySelect.html('<option value="">Select State First</option>');
        if (!countryId) {
            stateSelect.html('<option value="">Select Country First</option>');
            return;
        }
        $.get('{{ url("get-states") }}/' + countryId, function(states) {
            let html = '<option value="">Select State</option>';
            states.forEach(s => {
                html += `<option value="${s.id}">${s.name}</option>`;
            });
            stateSelect.html(html);
        });
    });

    $(document).on('change', '.state-select', function() {
        const stateId = $(this).val();
        const citySelect = $(this).closest('.row').find('.city-select');
        citySelect.html('<option value="">Loading...</option>');
        if (!stateId) {
            citySelect.html('<option value="">Select State First</option>');
            return;
        }
        $.get('{{ url("get-cities") }}/' + stateId, function(cities) {
            let html = '<option value="">Select City</option>';
            cities.forEach(c => {
                html += `<option value="${c.id}">${c.name}</option>`;
            });
            citySelect.html(html);
        });
    });
});
</script>
@endpush
