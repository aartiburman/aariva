@extends('backend.layouts.app')

@section('content')
<div class="page-content">
     <div class="container-fluid">
          <div class="row g-2 mb-4">
            <div class="col-12 col-md-12">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Delivery Settings</h4>
                                </div>

                                <div class="card-body">
                                    @if(session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    @if($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('vendor.delivery.settings.update') }}">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <label for="delivery_days">Expected Delivery Days</label>
                                            <input id="delivery_days" type="text" class="form-control" name="delivery_days" 
                                                   value="{{ old('delivery_days', $vendor_data->delivery_days ?? '2-3') }}" 
                                                   placeholder="e.g. 2-3, 5, 1-2" required autofocus>
                                            <small class="form-text text-muted">Enter the number of days or a range (e.g., 2-3) it usually takes to deliver an order.</small>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Update Delivery Days</button>
                                            <a href="{{ route('vendor.dashboard') }}" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
