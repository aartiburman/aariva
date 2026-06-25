@extends('backend.layouts.app')
@section('content')

<!-- [ Main Content ] start -->
<section class="pc-container">
    <div class="pc-content">

        <!-- [ breadcrumb ] start -->
        <div class="card">
            <div class="card-header">
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="page-header-title">
                                    <h5 class="mb-0">Email Template List</h5>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <ul class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.dashboard') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="javascript:void(0)">Email</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        Email Templates
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <div class="row">
            <div class="col-xl-12">

                <div class="card">
                    <div class="card-header">
                        <h5 style="display:inline-block;">Email Template List</h5>

                        <a href="{{ route('add.email.template') }}"
                           class="btn btn-success d-inline-flex"
                           style="float:right;color:white;">
                            <iconify-icon icon="solar:plus-linear" class="me-1"></iconify-icon>Create Email Template
                        </a>
                    </div>

                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatables" id="pc-dt-filter">
                                <thead>
                                    <tr>
                                        <th>
                                            <label class="custom-checkbox">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                            </label>
                                        </th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Variables</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {{-- Example Row --}}
                                    <tr>
                                        <td>
                                            <label class="custom-checkbox">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                            </label>
                                        </td>

                                        <td>Order Placed</td>
                                        <td>order_placed</td>
                                        <td>Your order_id is Confirmed</td>

                                        <td>
                                            <span class="badge bg-success">Active</span>
                                        </td>

                                        <td>
                                            <code>order id</code>
                                        </td>

                                        <td>2025-05-05</td>

                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="#" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Template">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="#" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Delete Template">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- @foreach($emailTemplates as $template) --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
<!-- [ Main Content ] end -->

@endsection
