 @extends('backend.layouts.app')
 @section('content')
 <div class="page-content">
    <div class="container-fluid">
         <div class="row">
             <div class="col-xl-12">
                 <div class="card">
                     <div class="card-body">
                         <h5 class="card-title mb-1 anchor" id="basic">
                             Basic Example
                         </h5>

                         <form action="{{ route('store.product.size.category') }}" method="POST">
                             @csrf

                             <div class="row">
                                 <div class="mb-3 col-md-6">
                                     <label class="form-label">Product Size Category</label>
                                     <input type="text"
                                         name="name"
                                         class="form-control"
                                         value="{{ old('name') }}"
                                         required>
                                 </div>

                                 <div class="mb-3 col-md-6">
                                     <label class="form-label">Status</label>
                                     <select class="form-control" name="status" required>
                                         <option value="">Select Status</option>
                                         <option value="1">Active</option>
                                         <option value="0">Inactive</option>
                                     </select>
                                 </div>
                             </div>

                             <button type="submit" class="btn btn-primary">
                                 <iconify-icon icon="solar:diskette-linear" class="me-1"></iconify-icon>
                                 Create Product Size Category
                             </button>
                         </form>
                     </div>
                 </div>


             </div> <!-- end col -->


         </div> <!-- end row -->

         <div class="row">
             <div class="col-xl-12">
               
                 <div class="card">
                     <div class="card-body">
                         <h5 class="card-title mb-1 anchor" id="nesting1">
                             Product Size or Category List <a class="anchor-link" href="#nesting1">#</a>
                             
                         </h5>

                         <div class="table-responsive">
                             <table class="table table-bordered table-striped table-centered">
                                 <thead>
                                     <tr>
                                         <th>
                                             <input type="checkbox">
                                         </th>
                                         <th>Size Category</th>
                                         <th>Sizes</th>
                                         <th>Action</th>
                                     </tr>
                                 </thead>

                                 <tbody>
                                     @foreach ($sizeType as $category => $sizes)
                                    
                                     <tr id="row_{{ $sizes[0]->id }}">
                                         <td>
                                             <input type="checkbox">
                                         </td>

                                         <td>
                                             <strong>{{ $category }}</strong>
                                         </td>

                                         <td>
                                              
                                             @if(!empty($sizes->toArray()))
                                           
                                             @foreach ($sizes as $size)
                                           
                                             <span class="badge badge-outline-success  me-1">
                                                 {{ $size->size_name }}
                                             </span>
                                             @endforeach
                                             @else
                                             -
                                             @endif
                                         </td>

                                         <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="{{ url('add-product-size/'.$sizes[0]->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Add Product Size">
                                                    <iconify-icon icon="solar:plus-circle-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="{{ url('edit-product-size-category/'.$sizes[0]->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="Edit Category">
                                                    <iconify-icon icon="solar:pen-linear" class="fs-20"></iconify-icon>
                                                </a>
                                                <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-product-size-category" data-id="{{ $sizes[0]->id }}" data-bs-toggle="tooltip" title="Delete Category">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                     </tr>
                                     @endforeach
                                 </tbody>
                             </table>
                         </div>


                     </div>
                 </div> <!-- end card body -->
             </div> <!-- end col -->


         </div>
     </div>
     <!-- End Container Fluid -->


 </div>
 @endsection
