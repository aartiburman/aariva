@extends('backend.layouts.app')
@section('content')

<!-- [ Main Content ] start -->
<section class="pc-container">
    <div class="pc-content">

        <!-- Breadcrumb -->
        <div class="card">
            <div class="card-header">
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="page-header-title">
                                    <h5 class="mb-0">Add Email Template</h5>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <ul class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.dashboard') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">Email</li>
                                    <li class="breadcrumb-item active">
                                        Add Email Template
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-md-12">

                <div class="card">
                    <div class="card-header">
                        <h5>Add Email Template</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('store.email.template') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Template Name -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Template Name</label>
                                    <input type="text"
                                           name="name"
                                           class="form-control"
                                           placeholder="Order Confirmation"
                                           required>
                                </div>

                                <!-- Slug -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Slug</label>
                                    <input type="text"
                                           name="slug"
                                           class="form-control"
                                           placeholder="order_confirmation"
                                           readonly>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Subject -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Email Subject</label>
                                    <input type="text"
                                           name="subject"
                                           class="form-control"
                                           placeholder="Your order order_id is confirmed"
                                           required>
                                </div>

                                <!-- Status -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Email Body -->
                            <div class="mb-3">
                                <label class="form-label">Email Body</label>
                                <textarea name="body"
                                          class="form-control"
                                          rows="6"
                                          placeholder="Write email content here..."></textarea>
                            </div>

                            <!-- Variables -->
                            <div class="mb-3">
                                <label class="form-label">Available Variables</label>
                                <input type="text"
                                       name="variables"
                                       class="form-control"
                                       placeholder="name email order_id">
                                <small class="text-muted">
                                    Use comma separated variables
                                </small>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description"
                                          class="form-control"
                                          rows="2"
                                          placeholder="Template description (optional)"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <iconify-icon icon="solar:diskette-linear" class="me-1"></iconify-icon>
                                Save Email Template
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection
