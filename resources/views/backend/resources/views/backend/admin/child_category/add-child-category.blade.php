@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Add Child Category</h4>
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

        <div class="row">
            <div class="col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom bg-transparent">
                        <h5 class="card-title mb-0">Add Child Category</h5>
                    </div>
                    <div class="card-body">
                        <form id="ChildCategoryForm" action="{{ route('store.child.category') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <h6 class="mb-3">General Information</h6>
                                </div>

                                <!-- Category Select -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror category_id">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- SubCategory Select -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SubCategory Name <span class="text-danger">*</span></label>
                                    <select name="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror subcategory_id">
                                        <option value="">Select SubCategory</option>
                                        <!-- Subcategories will be loaded via AJAX -->
                                    </select>
                                    @error('subcategory_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Child Category Name -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Child Category Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror child_category_id" placeholder="Enter Child Category Name" id="nameInput" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Slug -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="Auto Generated Slug" readonly id="slugInput" value="{{ old('slug') }}">
                                    @error('slug')
                                        <span class="invalid-feedback">{{ $message }}</span>
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
                                <a href="{{ route('child.category.list') }}" class="btn border-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Child Category</button>
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
        // Validation
        $('#ChildCategoryForm').validate({
            rules: {
                category_id: {
                    required: true
                },
                subcategory_id: {
                    required: true
                },
                name: {
                    required: true,
                    minlength: 2
                }
            },
            messages: {
                category_id: {
                    required: "Please select a category"
                },
                subcategory_id: {
                    required: "Please select a subcategory"
                },
                name: {
                    required: "Please enter child category name",
                    minlength: "Name must be at least 2 characters"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.mb-3').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                // Show loading spinner
                var $btn = $(form).find('button[type="submit"]');
                $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...').prop('disabled', true);
                $('#global-loader').fadeIn();
                form.submit();
            }
        });

        // Auto Slug
        $('input[name="name"]').on('keyup', function() {
            var name = $(this).val();
            var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            $('input[name="slug"]').val(slug);
        });

       
    });
</script>
@endpush
