@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Add SubCategory</h4>
                    <div class="page-title-right">

                        <div class="col-auto">
                        <a href="{{ route('subcategory-list') }}" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom bg-transparent">
                        <h5 class="card-title mb-0">Add SubCategory</h5>
                    </div>
                    <div class="card-body">
                        <form id="SubcategoryForm" action="{{ route('store.subcategory') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Add Thumbnail Photo -->
                            <div class="row">
                            

                                <!-- Category Select -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- SubCategory Name -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">SubCategory Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter SubCategory Name" id="nameInput" value="{{ old('name') }}">
                                    @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>


                                <!-- Slug -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="Auto Generated Slug" readonly id="slugInput" value="{{ old('slug') }}">
                                    @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Type Description">{{ old('description') }}</textarea>
                                </div>
                            </div>

                          
                              <div class="col-md-12 mb-4">
                                    <div class="mb-4">
                                        <label class="form-label">Upload Image</label>

                                        <input type="file"  name="image" id=""  class="form-control @error('image') is-invalid @enderror imageInput"  accept="image/*">

                                        @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <!-- Small Preview -->
                                        <img  src="" alt="Preview"  class="img-thumbnail mt-2 d-none imagePreview" width="80">
                                    </div>
                                    <div id="image-error-container">
                                        @error('image')
                                        <span class="d-block mt-2 text-danger invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                  <!-- Status -->
                            <div class="mb-4 mt-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input form-switch-gold" type="checkbox" name="is_active" id="isActive" value="1" checked>
                                    <label class="form-check-label" for="isActive">Active Status</label>
                                </div>
                            </div>


                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('subcategory.list') }}" class="btn border-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save SubCategory</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const nameInput = document.getElementById('nameInput');
        const slugInput = document.getElementById('slugInput');
        const imageInput = document.getElementById('imageInput');
        const fileBox = document.querySelector('.file-upload-box');

        // Slug generation
        if (nameInput) {
            nameInput.addEventListener('keyup', function() {
                let text = nameInput.value;

                // Update Slug
                if (slugInput) {
                    let slug = text.toLowerCase()
                        .replace(/[^\w ]+/g, '')
                        .replace(/ +/g, '-');
                    slugInput.value = slug;
                }
            });
        }

        // Image preview
        if (imageInput && fileBox) {
            const defaultView = fileBox.querySelector('.default-view');
            const previewView = fileBox.querySelector('.preview-view');
            const previewImg = previewView.querySelector('img');
            const removeBtn = previewView.querySelector('.remove-image'); // This selects the button inside preview-view

            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Update form preview
                        previewImg.src = e.target.result;
                        defaultView.classList.add('d-none');
                        previewView.classList.remove('d-none');
                    }
                    reader.readAsDataURL(file);

                    // Manually clear error and validation state
                    $(imageInput).removeClass('is-invalid');
                    $(fileBox).removeClass('border border-danger');
                    $('#image-error-container').empty();
                    $('#image-error-container').find('.invalid-feedback').remove();
                }
            });

            // Ensure removeBtn exists before adding event listener
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    imageInput.value = '';
                    previewImg.src = '';
                    defaultView.classList.remove('d-none');
                    previewView.classList.add('d-none');
                });
            }
        }
    });

    $(document).ready(function() {
        // Force validation on file change
        $('#imageInput').on('change', function() {
            if (this.files && this.files.length > 0) {
                $(this).removeClass('is-invalid');
                $(this).closest('.file-upload-box').removeClass('border border-danger');
                $('#image-error-container').empty();
            }
            $(this).valid();
        });

        $('#SubcategoryForm').validate({
            ignore: "", // Important: Validate everything including hidden inputs
            rules: {
                name: {
                    required: true,
                    minlength: 2
                },
                category_id: {
                    required: true
                },
                image: {
                    required: true,
                    extension: "jpg|jpeg|png|gif"
                }
            },
            messages: {
                name: {
                    required: "Please enter subcategory name",
                    minlength: "Name must be at least 2 characters"
                },
                category_id: {
                    required: "Please select a category"
                },
                image: {
                    required: "Please upload a subcategory image",
                    extension: "Please upload valid image file (jpg, jpeg, png, gif)"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                if (element.attr("name") == "image") {
                    error.addClass('d-block mt-2 text-danger'); // Force display block and red color
                    $('#image-error-container').html(error); // Place in the dedicated container
                } else {
                    element.closest('.mb-3').append(error);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr("name") == "image") {
                    $(element).closest('.file-upload-box').addClass('border border-danger');
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr("name") == "image") {
                    $(element).closest('.file-upload-box').removeClass('border border-danger');
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