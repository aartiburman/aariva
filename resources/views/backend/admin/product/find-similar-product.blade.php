@extends('backend.layouts.app')
@section('content')

<div class="page-content">

     <!-- Start Container Fluid -->
     <div class="container-fluid">
          <div class="row">
               <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                         <h4 class="mb-sm-0">Add Similar Product</h4>
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
                    <div class="card">
                         <div class="card-header">
                              <h4 class="card-title">Find & Clone Product</h4>
                         </div>
                         <div class="card-body">
                              <form id="searchSimilarForm">
                                   <div class="d-flex flex-nowrap align-items-end gap-2 w-100">
                                        <div style="flex:0 0 28%;max-width:28%;">
                                             <label class="form-label">Search Product Name</label>
                                             <input type="text" name="q" id="search_q" class="form-control" placeholder="Type product name...">
                                        </div>
                                        <div style="flex:0 0 14%;max-width:14%;">
                                             <label class="form-label">Category</label>
                                             <select name="category_id" id="search_category_id" class="form-select category_id">
                                                  <option value="">All Categories</option>
                                                  @php $lang = app()->getLocale(); @endphp
                                                  @foreach($categories_data as $cat)
                                                  <option value="{{ $cat->id }}">{{ $cat->{"name_$lang"} ?? $cat->name }}</option>
                                                  @endforeach
                                             </select>
                                        </div>
                                        <div style="flex:0 0 14%;max-width:14%;">
                                             <label class="form-label">Sub Category</label>
                                             <select name="subcategory_id" id="search_subcategory_id" class="form-select subcategory_id">
                                                  <option value="">All Sub Categories</option>
                                             </select>
                                        </div>
                                        <div style="flex:0 0 14%;max-width:14%;">
                                             <label class="form-label">Child Category</label>
                                             <select name="child_category_id" id="search_child_category_id" class="form-select child_category_id">
                                                  <option value="">All Child Categories</option>
                                             </select>
                                        </div>
                                        <div style="flex:0 0 14%;max-width:13%;">
                                             <label class="form-label">Brand</label>
                                             <select name="brand_id" id="search_brand_id" class="form-select categoryBrand brand_id">
                                                  <option value="">All Brands</option>
                                             </select>
                                        </div>
                                        <div style="flex:0 0 16%;max-width:12%;">
                                             <button type="button" id="btnSearchProduct" class="btn btn-primary w-100">
                                                  <iconify-icon icon="solar:magnifer-linear" class="me-1"></iconify-icon> Search
                                             </button>
                                        </div>
                                   </div>
                              </form>

                              <div id="search-results-wrapper" class="mt-4" style="display:none;">
                                   <hr>
                                   <h6>Search Results</h6>
                                   <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                             <thead class="table-light">
                                                  <tr>
                                                       <th>Name</th>
                                                       <th>Category</th>
                                                       <th>Sub Category</th>
                                                       <th>Child Category</th>
                                                       <th>Brand</th>
                                                       <th>Action</th>
                                                  </tr>
                                             </thead>
                                             <tbody id="search-results-body">
                                                  <!-- results here -->
                                             </tbody>
                                        </table>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <div class="card" id="product-detail-card" style="display:none;">
                         <div class="card-header">
                              <h4 class="card-title">Configure New Product</h4>
                         </div>
                         <div class="card-body">
                              <div id="similar-product-result">
                                   <!-- Rendered form here -->
                              </div>
                         </div>
                    </div>
               </div> <!-- end col -->


          </div> <!-- end row -->
     </div>
     <!-- End Container Fluid -->

</div>

@push('scripts')
<script>
     $(document).ready(function() {
          $('#btnSearchProduct').on('click', function() {
               let q = $('#search_q').val();
               let category_id = $('#search_category_id').val();
               let subcategory_id = $('#search_subcategory_id').val();
               let child_category_id = $('#search_child_category_id').val();
               let brand_id = $('#search_brand_id').val();

               $.ajax({
                    url: "{{ route('ajax.find.similar') }}",
                    type: "POST",
                    data: {
                         _token: "{{ csrf_token() }}",
                         q: q,
                         category_id: category_id,
                         subcategory_id: subcategory_id,
                         child_category_id: child_category_id,
                         brand_id: brand_id
                    },
                    beforeSend: function() {
                         $('#btnSearchProduct').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Searching...');
                    },
                    success: function(response) {
                         $('#btnSearchProduct').prop('disabled', false).html('<iconify-icon icon="solar:magnifer-linear" class="me-1"></iconify-icon> Search');

                         if (response.success) {
                              let rows = '';
                              if (response.data.length > 0) {
                                   $.each(response.data, function(index, item) {
                                        rows += `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td>${item.category_name || '-'}</td>
                                        <td>${item.subcategory_name || '-'}</td>
                                        <td>${item.child_category_name || '-'}</td>
                                        <td>${item.brand_name || '-'}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success select-product-btn" data-id="${item.id}">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                   });
                              } else {
                                   rows = '<tr><td colspan="6" class="text-center">No products found.</td></tr>';
                              }
                              $('#search-results-body').html(rows);
                              $('#search-results-wrapper').show();
                         }
                    },
                    error: function(xhr) {
                         $('#btnSearchProduct').prop('disabled', false).html('<iconify-icon icon="solar:magnifer-linear" class="me-1"></iconify-icon> Search');
                         let msg = 'Error occurred while searching.';
                         try {
                              const res = xhr.responseJSON;
                              if (res && res.message) msg = res.message;
                         } catch(e){}
                         toastr.error(msg);
                    }
               });
          });

          $(document).on('click', '.select-product-btn', function() {
               let productId = $(this).data('id');

               $.ajax({
                    url: "{{ route('ajax.render.product') }}",
                    type: "POST",
                    data: {
                         _token: "{{ csrf_token() }}",
                         id: productId
                    },
                    success: function(response) {
                         if (response.success) {
                              $('#similar-product-result').html(response.html);
                              $('#product-detail-card').show();

                              $('html, body').animate({
                                   scrollTop: $("#product-detail-card").offset().top - 100
                              }, 500);

                              // Re-initialize plugins if needed (e.g. Choices.js)
                              if (typeof initChoices === 'function') {
                                   $('.js-size-select').each(function() {
                                        initChoices(this);
                                   });
                              }
                         } else {
                              toastr.error(response.message || 'Error loading product details.');
                         }
                    },
                    error: function(xhr) {
                         let msg = 'Error loading product details.';
                         try {
                              const res = xhr.responseJSON;
                              if (res && res.message) msg = res.message;
                         } catch(e){}
                         toastr.error(msg);
                    }
               });
          });

          // AJAX FORM SUBMISSION FOR SIMILAR PRODUCT
          $(document).on('submit', '#productForm', function(e) {
               e.preventDefault();
               const form = $(this);
               const url = form.attr('action');
               const formData = new FormData(this);
               const submitBtn = form.find('button[type="submit"]');
               const originalText = submitBtn.text();

               // Clear previous errors
               form.find('.is-invalid').removeClass('is-invalid');
               form.find('.invalid-feedback').remove();

               submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

               $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                         if (response.status) {
                              toastr.success(response.message || 'Product created successfully');
                              window.location.href = "{{ route('product.list') }}";
                         } else {
                              toastr.error(response.message || 'Failed to create product');
                              submitBtn.prop('disabled', false).text(originalText);
                         }
                    },
                    error: function(xhr) {
                         submitBtn.prop('disabled', false).text(originalText);
                         
                         if (xhr.status === 422) {
                              const errors = xhr.responseJSON.errors;
                              Object.keys(errors).forEach(key => {
                                   let fieldName = key;
                                   if (key.includes('.')) {
                                        let parts = key.split('.');
                                        fieldName = parts[0] + '[]';
                                   }

                                   let input;
                                   if (key.includes('.')) {
                                        let index = key.split('.')[1];
                                        input = form.find(`[name="${fieldName}"]`).eq(index);

                                        if (input.length === 0) {
                                             input = form.find(`[name^="${key.split('.')[0]}[${index}]"]`);
                                        }
                                   } else {
                                        input = form.find(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                                   }

                                   input.addClass('is-invalid');
                                   let errorMsg = `<div class="invalid-feedback d-block text-danger mt-1">${errors[key][0]}</div>`;

                                   if (input.closest('.choices').length) {
                                        input.closest('.choices').after(errorMsg);
                                        input.closest('.choices').find('.choices__inner').addClass('border-danger');
                                   } else if (input.parent().hasClass('input-group')) {
                                        input.parent().after(errorMsg);
                                   } else if (input.is(':checkbox') || input.is(':radio')) {
                                        input.closest('.form-check').append(errorMsg);
                                   } else {
                                        input.after(errorMsg);
                                   }
                              });

                              toastr.error('Please fix the errors highlighted in red.');
                              
                              $('html, body').animate({
                                   scrollTop: $('.is-invalid').first().offset().top - 150
                              }, 500);
                         } else {
                              toastr.error('An error occurred. Please try again.');
                         }
                    }
               });
          });
     });
</script>
@endpush
@endsection
