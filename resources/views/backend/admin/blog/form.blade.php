@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ isset($blog) ? 'Edit Blog' : 'Add Blog' }}</h4>
                        <a href="{{ route('admin.blog.index') }}" class="btn btn-sm btn-soft-secondary">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="align-middle"></iconify-icon> Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ isset($blog) ? route('admin.blog.update', $blog->id) : route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="blog-title" class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $blog->title ?? '') }}" placeholder="Enter blog title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                                    <input type="text" name="slug" id="blog-slug" class="form-control @error('slug') is-invalid @enderror" 
                                           value="{{ old('slug', $blog->slug ?? '') }}" placeholder="blog-slug" required readonly>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Enter short description">{{ old('description', $blog->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Content <span class="text-danger">*</span></label>
                                    <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" 
                                              placeholder="Enter blog content">{{ old('content', $blog->content ?? '') }}</textarea>
                                    @error('content')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                                    @if(isset($blog) && $blog->image)
                                        <div class="mt-2">
                                            <img src="{{ asset('uploads/blog/' . $blog->image) }}" alt="Blog Image" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="status" value="1" 
                                               {{ old('status', $blog->status ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control" maxlength="255" placeholder="SEO title (optional)" value="{{ old('meta_title', $blog->meta_title ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500" placeholder="SEO description (optional)">{{ old('meta_description', $blog->meta_description ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <iconify-icon icon="solar:check-read-linear" class="align-middle me-1"></iconify-icon>
                                    {{ isset($blog) ? 'Update Blog' : 'Save Blog' }}
                                </button>
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
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Slug generator
        const titleInput = document.getElementById('blog-title');
        const slugInput = document.getElementById('blog-slug');

        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function() {
                const title = this.value;
                const slug = title.toLowerCase()
                    .replace(/[^\w ]+/g, '')
                    .replace(/ +/g, '-');
                slugInput.value = slug;
            });
        }

        // CKEditor
        if (document.querySelector('#editor')) {
            // Check if instance already exists
            const existingInstance = document.querySelector('.ck-editor');
            if (!existingInstance) {
                ClassicEditor
                    .create(document.querySelector('#editor'))
                    .then(editor => {
                        console.log('CKEditor initialized');
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        }
    });
</script>

<style>
    .ck-editor__editable_inline {
        min-height: 300px;
    }
    /* CKEditor Dark Theme Overrides */
    [data-bs-theme="dark"] .ck-editor__editable {
        background-color: #1e2125 !important;
        color: #ffffff !important;
        border-color: #373b3e !important;
    }
    [data-bs-theme="dark"] .ck-toolbar {
        background-color: #2b3035 !important;
        border-color: #373b3e !important;
    }
    [data-bs-theme="dark"] .ck.ck-button {
        color: #ffffff !important;
    }
</style>
@endpush
