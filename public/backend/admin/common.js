
$(document).on('blur', '.product-name', function () {

    let name = $(this).val().trim();

    if (!name) {
        $('.skugen').val('');
        return;
    }

    // Remove spaces & special characters
    name = name.replace(/[^a-zA-Z]/g, '');

    // Take first 3–4 letters
    let prefixLength = name.length >= 4 ? 4 : 3;
    let prefix = name.substring(0, prefixLength).toUpperCase();

    // Random 3 digit number
    let randomNumber = Math.floor(100 + Math.random() * 900);

    // Final SKU
    let sku = prefix + randomNumber;

    // Set SKU
    $('.skugen').val(sku);
});

// Centralized image removal logic
$(document).on('click', '.remove-image-btn', function () {
    var btn = $(this);
    var bannerId = btn.data('id');
    var imageName = btn.data('name');
    var url = btn.data('url');

    if (confirm('Are you sure you want to remove this image?')) {
        $.ajax({
            url: url,
            type: "POST",
            data: {
                banner_id: bannerId,
                image_name: imageName,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.status) {
                    btn.closest('.banner-image-container').fadeOut(300, function () {
                        $(this).remove();
                    });
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
             toastr.error('Something went wrong. Please try again.');
                }
            });
        }
    });

// Global Date Range Picker Initialization
$(document).ready(function() {
    function initDateRangePicker() {
        if (typeof $.fn.daterangepicker !== 'undefined') {
            $('.range-datepicker').each(function() {
                const $el = $(this);
                
                // Check if it's already initialized to avoid double initialization
                if ($el.data('daterangepicker')) return;

                let startDate = moment().subtract(3, 'months');
                let endDate = moment();

                const currentVal = $el.val();
                if (currentVal && (currentVal.includes(' to ') || currentVal.includes(' - '))) {
                    const separator = currentVal.includes(' to ') ? ' to ' : ' - ';
                    const dates = currentVal.split(separator);
                    if (dates.length === 2) {
                        startDate = moment(dates[0]);
                        endDate = moment(dates[1]);
                    }
                }

                $el.daterangepicker({
                    autoUpdateInput: false,
                    startDate: startDate,
                    endDate: endDate,
                    maxDate: moment(),
                    showDropdowns: true,
                    minYear: 1901,
                    maxYear: parseInt(moment().format('YYYY'), 10),
                    locale: {
                        format: 'YYYY-MM-DD',
                        separator: ' to ',
                        cancelLabel: 'Clear'
                    },
                    ranges: {
                       'Today': [moment(), moment()],
                       'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                       'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                       'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                       'This Month': [moment().startOf('month'), moment().endOf('month')],
                       'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                       'Last 3 Months': [moment().subtract(3, 'months'), moment()]
                    }
                });

                $el.on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
                    const form = $(this).closest('form');
                    if (form.length > 0) {
                        setTimeout(() => {
                            form.submit();
                        }, 100);
                    }
                });

                $el.on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    const form = $(this).closest('form');
                    if (form.length > 0) {
                        setTimeout(() => {
                            form.submit();
                        }, 100);
                    }
                });

                // Set initial value only if input is not empty (e.g. from server-side request)
                // OR set default 3 months if empty AND it's a fresh page load (no search params)
                const urlParams = new URLSearchParams(window.location.search);
                const hasDateParam = urlParams.has('date_range');

                if (currentVal && (currentVal.includes(' to ') || currentVal.includes(' - '))) {
                    const separator = currentVal.includes(' to ') ? ' to ' : ' - ';
                    const dates = currentVal.split(separator);
                    if (dates.length === 2) {
                        $el.data('daterangepicker').setStartDate(moment(dates[0]));
                        $el.data('daterangepicker').setEndDate(moment(dates[1]));
                    }
                } else if (!currentVal && !hasDateParam && !$el.hasClass('no-default-date')) {
                    // Only apply default 3 months if the field is empty AND not explicitly cleared by user (no date_range in URL)
                    const defaultStart = moment().subtract(3, 'months');
                    const defaultEnd = moment();
                    $el.val(defaultStart.format('YYYY-MM-DD') + ' to ' + defaultEnd.format('YYYY-MM-DD'));
                    $el.data('daterangepicker').setStartDate(defaultStart);
                    $el.data('daterangepicker').setEndDate(defaultEnd);
                }
            });
        }
    }

    // Initial call
    initDateRangePicker();

    // Re-initialize for dynamic content (if any)
    $(document).on('ajaxComplete', function() {
        initDateRangePicker();
    });
});

    
// image [preview]
     $('.imageInput').on('change', function(e) {
                    let file = e.target.files[0];
                    var $input = $(this);
                    
                    if (!file) return;

                    // Validate image type
                    if (!file.type.startsWith('image/')) {
                        alert('Please select a valid image file');
                        $input.val('');
                        return;
                    }

                    var $container = $input.closest('.image-preview-wrap');
                    if (!$container.length) {
                        $container = $input.closest('.mb-4');
                    }
                    if (!$container.length) {
                        $container = $input.closest('.form-group');
                    }

                    var $previews = $container.length ? $container.find('.imagePreview') : $input.siblings('.imagePreview');
                    if (!$previews.length) {
                        $previews = $('.imagePreview');
                    }

                    var $targetPreview = $previews.filter(function() {
                        return !$(this).attr('src') || $(this).hasClass('d-none');
                    }).first();

                    if (!$targetPreview.length) {
                        $targetPreview = $previews.first();
                    }

                    let reader = new FileReader();

                    reader.onload = function(event) {
                        $targetPreview
                            .attr('src', event.target.result)
                            .removeClass('d-none');
                    };

                    reader.readAsDataURL(file);
                });


   (function($){
        window.bindImagePreview = function(inputSel, previewSel){
            $(document).on('change', inputSel, function(){
                var file = this.files && this.files[0];
                var $img = $(previewSel);
                if(file){
                    var url = URL.createObjectURL(file);
                    $img.attr('src', url).removeClass('d-none');
                }else{
                    $img.attr('src','').addClass('d-none');
                }
            });
        };

        // Password Toggle Global Logic
        $(document).on('click', '.toggle-password', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            var input = $(target);
            var icon = $(this).find('iconify-icon');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.attr('icon', 'solar:eye-closed-linear');
            } else {
                input.attr('type', 'password');
                icon.attr('icon', 'solar:eye-linear');
            }
        });

        $(function(){
            bindImagePreview('#imageInput', '.newImagePreview');
        });
   })(jQuery);

// Generic image preview initializer (data-driven)
(function($){
    window.initCommonImagePreview = function(){
        // Change handler: .js-img-input with data-preview selector or nearest .js-img-preview
        $(document).on('change', '.js-img-input', function(){
            var file = this.files && this.files[0];
            var target = $(this).data('preview');
            var $img = target ? $(target) : $(this).closest('.image-preview-wrap').find('.js-img-preview').first();
            if(!$img.length) $img = $(this).siblings('.js-img-preview').first();
            if(!$img.length) return;
            if(file && file.type && file.type.indexOf('image/') === 0){
                var url = URL.createObjectURL(file);
                $img.attr('src', url).removeClass('d-none');
            }else{
                $img.attr('src','').addClass('d-none');
            }
        });
        // Remove handler: .js-img-remove with optional data-input and data-preview
        $(document).on('click', '.js-img-remove', function(){
            var inputSel = $(this).data('input');
            var previewSel = $(this).data('preview');
            var $input = inputSel ? $(inputSel) : $(this).closest('.image-preview-wrap').find('.js-img-input').first();
            var $img = previewSel ? $(previewSel) : $(this).closest('.image-preview-wrap').find('.js-img-preview').first();
            if($input.length) $input.val('');
            if($img.length) $img.attr('src','').addClass('d-none');
        });
    };
    $(function(){ initCommonImagePreview(); });
})(jQuery);
