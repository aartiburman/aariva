@extends('backend.layouts.app')
@section('content')

<div class="page-content">
  <!-- Start Container Fluid -->
  <div class="container">
    <div class="row">
      <div class="col-xl-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-1 anchor" id="basic">
              Update Profile
            </h5>

            <div class="card-body">
              <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" id="adminProfileForm" class="no-loader">
                @csrf
                @method('Post')

                <div class="row">

                  <!-- Name -->
                  <div class="mb-3 col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text"
                      name="name"
                      id="name"
                      class="form-control"
                      value="{{ $admin_info->name }}"
                      required>
                    <span class="text-danger error-text name_error"></span>
                  </div>

                  <!-- Email -->
                  <div class="mb-3 col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email"
                      name="email"
                      class="form-control"
                      value="{{ $admin_info->email }}"
                      readonly>
                  </div>

                </div>

                <div class="row">

                  <!-- Phone -->
                  <div class="mb-3 col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text"
                      name="phone"
                      id="phone"
                      class="form-control"
                      value="{{ $admin_info->phone ?? '' }}">
                    <span class="text-danger error-text phone_error"></span>
                  </div>

                  <!-- Profile Image -->
                  <div class="mb-3 col-md-6">
                    <label class="form-label">Profile Image</label>
                    <input type="file" name="image" class="form-control">
                    @if(!empty($admin_info->image))
                    <img src="{{ $admin_info->image }}" alt="Profile Image" class="img-thumbnail mt-2" width="100">
                    @endif
                  </div>

                </div>

                <!-- Address -->
                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <textarea name="address" id="address" class="form-control" rows="3">{{ $admin_info->address ?? '' }}</textarea>
                  <span class="text-danger error-text address_error"></span>
                </div>

                <div class="text-end mt-4">
                  <button type="submit" class="btn btn-primary px-sm-4">
                    Update Profile
                  </button>
                </div>

              </form>
            </div>
          </div>
        </div>


      </div> <!-- end col -->


    </div> <!-- end row -->
  </div>
  <!-- End Container Fluid -->


</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $('#adminProfileForm').on('submit', function (e) {

        let isValid = true;

        // Clear old errors
        $('.error-text').text('');

        let name = $('#name').val().trim();
        let phone = $('#phone').val().trim();
        let address = $('#address').val().trim();

        // Name validation
        if (name === '') {
            $('.name_error').text('Full Name is required.');
            isValid = false;
        }

        // Phone validation (only digits + 10 digit)
        if (phone !== '') {

            let phoneRegex = /^[0-9]+$/;

            if (!phoneRegex.test(phone)) {
                $('.phone_error').text('Phone number must contain only digits.');
                isValid = false;
            }
            else if (phone.length > 12) {
                $('.phone_error').text('Phone number must not exceed 12 digits.');
                isValid = false;
            }
        }

        // Address validation
        if (address === '') {
            $('.address_error').text('Address is required.');
            isValid = false;
        }

        // Prevent form submit if invalid
        if (!isValid) {
            e.preventDefault();
        }

    });

});
</script>
@endpush
