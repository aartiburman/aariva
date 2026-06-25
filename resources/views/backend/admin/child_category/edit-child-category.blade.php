 @extends('backend.layouts.app')
 @section('content')
 <div class="page-content">
   <!-- Start Container Fluid -->
   <div class="container">
     <div class="row">
       <div class="col-xl-12">
         <div class="card">
           <div class="card-body">
             <h5 class="card-title mb-1 anchor mb-4" id="basic">
              Update Category 
             </h5>

             <form action="{{ route('update.child.category') }}" method="POST">
                            @csrf

                            <input type="hidden" name="child_category_id" value="{{ $childCategory->id }}">

                            <div class="row">
                                <!-- Category -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Category</label>
                                    <select name="category_id"  class="form-select category_id" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $childCategory->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sub Category -->

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Sub Category</label>
                                    <select name="subcategory_id"  class="form-select subcategory_id" data-selected="{{ $childCategory->subcategory_id }}" required>
                                        <option value="">-- Select Sub Category --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Name -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Child Category Name</label>
                                    <input type="text"
                                        name="name"
                                        class="form-control"
                                        value="{{ old('name', $childCategory->name) }}"
                                        required>
                                </div>

                                <!-- Slug -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Slug</label>
                                    <input type="text"
                                        name="slug"
                                        class="form-control"
                                        value="{{ $childCategory->slug }}"
                                        readonly>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" {{ $childCategory->is_active == 1 ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="0" {{ $childCategory->is_active == 0 ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">
                                {{ old('description', $childCategory->description) }}</textarea>
                            </div>

                            <!-- Meta Title -->
                            <div class="mb-3">
                                <label class="form-label">Meta Title</label>
                                <input type="text"
                                    name="meta_title"
                                    class="form-control"
                                    value="{{ old('meta_title', $childCategory->meta_title) }}">
                            </div>

                            <!-- Meta Description -->
                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2">
                                {{ old('meta_description', $childCategory->meta_description) }}
                                </textarea>
                            </div>

                             <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('child.category.list') }}" class="btn border-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Child Category</button>
                            </div>

                        </form>
           </div>
         </div>


       </div> <!-- end col -->


     </div> <!-- end row -->
   </div>
   <!-- End Container Fluid -->



 @endsection

