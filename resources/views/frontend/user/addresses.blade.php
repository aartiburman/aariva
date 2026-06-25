@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">My Addresses</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Addresses</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="card rounded-0">
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('frontend.user.profile') }}" class="list-group-item list-group-item-action">My Profile</a>
                            <a href="{{ route('frontend.user.orders') }}" class="list-group-item list-group-item-action">My Orders</a>
                            <a href="javascript:;" class="list-group-item list-group-item-action active">My Addresses</a>
                            <a href="{{ route('frontend.wishlist.index') }}" class="list-group-item list-group-item-action">Wishlist</a>
                            <a class="list-group-item list-group-item-action text-danger" href="{{ route('frontend.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card rounded-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h5 class="mb-0">My Addresses</h5>
                            <button class="btn btn-dark btn-ecomm" data-bs-toggle="modal" data-bs-target="#addAddressModal">Add New Address</button>
                        </div>
                        @if ($addresses->isEmpty())
                        <p class="text-muted">No addresses saved yet.</p>
                        @else
                        <div class="row g-3">
                            @foreach ($addresses as $addr)
                            <div class="col-md-6">
                                <div class="card rounded-0 border">
                                    <div class="card-body">
                                        <h6>{{ $addr->name }}</h6>
                                        <p class="mb-1">{{ $addr->phone }}</p>
                                        <p class="mb-1">{{ $addr->address }}</p>
                                        <p class="mb-1">{{ $addr->city }}, {{ $addr->state }} {{ $addr->zip }}</p>
                                        <p class="mb-0">{{ $addr->country }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addAddressForm">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <select name="country_id" class="form-select">
                            @foreach ($countries as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City ID</label>
                        <input type="number" name="city_id" class="form-control" placeholder="City ID" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Zip</label>
                        <input type="text" name="zip" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" id="saveAddressBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#saveAddressBtn').on('click', function() {
        var $btn = $(this).prop('disabled', true).html('Saving...');
        $.ajax({
            url: '{{ route("frontend.checkout.address.add") }}',
            method: 'POST',
            data: $('#addAddressForm').serialize() + '&_token={{ csrf_token() }}',
            success: function() {
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON.message || 'Failed to save address');
                $btn.prop('disabled', false).html('Save');
            }
        });
    });
});
</script>
@endpush
