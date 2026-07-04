@extends('backend.layouts.app')
@section('content')

<div class="page-content">
  <!-- Start Container Fluid -->
  <div class="container-fluid">
  <div class="row align-items-center mb-3">
            <div class="col">
                <h4 class="mb-0">Update Brand</h4>
            </div>
            <div class="col-auto">
                <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                    Back to List
                </a>
            </div>
        </div>
    <div class="row">
      <div class="col-xl-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-1 anchor mb-4" id="basic">
              Update Brand
            </h5>
            

            <form action="{{ route('update.brand') }}"
              method="POST"
              enctype="multipart/form-data"
              id="BrandForm">
              @csrf
              @method('PUT')

              <input type="hidden" name="id" value="{{$brand->id}}">

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Category</label>
                  <select name="category_id" class="form-select category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                    <option @if($brand->category_id == $cat->id ) selected @endif value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label">Sub Category</label>
                  <select name="subcategory_id" class="form-select subcategory_id" data-selected="{{ $brand->subcategory_id }}">
                    <option value="">Select Sub Category</option>
              
                  </select>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label">Child Category</label>
                  <select name="child_category_id" class="form-select child_category_id" data-selected="{{ $brand->childcategory_id }}">
                    <option value="">Select Child Category </option>
                   
                  </select>
                </div>
              </div>



              <div class="row">
                <!-- Brand Name English -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Brand Name</label>
                  <input type="text"
                    name="name"
                    value="{{ old('name', $brand->name) }}"
                    class="form-control @error('name') is-invalid @enderror">

                  @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>


            
                <!-- Slug -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Slug</label>
                  <input type="text"
                    name="slug"
                    value="{{ old('slug', $brand->slug) }}"
                    readonly
                    class="form-control">
                </div>
              </div>

              <!-- Brand Logo -->
              <div class="col-md-12 mb-4">
                                    <div class="mb-4">
                                        <label class="form-label">Upload Image</label>

                                        <input type="file"  name="logo" id=""  class="form-control @error('logo') is-invalid @enderror imageInput"  accept="image/*" >

                                        @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <!-- Small Preview -->
                                        <img  src="" alt="Preview"  class="img-thumbnail mt-2 d-none imagePreview" width="80">
                                        <img  src="{{ asset($brand->logo) }}" alt="Preview" width="80" class="mg-thumbnail mt-2 imagePreview">

                                    </div>
                                    <div id="image-error-container">
                                        @error('logo')
                                        <span class="d-block mt-2 text-danger invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

              <div class="row">
                <!-- Description English -->
                <div class="mb-3 col-md-12">
                  <label class="form-label">Description</label>
                  <textarea name="description"
                    rows="3"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $brand->description) }}</textarea>

                  @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" maxlength="255" placeholder="SEO title (optional)" value="{{ old('meta_title', $brand->meta_title ?? '') }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500" placeholder="SEO description (optional)">{{ old('meta_description', $brand->meta_description ?? '') }}</textarea>
                  </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status"
                    class="form-select @error('status') is-invalid @enderror">
                    <option value="1" {{ old('status', $brand->status) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $brand->status) == 0 ? 'selected' : '' }}>Inactive</option>
                  </select>

                  @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                  Update Brand
                </button>

                <a href="{{ route('brand.list') }}" class="btn btn-secondary ms-2">
                  Cancel
                </a>
              </div>
            </form>
          </div>
        </div>


      </div> <!-- end col -->


    </div> <!-- end row -->
  </div>
  <!-- End Container Fluid -->


</div>

@endsection
