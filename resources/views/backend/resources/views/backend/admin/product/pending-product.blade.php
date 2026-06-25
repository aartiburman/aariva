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
                                    <h5 class="mb-0">Product List</h5>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <ul class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="javascript: void(0)">List</a></li>
                                    <li class="breadcrumb-item" aria-current="page">Product List</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->



        <div class="row">
            <!-- [ basic-table ] start -->
            <div class="col-xl-12">
                <!-- <div class="card">
                     <div class="card-header">
                         <h5>Inline Form</h5>
                     </div>
                     <div class="card-body">
                         <form class="row row-cols-md-auto g-3 align-items-center">
                             <div class="col-12">
                                 <label class="visually-hidden" for="inlineFormInputName">Name</label>
                                 <input type="text" class="form-control" id="inlineFormInputName" placeholder="Jane Doe">
                             </div>
                             <div class="col-12">
                                 <label class="visually-hidden" for="inlineFormInputGroupUsername">Username</label>
                                 <div class="input-group">
                                     <div class="input-group-text">@</div>
                                     <input type="text" class="form-control" id="inlineFormInputGroupUsername" placeholder="Username">
                                 </div>
                             </div>
                             <div class="col-12">
                                 <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
                                 <select class="form-select" id="inlineFormSelectPref">
                                     <option selected="">Choose...</option>
                                     <option value="1">One</option>
                                     <option value="2">Two</option>
                                     <option value="3">Three</option>
                                 </select>
                             </div>
                             <div class="col-12">
                                 <div class="form-check">
                                     <input class="form-check-input" type="checkbox" id="inlineFormCheck">
                                     <label class="form-check-label" for="inlineFormCheck"> Remember me </label>
                                 </div>
                             </div>
                             <div class="col-12">
                                 <button type="submit" class="btn btn-primary">Submit</button>
                             </div>
                         </form>
                     </div>
                 </div> -->

                <div class="card">
                    <div class="card-header">
                        <h5 style="display:inline-block;">Product List</h5>


                        <a href="{{route('add.product')}}" class="btn btn-success d-inline-flex" style="float:right;color: white;">
                            <iconify-icon icon="solar:plus-linear" class="me-1"></iconify-icon>Add Product</a>
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

                                        <th>Product Name</th>
                                        <th>Variant Count</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($products as $value)

                                    <tr id="row_{{ $value->id }}">
                                        <td>
                                            <label class="custom-checkbox">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                            </label>
                                        </td>

                                        <!-- CLICKABLE PRODUCT NAME -->
                                        <td>
                                            <span
                                                class="toggle-variant"
                                                data-id="{{ $value->id }}">
                                                <strong>{{ $value->name }}</strong>
                                                <iconify-icon icon="solar:alt-arrow-down-linear" class="ms-1"></iconify-icon>
                                            </span>
                                            @if($value->available_offers && $value->available_offers->count() > 0)
                                            <div class="mt-1">
                                                @foreach($value->available_offers as $offer)
                                                <span class="badge bg-soft-info text-info border border-info border-opacity-25 fs-11" data-bs-toggle="tooltip" title="{{ $offer->type == 1 ? $offer->value . '%' : '₹' . $offer->value }} OFF">
                                                    <iconify-icon icon="solar:tag-linear" class="align-middle me-1"></iconify-icon>{{ $offer->code }}
                                                </span>
                                                @endforeach
                                            </div>
                                            @endif
                                            <br>

                                            <small class="text-muted">
                                                Vendor: {{ $value->vendor_name }} <br>
                                                {{ $value->category_name }} > {{ $value->subcategory_name }} > {{ $value->child_category_name }}
                                            </small>
                                        </td>

                                        <td>
                                            <span class="badge bg-primary-subtle text-primary fs-12">
                                                {{ $value->variants->count() }} Variants
                                            </span>
                                        </td>

                                        <td>
                                            <select class="form-select form-select-sm change-product-status" data-id="{{$value->id}}">
                                                <option @if($value->status == 0 )selected @endif value="0">Pending</option>
                                                <option @if($value->status == 1 )selected @endif value="1">Approve</option>
                                                <option @if($value->status == 2 )selected @endif value="2">Reject</option>
                                            </select>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="{{ url('product-detail/'.$value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Product">
                                                    <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="{{url('edit-product').'/'. $value->id }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Update product">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="{{url('edit-variant').'/'. $value->id }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Update variant">
                                                    <iconify-icon icon="solar:tuning-square-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-product" data-id="{{$value->id}}" data-bs-toggle="tooltip" title="Delete Product">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr class="variant-row d-none" id="variant_{{ $value->id }}">
                                        <td colspan="8">


                                            @forelse ($value->variants as $variant)
                                            @php
                                            $images = json_decode($variant->image, true) ?? [];
                                            @endphp

                                            <div class="variant-card mb-3">
                                              
                                                <div class="variant-info">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Color:</strong> {{ $variant->color }}</p>
                                                            <p class="mb-1"><strong>Size:</strong>
                                                            @foreach($variant->sizes_list as $size)
                                                                {{ $size->name }}@if(! $loop->last), @endif
                                                            @endforeach
                                                            </p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Price:</strong> {{ $variant->price }}</p>
                                                            <p class="mb-1"><strong>Stock:</strong> {{ $variant->stock }}</p>
                                                          </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Discount:</strong> {{ $variant->discount_value }} {{ $variant->discount_type }}</p>
                                                            <p class="mb-0"><strong>Final Price:</strong> {{ $variant->final_price }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if(count($images))
                                                <div class="variant-images">
                                                    @foreach ($images as $img)
                                                    <img src="{{ asset('uploads/products/'.$img) }}" class="variant-thumb">
                                                    @endforeach
                                                </div>
                                                @endif

                                            </div>

                                            @empty
                                            <span class="text-muted">No variants found</span>
                                            @endforelse


                                        </td>
                                    </tr>

                                    @endforeach
                                </tbody>


                            </table>
                        </div>
                    </div>


                </div>
            </div>
            <!-- [ basic-table ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
</section>
<!-- [ Main Content ] end -->

@endsection
