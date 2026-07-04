@extends('backend.layouts.app')
@section('content')

<div class="page-content">
  <!-- Start Container Fluid -->
  <div class="container">
    <div class="row">
      <div class="col-xl-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-1 anchor mb-3" id="basic">
              Update Subcategory 
            </h5>

            <form action="{{ route('update.subcategory') }}" method="POST" enctype="multipart/form-data">
              @csrf

              <input type="hidden" name="subcategory_id" value="{{$subcategory->id}}">
              <div class="row">
                <!-- Name -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Subcategory Name</label>
                  <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $subcategory->name) }}"
                    required>
                </div>

                <!-- Slug -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Slug</label>
                  <input
                    type="text"
                    name="slug"
                    class="form-control"
                    value="{{ $subcategory->slug }}"
                    readonly>
                </div>
              </div>
             <div class="row">
                                 <div class="col-md-12 mb-4">
                                    <div class="mb-4">
                                        <label class="form-label">Upload Image</label>

                                        <input type="file"  name="image" id=""  class="form-control @error('image') is-invalid @enderror imageInput"  accept="image/*">

                                        @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <!-- Small Preview -->
                                        <img  src="" alt="Preview"  class="img-thumbnail mt-2 d-none imagePreview" width="80">
                                        <img  src="{{ asset($subcategory->image) }}" alt="Preview" width="80" class="img-thumbnail mt-2 imagePreview">

                                    </div>
                                    <div id="image-error-container">
                                        @error('image')
                                        <span class="d-block mt-2 text-danger invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                        
                                <div class="col-md-12 mb-3">
                                    <h6 class="mb-3">General Information</h6>
                                </div>
                              
                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Type Description">{{ $subcategory->description }}</textarea>
                                </div>
                            </div>

              <div class="row">
                <!-- Category -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Category</label>
                  <select name="category_id" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                      {{ $subcategory->category_id == $category->id ? 'selected' : '' }}>
                      {{ $category->name }}
                    </option>
                    @endforeach
                  </select>
                </div>

                <!-- Status -->
                <div class="mb-3 col-md-6">
                  <label class="form-label">Status</label>
                  <select name="is_active" class="form-select">
                    <option value="1" {{ $subcategory->is_active == 1 ? 'selected' : '' }}>
                      Active
                    </option>
                    <option value="0" {{ $subcategory->is_active == 0 ? 'selected' : '' }}>
                      Inactive
                    </option>
                  </select>
                </div>
              </div>

           
                              <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control" maxlength="255" placeholder="SEO title (optional)" value="{{ old('meta_title', $subcategory->meta_title ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500" placeholder="SEO description (optional)">{{ old('meta_description', $subcategory->meta_description ?? '') }}</textarea>
                                </div>
                              </div>
                              <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('subcategory.list') }}" class="btn border-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update SubCategory</button>
                            </div>

            </form>
          </div>
        </div>


      </div> <!-- end col -->


    </div> <!-- end row -->
  </div>
  <!-- End Container Fluid -->


</div>>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('imageInput');
        const fileBox = document.querySelector('.file-upload-box');

        if(imageInput && fileBox){
            const defaultView = fileBox.querySelector('.default-view');
            const previewView = fileBox.querySelector('.preview-view');
            const previewImg = previewView.querySelector('img');
            const removeBtn = previewView.querySelector('.remove-image');

            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        defaultView.classList.add('d-none');
                        previewView.classList.remove('d-none');
                    }
                    reader.readAsDataURL(file);
                }
            });

            removeBtn.addEventListener('click', function() {
                imageInput.value = '';
                previewImg.src = '';
                defaultView.classList.remove('d-none');
                previewView.classList.add('d-none');
            });
        }
    });
</script>
@endpush
