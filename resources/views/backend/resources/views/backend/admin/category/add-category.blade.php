@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create Category</h4>
                    <div class="page-title-right">
                        <div class="col-auto">
                        <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon>
                            Back to List
                        </a>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row ">
            <!-- Add Category Form -->
            <div class="col-xl-12 mb-4">
                <div class="card border-0 shadow-sm ">
                    <div class="card-header border-bottom bg-transparent">
                        <h5 class="card-title mb-0">General Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="CategoryForm" action="{{ route('store.category') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                             
                                <!-- Category Title -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category Title <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Title" required id="nameInput" maxlength="255" value="{{ old('name') }}">
                                    @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Slug (Auto-generated) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="Auto-generated slug" readonly id="slugInput" maxlength="255" value="{{ old('slug') }}">
                                    @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control " rows="4" placeholder="Type Description" maxlength="1000"></textarea>
                                </div>
                            </div>
                               <!-- Add Thumbnail Photo -->
                                <div class="col-md-12 mb-4">
                                    <div class="mb-4">
                                        <label class="form-label">Upload Image</label>

                                        <input type="file"  name="image" id=""  class="form-control @error('image') is-invalid @enderror imageInput"  accept="image/*" required>

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

                    </div>

                </div>

                <div class="col-xl-12">
                    <div class="card border-0 shadow-sm">

                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-md-12 mb-3">
                                    <h6 class="mb-3">Meta Options</h6>
                                </div>

                                <!-- Meta Title -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control " placeholder="Enter Meta Title" maxlength="255">
                                </div>

                                <!-- Meta Description -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control " rows="1" placeholder="Type Meta Description" maxlength="500"></textarea>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-4 mt-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input form-switch-gold" type="checkbox" name="is_active" id="isActive" value="1" checked>
                                    <label class="form-check-label" for="isActive">Active Status</label>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('category.list') }}" class="btn border-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>

                            </form>
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
                    const removeBtn = previewView.querySelector('.remove-image');

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

                    removeBtn.addEventListener('click', function() {
                        imageInput.value = '';
                        previewImg.src = '';
                        defaultView.classList.remove('d-none');
                        previewView.classList.add('d-none');
                    });
                }
            });

            // jQuery Validation
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

                $('#CategoryForm').validate({
                    ignore: "", // Important: Validate everything including hidden inputs
                    rules: {
                        name: {
                            required: true,
                            minlength: 2
                        },
                        image: {
                            required: true,
                            extension: "jpg|jpeg|png|webp|gif"
                        }
                    },
                    messages: {
                        name: {
                            required: "Please enter category title",
                            minlength: "Title must be at least 2 characters"
                        },
                        image: {
                            required: "Please upload a category image",
                            extension: "Please upload valid image file (jpg, jpeg, png, webp, gif)"
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
                        $('#global-loader').fadeIn();
                        form.submit();
                    }
                });
            });
        </script>
        <script>
            $(document).ready(function() {

               

            });
        </script>
        @endpush