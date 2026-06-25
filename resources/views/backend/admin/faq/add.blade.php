@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">{{ isset($faq) ? 'Edit' : 'Add' }} FAQ</h4>
            </div>
        </div>
      
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('faq.store') }}" method="POST">
                            @csrf
                            @if(isset($faq))
                                <input type="hidden" name="id" value="{{ $faq->id }}">
                            @endif

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Question</label>
                                    <input type="text" name="question" class="form-control" placeholder="Enter question" value="{{ $faq->question ?? old('question') }}" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Answer</label>
                                    <textarea name="answer" id="editor" class="form-control">{{ $faq->answer ?? old('answer') }}</textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="1" {{ (isset($faq) && $faq->status == 1) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ (isset($faq) && $faq->status == 0) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <a href="{{ route('faq.list') }}" class="btn btn-light px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ isset($faq) ? 'Update' : 'Save' }} FAQ
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

