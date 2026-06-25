@extends('backend.layouts.app')

@section('content')
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">All Customers List</h4>
                        <div class="d-flex align-items-center gap-2">
                            <form action="{{ route('all.customers') }}" method="GET" class="d-flex align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm me-1" placeholder="Search customers..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                                @if(request('search'))
                                <a href="{{ route('all.customers') }}" class="btn btn-sm btn-secondary ms-1">Clear</a>
                                @endif
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Customer ID</th>
                                        <th>Customer Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                    <tr>
                                        <td><a href="javascript:void(0);" class="fw-medium">#{{ $customer->id }}</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                        {{ substr($customer->name ?? 'C', 0, 1) }}
                                                    </div>
                                                </div>
                                                {{ $customer->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                                        <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @if($customer->status == 1)
                                            <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                            <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="javascript:void(0);" class="btn btn-soft-primary btn-sm"><i class="bx bx-show fs-16"></i></a>
                                                <a href="javascript:void(0);" class="btn btn-soft-info btn-sm"><i class="bx bx-edit fs-16"></i></a>
                                                <a href="javascript:void(0);" class="btn btn-soft-danger btn-sm"><i class="bx bx-trash fs-16"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} customers
                            </div>
                            <div>
                                {{ $customers->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Container Fluid -->

</div>
@endsection
