@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Add Brand</h4>
                   
                    <div class="page-title-right">
                        <div class="col-auto">
                        <a href="{{ route('brand.list') }}" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                            Back to List
                        </a>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Add Brand</h4>
                        <form id="BrandForm" action="{{ route('store.brand') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category Name</label>
                                    <select name="category_id" id="" class="form-select @error('category_id') is-invalid @enderror category_id">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sub Category</label>
                                    <select name="subcategory_id" id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror subcategory_id">
                                        <option value="">Select Sub Category</option>
                                    </select>
                                    @error('subcategory_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Child Category</label>
                                    <select name="child_category_id" id="child_category_id" class="form-select @error('child_category_id') is-invalid @enderror child_category_id">
                                        <option value="">Select Child Category</option>
                                    </select>
                                    @error('child_category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Brand Name" maxlength="255" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control" placeholder="Auto Generated Slug" readonly maxlength="255" value="{{ old('slug') }}">
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label mb-2">Brand Logo <span class="text-danger">*</span></label>
                                    <div class="file-upload-box text-center p-4 rounded-3 position-relative file-upload-box-theme d-inline-block @error('logo') border border-danger @enderror">
                                        <div class="default-view">
                                            <iconify-icon icon="solar:cloud-upload-linear" class="fs-48 mb-2 text-gold"></iconify-icon>
                                            <p class="mb-0 text-muted">Drop your images here, or <span class="text-gold cursor-pointer">click to browse</span></p>
                                            <small class="text-muted d-block mt-1">1600 x 1200 (4:3) recommended. PNG, JPG and GIF files are allowed</small>
                                        </div>
                                        <div class="preview-view d-none">
                                            <img src="" alt="Preview" class="img-fluid rounded shadow-sm preview-view-img">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image">
                                                <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                                            </button>
                                        </div>
                                        <input type="file" name="logo" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" id="imageInput" accept="image/*" required>
                                    </div>
                                    <div id="image-error-container">
                                        @error('logo')
                                            <span class="d-block mt-2 text-danger invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control" maxlength="255" placeholder="SEO title (optional)" value="{{ old('meta_title') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500" placeholder="SEO description (optional)">{{ old('meta_description') }}</textarea>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                          
                              <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('brand.list') }}" class="btn border-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Save Brand</button>
                                </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Image preview logic
        const fileInput = document.getElementById('imageInput');
        const defaultView = document.querySelector('.default-view');
        const previewView = document.querySelector('.preview-view');
        const previewImg = document.querySelector('.preview-view-img');
        const removeBtn = document.querySelector('.remove-image');
        const uploadBox = document.querySelector('.file-upload-box');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    defaultView.classList.add('d-none');
                    previewView.classList.remove('d-none');
                    // uploadBox.classList.add('border-0', 'p-0'); // Do not remove border/padding to keep layout consistent
                    
                    // Manually clear error and validation state
                    $(fileInput).removeClass('is-invalid');
                    $(uploadBox).removeClass('border border-danger');
                    $('#image-error-container').empty();
                    $('#image-error-container').find('.invalid-feedback').remove();
                }
                reader.readAsDataURL(file);
            }
        });

        // Force validation on file change
        $(fileInput).on('change', function() {
            if (this.files && this.files.length > 0) {
                 $(this).removeClass('is-invalid');
                 $(this).closest('.file-upload-box').removeClass('border border-danger');
                 $('#image-error-container').empty(); 
            }
            $(this).valid();
        });

        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            defaultView.classList.remove('d-none');
            previewView.classList.add('d-none');
            previewImg.src = '';
            uploadBox.classList.remove('border-0', 'p-0');
        });



        // Form validation
        $('#BrandForm').validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 255
                },
                logo: {
                    required: true
                },
                status: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Please enter brand name",
                    maxlength: "Brand name cannot exceed 255 characters"
                },
                logo: {
                    required: "Please select a brand logo"
                },
                status: {
                    required: "Please select status"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                if (element.attr("name") == "logo") {
                    error.appendTo("#image-error-container");
                    $(".file-upload-box").addClass("border border-danger");
                } else {
                    element.closest('.mb-3').append(error);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr("name") == "logo") {
                    $(".file-upload-box").addClass("border border-danger");
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr("name") == "logo") {
                    $(".file-upload-box").removeClass("border border-danger");
                }
            },
            submitHandler: function(form) {
                // Show loading spinner
                var $btn = $(form).find('button[type="submit"]');
                $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...').prop('disabled', true);
                form.submit();
            }
        });
    });
</script>
@endpush
