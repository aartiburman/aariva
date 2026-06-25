@extends('backend.layouts.app')
@section('content')
<section class="pc-container">
  <div class="pc-content">
    <div class="card">
      <div class="card-header">
        <div class="page-header">
          <div class="page-block">
            <div class="row align-items-center">
              <div class="col-md-12">
                <div class="page-header-title">
                  <h5 class="mb-0">Edit Contact Detail</h5>
                </div>
              </div>
              <div class="col-md-12">
                <ul class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0)">Contact Details</a></li>
                  <li class="breadcrumb-item" aria-current="page">Edit Contact Detail</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5>Edit Contact Detail</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('update.contact.detail') }}" method="POST">
              @csrf
              <input type="hidden" name="id" value="{{ $contact->id }}">
              <div class="row">
                <div class="mb-3 col-md-4">
                  <label class="form-label">Country</label>
                  <select name="country_id" class="form-select country_id">
                    <option value="">Select Country</option>
                    @foreach($countries as $country)
                      <option value="{{ $country->id }}" {{ $contact->country_id == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3 col-md-4">
                  <label class="form-label">State</label>
                  <select name="state_id" class="form-select state_id">
                    <option value="">Select State</option>
                    @foreach($states as $state)
                      <option value="{{ $state->id }}" {{ $contact->state_id == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-3 col-md-4">
                  <label class="form-label">City</label>
                  <select name="city_id" class="form-select city_id">
                    <option value="">Select City</option>
                    @foreach($cities as $city)
                      <option value="{{ $city->id }}" {{ $contact->city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Title</label>
                  <input type="text" name="title" value="{{ old('title', $contact->title) }}" class="form-control @error('title') is-invalid @enderror">
                  @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" value="{{ old('email', $contact->email) }}" class="form-control @error('email') is-invalid @enderror">
                  @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Phone</label>
                  <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                  @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label">WhatsApp</label>
                  <input type="text" name="whatsapp" value="{{ old('whatsapp', $contact->whatsapp) }}" class="form-control @error('whatsapp') is-invalid @enderror">
                  @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" value="{{ old('address', $contact->address) }}" class="form-control @error('address') is-invalid @enderror">
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="row">
                <div class="mb-3 col-md-3">
                  <label class="form-label">City</label>
                  <input type="text" name="city" value="{{ old('city', $contact->city) }}" class="form-control @error('city') is-invalid @enderror">
                  @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-3">
                  <label class="form-label">State</label>
                  <input type="text" name="state" value="{{ old('state', $contact->state) }}" class="form-control @error('state') is-invalid @enderror">
                  @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-3">
                  <label class="form-label">Country</label>
                  <input type="text" name="country" value="{{ old('country', $contact->country) }}" class="form-control @error('country') is-invalid @enderror">
                  @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-3">
                  <label class="form-label">Postal Code</label>
                  <input type="text" name="postal_code" value="{{ old('postal_code', $contact->postal_code) }}" class="form-control @error('postal_code') is-invalid @enderror">
                  @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Map URL</label>
                  <input type="url" name="map_url" value="{{ old('map_url', $contact->map_url) }}" class="form-control @error('map_url') is-invalid @enderror">
                  @error('map_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label">Opening Hours</label>
                  <input type="text" name="opening_hours" value="{{ old('opening_hours', $contact->opening_hours) }}" class="form-control @error('opening_hours') is-invalid @enderror">
                  @error('opening_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="row">
                <div class="mb-3 col-md-4">
                  <label class="form-label">Facebook URL</label>
                  <input type="url" name="facebook_url" value="{{ old('facebook_url', $contact->facebook_url) }}" class="form-control @error('facebook_url') is-invalid @enderror">
                  @error('facebook_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-4">
                  <label class="form-label">Instagram URL</label>
                  <input type="url" name="instagram_url" value="{{ old('instagram_url', $contact->instagram_url) }}" class="form-control @error('instagram_url') is-invalid @enderror">
                  @error('instagram_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-4">
                  <label class="form-label">Twitter URL</label>
                  <input type="url" name="twitter_url" value="{{ old('twitter_url', $contact->twitter_url) }}" class="form-control @error('twitter_url') is-invalid @enderror">
                  @error('twitter_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Order</label>
                  <input type="number" name="order_by" value="{{ old('order_by', $contact->order_by) }}" class="form-control @error('order_by') is-invalid @enderror">
                  @error('order_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="1" {{ old('status', $contact->status) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $contact->status) == 0 ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Update Contact Detail</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

