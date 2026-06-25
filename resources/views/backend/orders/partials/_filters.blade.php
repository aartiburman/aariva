<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <form action="{{ url()->current() }}" method="POST" id="filter-form">
            @csrf
            <div class="row align-items-end g-2">
                <div class="col-md">
                    <label class="form-label fw-semibold fs-13 mb-1">Search Orders</label>
                    <input type="text" name="search" id="order-search" class="form-control form-control-sm" placeholder="Search ref, user, product..." value="{{ request('search') }}">
                </div>

                @if(Auth::user()->role == '1' && isset($vendors))
                <div class="col-md">
                    <label class="form-label fw-semibold fs-13 mb-1">Store Name</label>
                    <select name="vendor_id" class="form-select form-select-sm filter-change">
                        <option value="">All Store</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->store_name ?? $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md">
                    <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                    <input type="text" name="date_range" class="form-control form-control-sm range-datepicker" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                </div>
                
                @if(!isset($hide_status_filter))
                <div class="col-md">
                    <label class="form-label fw-semibold fs-13 mb-1">Order Status</label>
                    <select name="status" class="form-select form-select-sm filter-change">
                        <option value="">All Status</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pending</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Confirmed</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Shipped</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Delivered</option>
                        <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Cancelled</option>
                        <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Returned</option>
                        <option value="6" {{ request('status') == '6' ? 'selected' : '' }}>In Dispute</option>
                    </select>
                </div>
                @endif

                <div class="col-md">
                    <label class="form-label fw-semibold fs-13 mb-1">Payment Mode</label>
                    <select name="payment_mode" class="form-select form-select-sm filter-change">
                        <option value="">All Mode</option>
                        <option value="COD" {{ request('payment_mode') == 'COD' ? 'selected' : '' }}>COD</option>
                        <option value="PayPal" {{ request('payment_mode') == 'PayPal' ? 'selected' : '' }}>PayPal</option>
                        <option value="Khati" {{ request('payment_mode') == 'Khati' ? 'selected' : '' }}>Khati</option>
                        <option value="eSeva" {{ request('payment_mode') == 'eSeva' ? 'selected' : '' }}>eSeva</option>
                    </select>
                </div>

                <div class="col-md">
                    <label class="form-label fw-semibold fs-13 mb-1">Pay Status</label>
                    <select name="payment_status" class="form-select form-select-sm filter-change">
                        <option value="">All Status</option>
                        <option value="0" {{ request('payment_status') == '0' ? 'selected' : '' }}>Unpaid</option>
                        <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <div class="col-auto">
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if(!isset($use_ajax) || !$use_ajax)
@push('scripts')
<script>
    $(document).ready(function() {
        let debounceTimer;
        const form = $('#filter-form');
        const searchInput = $('#order-search');
        const dateRangeInput = $('.range-datepicker');

        function submitForm() {
            form.trigger('submit');
        }

        if (searchInput.length) {
            searchInput.on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitForm, 500);
            });

            // Set cursor to the end of input after reload if searching
            if (searchInput.val().length > 0) {
                const val = searchInput.val();
                searchInput.focus().val('').val(val);
            }
        }

        if (dateRangeInput.length) {
            dateRangeInput.on('change', function() {
                submitForm();
            });
        }
        
        $(document).on('change', '.filter-change', function() {
            submitForm();
        });
    });
</script>
@endpush
@endif
