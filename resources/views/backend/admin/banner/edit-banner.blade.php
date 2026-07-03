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
              Edit Banner
            </h5>

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form action="{{ route('update.banner') }}" method="POST" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="id" value="{{ $banner->id }}">

              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Title</label>
                  <input type="text" name="title" value="{{ old('title', $banner->title) }}" class="form-control @error('title') is-invalid @enderror">
                  @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 col-md-6">
                  <label class="form-label">Position</label>
                  <select name="position" class="form-select @error('position') is-invalid @enderror">
                    @php $pos = old('position', $banner->position); @endphp
                    <option value="top" {{ $pos == 'top' ? 'selected' : '' }}>Top</option>
                    <option value="deal" {{ $pos == 'deal' ? 'selected' : '' }}>Deal</option>
                    <option value="middle" {{ $pos == 'middle' ? 'selected' : '' }}>Middle</option>
                    <option value="bottom" {{ $pos == 'bottom' ? 'selected' : '' }}>Bottom</option>
                    <option value="popup" {{ $pos == 'popup' ? 'selected' : '' }}>Popup</option>
                    <option value="promo" {{ $pos == 'promo' ? 'selected' : '' }}>Promo</option>
                    <option value="wishlist" {{ $pos == 'wishlist' ? 'selected' : '' }}>wishlist</option>
                    <option value="cart" {{ $pos == 'cart' ? 'selected' : '' }}>cart</option>
                    <!-- <option value="contact" {{ $pos == 'contact' ? 'selected' : '' }}>contact</option>\ -->
                    <option value="product_detail" {{ $pos == 'product_detail' ? 'selected' : '' }}>Product Detail</option>
                  </select>
                  @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row">
                <!-- <div class="mb-3 col-md-6">
                  <label class="form-label">Order</label>
                  <input type="number" name="order_by" value="{{ old('order_by', $banner->order_by) }}" class="form-control @error('order_by') is-invalid @enderror">
                  @error('order_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div> -->

                <div class="mb-3 col-md-6">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @php $st = old('status', $banner->status); @endphp
                    <option value="1" {{ $st == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $st == 0 ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Start Date</label>
                  <input type="date" name="start_date" value="{{ old('start_date', $banner->start_date ? $banner->start_date->format('Y-m-d') : '') }}" class="form-control @error('start_date') is-invalid @enderror">
                  @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 col-md-6">
                  <label class="form-label">End Date</label>
                  <input type="date" name="end_date" value="{{ old('end_date', $banner->end_date ? $banner->end_date->format('Y-m-d') : '') }}" class="form-control @error('end_date') is-invalid @enderror">
                  @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row">
                <div class="mb-3 col-md-6">
                  <label class="form-label">Link Type</label>
                  @php $lt = old('link_type', $banner->link_type); @endphp
                  <select name="link_type" class="form-select @error('link_type') is-invalid @enderror">
                    <option value="">None</option>
                    <option value="product" {{ $lt == 'product' ? 'selected' : '' }}>Product</option>
                    <option value="category" {{ $lt == 'category' ? 'selected' : '' }}>Category</option>
                    <option value="brand" {{ $lt == 'brand' ? 'selected' : '' }}>Brand</option>
                    <option value="external" {{ $lt == 'external' ? 'selected' : '' }}>External URL</option>
                  </select>
                  @error('link_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 col-md-3">
                  <label class="form-label">Link ID</label>
                  <input type="number" name="link_id" value="{{ old('link_id', $banner->link_id) }}" class="form-control @error('link_id') is-invalid @enderror">
                  @error('link_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 col-md-3">
                  <label class="form-label">External URL</label>
                  <input type="url" name="link_url" value="{{ old('link_url', $banner->link_url) }}" class="form-control @error('link_url') is-invalid @enderror">
                  @error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              @php $isMulti = $banner->image && is_array(json_decode($banner->image, true)) && json_last_error() === JSON_ERROR_NONE; @endphp

              <div class="mb-3">
                <label class="form-label">Image Type</label>
                <div class="d-flex gap-4">
                  <div class="form-check">
                    <input class="form-check-input image-type-radio" type="radio" name="image_type" id="imgTypeSingle" value="single" {{ $isMulti ? '' : 'checked' }}>
                    <label class="form-check-label" for="imgTypeSingle">Single Image</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input image-type-radio" type="radio" name="image_type" id="imgTypeMultiple" value="multiple" {{ $isMulti ? 'checked' : '' }}>
                    <label class="form-check-label" for="imgTypeMultiple">Multiple Images</label>
                  </div>
                </div>
              </div>

              <div class="mb-3 {{ $isMulti ? 'd-none' : '' }}" id="singleImageInput">
                <label class="form-label">Banner Image</label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="mb-3 {{ $isMulti ? '' : 'd-none' }}" id="multipleImageInput">
                <label class="form-label">Add More Images</label>
                <input type="file" name="images[]" class="form-control @error('images.*') is-invalid @enderror" multiple accept="image/*">
                @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">New images will be added to existing ones</small>
              </div>
                
                @if(!empty($banner->image_data) && is_array($banner->image_data))
                <div class="d-flex flex-wrap gap-2 mt-2 mb-3">
                  @foreach($banner->image_data as $img)
                  <div class="position-relative banner-image-container" data-name="{{ $img['name'] }}">
                    <img
                      src="{{ $img['url'] }}"
                      alt="Banner Image"
                      width="120" class="img-thumbnail">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" 
                       data-id="{{ $banner->id }}" data-name="{{ $img['name'] }}" data-url="{{ route('delete.banner.image') }}">
                       <iconify-icon icon="solar:trash-bin-minimalistic-2-linear"></iconify-icon>
                     </button>
                  </div>
                  @endforeach
                </div>
                @endif

              @push('scripts')
              <script>
              $(document).ready(function() {
                $('.image-type-radio').on('change', function() {
                  if ($(this).val() === 'multiple') {
                    $('#singleImageInput').addClass('d-none');
                    $('#multipleImageInput').removeClass('d-none');
                  } else {
                    $('#singleImageInput').removeClass('d-none');
                    $('#multipleImageInput').addClass('d-none');
                  }
                });
              });
              </script>
              @endpush

              <button type="submit" class="btn btn-primary">Update Banner</button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<!-- [ Main Content ] end -->

@endsection
