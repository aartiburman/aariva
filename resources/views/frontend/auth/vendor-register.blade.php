@extends('frontend.layouts.app')

@section('title', __t('Become a Seller'))

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">{{ __t('Become a Seller') }}</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> {{ __t('Home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __t('Become a Seller') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-0 py-lg-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="text-center mb-4">
                                <h3>{{ __t('Seller Registration') }}</h3>
                                <p class="text-muted">{{ __t('Fill in your details to start selling on our platform') }}</p>
                            </div>
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                                </div>
                            @endif
                            <div class="form-body">
                                <form class="row g-3" method="POST" action="{{ route('frontend.become-seller') }}" enctype="multipart/form-data" id="sellerForm">
                                    @csrf
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2">{{ __t('Account Information') }}</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Full Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ __t('Full Name') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Email Address') }} <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="example@user.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Phone') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="{{ __t('Phone Number') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Password') }} <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" placeholder="{{ __t('Password') }}" required minlength="8">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Confirm Password') }} <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __t('Confirm Password') }}" required equalto="#password">
                                    </div>
                                    <div class="col-12 mt-3">
                                        <h5 class="border-bottom pb-2">{{ __t('Store Information') }}</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Store Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="store_name" class="form-control" value="{{ old('store_name') }}" placeholder="{{ __t('Store Name') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __t('Business Name') }}</label>
                                        <input type="text" name="business_name" class="form-control" value="{{ old('business_name') }}" placeholder="{{ __t('Business Name') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __t('Address') }}</label>
                                        <textarea name="address" class="form-control" rows="2" placeholder="{{ __t('Your business address') }}">{{ old('address') }}</textarea>
                                    </div>
                                    @if ($kycDocuments->count() > 0)
                                    <div class="col-12 mt-3">
                                        <h5 class="border-bottom pb-2">{{ __t('KYC Documents') }}</h5>
                                    </div>
                                    @foreach ($kycDocuments as $doc)
                                    <div class="col-md-6">
                                        <label class="form-label">{{ $doc->name }} <span class="text-danger">*</span></label>
                                        <input type="file" name="documents[{{ $doc->id }}]" class="form-control" accept=".pdf,.jpeg,.png,.jpg,.doc,.docx" required>
                                        <small class="text-muted">{{ __t('PDF, JPG, PNG, DOC — max 5MB') }}</small>
                                    </div>
                                    @endforeach
                                    @endif
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-dark btn-ecomm"><i class="bx bx-store"></i> {{ __t('Register as Seller') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script>
$(document).ready(function() {
    $('#sellerForm').validate({
        rules: {
            name: { required: true },
            email: { required: true, email: true },
            phone: { required: true },
            password: { required: true, minlength: 8 },
            password_confirmation: { required: true, equalTo: '[name="password"]' },
            store_name: { required: true }
        },
        messages: {
            name: '{{ __t("Please enter your full name") }}',
            email: {
                required: '{{ __t("Please enter your email address") }}',
                email: '{{ __t("Please enter a valid email address") }}'
            },
            phone: '{{ __t("Please enter your phone number") }}',
            password: {
                required: '{{ __t("Please enter a password") }}',
                minlength: '{{ __t("Password must be at least 8 characters") }}'
            },
            password_confirmation: {
                required: '{{ __t("Please confirm your password") }}',
                equalTo: '{{ __t("Passwords do not match") }}'
            },
            store_name: '{{ __t("Please enter your store name") }}'
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) { $(element).addClass('is-invalid'); },
        unhighlight: function(element) { $(element).removeClass('is-invalid'); }
    });

    @if ($kycDocuments->count() > 0)
    $.each({!! json_encode($kycDocuments->pluck('name', 'id')) !!}, function(id, name) {
        $('[name="documents[' + id + ']"]').rules('add', {
            required: true,
            extension: 'pdf|jpeg|png|jpg|doc|docx',
            messages: {
                required: '{{ __t("Please upload") }} ' + name,
                extension: '{{ __t("Allowed formats: PDF, JPG, PNG, DOC") }}'
            }
        });
    });
    @endif
});
</script>
@endpush
