@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">{{ isset($policy) ? 'Edit' : 'Add' }} Vendor Policy</h4>
            </div>
            <div class="col-md-6 text-end"></div>
        </div>
      
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('vendor.policy.store') }}" method="POST">
                            @csrf
                            @if(isset($policy))
                                <input type="hidden" name="id" value="{{ $policy->id }}">
                            @endif

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter title" value="{{ $policy->title ?? old('title') }}" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Content</label>
                                    <textarea name="content" id="editor" class="form-control">{{ $policy->content ?? old('content') }}</textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="1" {{ (isset($policy) && $policy->status == 1) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ (isset($policy) && $policy->status == 0) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <a href="{{ route('vendor.policy.list') }}" class="btn btn-light px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ isset($policy) ? 'Update' : 'Save' }} Policy
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

