@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">My Profile</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Profile</li>
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
                            <a href="{{ route('frontend.user.profile') }}" class="list-group-item list-group-item-action active">My Profile</a>
                            <a href="{{ route('frontend.user.orders') }}" class="list-group-item list-group-item-action">My Orders</a>
                            <a href="{{ route('frontend.wishlist.index') }}" class="list-group-item list-group-item-action">Wishlist</a>
                            <a class="list-group-item list-group-item-action text-danger" href="{{ route('frontend.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="card rounded-0">
                    <div class="card-body">
                        <h5 class="mb-4">Profile Information</h5>
                        <form method="POST" action="{{ route('frontend.user.profile.update') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="{{ old('dob', $user->dob ? date('Y-m-d', strtotime($user->dob)) : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Country</label>
                                    <select name="country_id" class="form-select">
                                        <option value="">Select Country</option>
                                        @foreach ($countries as $c)
                                        <option value="{{ $c->id }}" {{ $user->country_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">State</label>
                                    <select name="state_id" class="form-select">
                                        <option value="">Select State</option>
                                        @foreach ($states as $s)
                                        <option value="{{ $s->id }}" {{ $user->state_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City</label>
                                    <select name="city_id" class="form-select">
                                        <option value="">Select City</option>
                                        @foreach ($cities as $ci)
                                        <option value="{{ $ci->id }}" {{ $user->city_id == $ci->id ? 'selected' : '' }}>{{ $ci->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Zip Code</label>
                                    <input type="text" name="zip" class="form-control" value="{{ old('zip', $user->zip) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-dark btn-ecomm">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
