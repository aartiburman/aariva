@extends('backend.layouts.app')
@section('content')

<!-- [ Main Content ] start -->
<section class="pc-container">
  <div class="pc-content">

    <div class="card">
      <div class="card-header">
        <div class="page-header">
          <div class="page-block">
            <div class="row align-items-center">
              <div class="col-md-12">
                <div class="page-header-title">
                  <h5 class="mb-0">Add Shipping Zone</h5>
                </div>
              </div>
              <div class="col-md-12">
                <ul class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0)">Shipping Zone</a></li>
                  <li class="breadcrumb-item" aria-current="page">Add Shipping Zone</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- [ breadcrumb ] end -->


    <!-- [ Main Content ] start -->
    <div class="row">
      <div class="col-md-12">


        <div class="card">
          <div class="card-header">
            <h5>Add Shipping Zone</h5>
          </div>
          <div class="card-body">
        <form action="{{ route('store.tax.rate') }}" method="POST">
            @csrf

            <div class="row">

                <!-- Tax Name -->
                <div class="mb-3 col-md-6">
                    <label class="form-label">Tax Name</label>
                    <input type="text"
                        name="name"
                        class="form-control"
                        placeholder="GST / VAT / Sales Tax"
                        required>
                </div>

                <!-- Slug -->
                <div class="mb-3 col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text"
                        name="slug"
                        class="form-control"
                        readonly
                        placeholder="auto-generated">
                </div>

            </div>

            <div class="row">

                <!-- Tax Percentage -->
                <div class="mb-3 col-md-6">
                    <label class="form-label">Tax Percentage (%)</label>
                    <input type="number"
                        step="0.01"
                        name="tax_percentage"
                        class="form-control"
                        placeholder="e.g. 18"
                        required>
                </div>

                <!-- Country -->
                <div class="mb-3 col-md-6">
                    <label class="form-label">Country</label>
                    <input type="text"
                        name="country"
                        class="form-control"
                        placeholder="India / USA">
                </div>

            </div>

            <div class="row">

                <!-- State -->
                <div class="mb-3 col-md-6">
                    <label class="form-label">State</label>
                    <input type="text"
                        name="state"
                        class="form-control"
                        placeholder="Optional (State-wise tax)">
                </div>

            </div>

            <!-- Status -->
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        name="is_active"
                        value="1"
                        checked>
                    <label class="form-check-label">Active</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Save Tax Rate
            </button>
        </form>

          </div>
        </div>

      </div>

      <!-- [ form-element ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->
</section>
<!-- [ Main Content ] end -->
@endsection
