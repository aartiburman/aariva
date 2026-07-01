<!-- ========== Footer Start ========== -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <script>document.write(new Date().getFullYear())</script>
                &copy; Aariva. Crafted with 
                <iconify-icon icon="iconamoon:heart-duotone"
                    class="fs-18 align-middle text-danger"></iconify-icon>
                <a href="https://arnit.ae" target="_blank">ARNIT</a>
            </div>
        </div>
    </div>
</footer>
<!-- ========== Footer End ========== -->

</div>
</div>
<!-- END Wrapper -->

<style>
#global-loader{position:fixed;inset:0;background:rgba(255,255,255,.6);backdrop-filter:saturate(180%) blur(2px);z-index:2147483000;display:none}
#global-loader .spinner{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)}
body.loading-process{cursor:progress}
</style>
<div id="global-loader">
    <div class="spinner">
        <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem"></div>
    </div>
</div>

<!-- ================= Vendor & Core Scripts ================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('backend/assets/js/vendor.js') }}"></script>
<script src="{{ asset('backend/assets/js/app.js') }}"></script>
<script src="{{ asset('backend/assets/js/choices.min.js') }}"></script>
<script src="{{ asset('backend/admin/custom.js') }}"></script>
<script src="{{ asset('backend/admin/common.js') }}"></script>
<!-- Bridge Blade data to static JS BEFORE loading change-status.js -->

<script>
  // Base URL and CSRF for static/public scripts
  window.BaseUrl = "{{ url('') }}";
  window.csrf = "{{ csrf_token() }}";
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="{{ asset('backend/admin/validation.js') }}"></script>
<script src="{{ asset('backend/admin/delete-record.js') }}?v={{ time() }}"></script>

<script src="{{ asset('backend/admin/change-status.js') }}"></script>

<!-- Moment.js and DateRangePicker.js -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- ================= Global Variables ================= -->
<script>
$(document).ready(function () {

    // ================= Global Variables =================
    var GoogleApiKey = "{{ config('app.google_api_key') }}";
    var csrfToken = "{{ csrf_token() }}";

    // ================= Update Player ID Function =================
    window.updatePlayerId = function (id) {

   
        $.ajax({
            url: "{{ route('update.player.id') }}",
            type: "POST",
            data: {
                player_id: id,
                device_type: 'web',
                _token: csrfToken
            },
            success: function (response) {
                console.log('Player ID updated');
            },
            error: function (xhr) {
                console.log('Error updating player ID');
            }
        });
    };


    // ================= Chart Data =================
    window.chart_income_data  = @json($revenue_chart_data ?? []);
    window.chart_expense_data = @json($expense_chart_data ?? []);
    window.chart_labels       = @json($revenue_chart_labels ?? []);

});
</script>

<!-- ================= Page Loader ================= -->
<script>
$(function(){
    let loaderTimer = null;

    function hideLoader(){
        $('#global-loader').fadeOut(200);
        $('body').removeClass('loading-process');
        if (loaderTimer) {
            clearTimeout(loaderTimer);
            loaderTimer = null;
        }
    }

    function showLoaderDelayed(delayMs = 300){
        if (loaderTimer) clearTimeout(loaderTimer);
        loaderTimer = setTimeout(function(){
            $('#global-loader').fadeIn(150);
            $('body').addClass('loading-process');
        }, delayMs);
    }

    function isExportElement(el){
        const $el = $(el);
        if ($el.is('[data-export]') || $el.is('.export-btn') || $el.is('#bulk-export-btn')) return true;
        if ($el.is('[download]')) return true;
        const href = $el.attr('href') || '';
        if (!href) return false;
        const lower = href.toLowerCase();
        return lower.endsWith('.csv') || lower.endsWith('.xlsx') || lower.endsWith('.xls') || lower.includes('download') || lower.includes('template') || lower.includes('export');
    }

    $(window).on('load pageshow', hideLoader);

    setTimeout(function(){
        if($('#global-loader').is(':visible')){
            hideLoader();
        }
    },10000);

    // Hide loader for exports/downloads
    $(document).on('click', 'a, button', function(){
        if (!isExportElement(this)) return;
        hideLoader();
    });

    // Show loader for most clicks that trigger navigation (with a small delay to avoid flicker)
    $(document).on('click','a:not([data-export]):not(.export-btn):not(#bulk-export-btn)',function(){
        if (isExportElement(this)) return;
        const href = $(this).attr('href');
        const target = $(this).attr('target');
        if(href && href !== '#' &&
           !href.startsWith('javascript:') &&
           target !== '_blank' &&
           !$(this).attr('data-bs-toggle')){
            showLoaderDelayed(300);
        }
    });


    // Show loader when select changes (if it triggers navigation)
$(document).on('change', 'select:not([data-select]):not(.no-loader)', function () {

    if (isExportElement(this)) return;

    const value = $(this).val();
    const target = $(this).attr('data-target');
    const name = $(this).attr('name');
    
    // Skip loader for category/subcategory/child category and location selects
    if (name && ['category_id', 'subcategory_id', 'child_category_id', 'brand_id', 'country_id', 'state_id', 'city_id'].includes(name)) {
        return;
    }

    // If select value is URL
    if (value && value !== '#' && value.startsWith('http')) {
        showLoaderDelayed(300);
        window.location.href = value;
    }

    // OR if select has data-target URL
    else if (target) {
        showLoaderDelayed(300);
        window.location.href = target;
    }
});
    // Show loader for form submissions (except ones explicitly opting out or failing validation)
    $(document).on('submit','form:not(.no-loader)',function(){
        if (isExportElement(this)) return;

        // If jQuery Validation is present, don't show loader if form is invalid
        if (typeof $.fn.valid === 'function') {
            if (!$(this).valid()) {
                return;
            }
        }

        showLoaderDelayed(300);
    });

    // Global AJAX loader with delay; hides on completion
    $(document).ajaxStart(function(){
        showLoaderDelayed(300);
    });
    $(document).ajaxStop(function(){
        hideLoader();
    });
});
</script>

@auth

<script>
// Background session heartbeat to enforce auto-logout on session mismatch
(function(){
    let timer = null;
    function ping(){
        fetch("{{ route('session.heartbeat') }}", {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(resp){
            return resp.json();
        }).then(function(data){
            if (data && data.valid === false && data.redirect) {
                window.location.href = data.redirect;
            }
        }).catch(function(){ /* ignore */ });
    }
    // Start after a short delay then every 60s
    setTimeout(function(){
        ping();
        timer = setInterval(ping, 60000);
    }, 5000);
})();
</script>
@endauth


<script>
     $(document).ready(function () {

    var checkAll = $('#checkAll');
    var exportBtn = $('#bulk-export-btn');
    var deleteBtn = $('#bulk-delete-btn');

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    function getRowCheckboxes() {
        return $('.form-check-input[data-id]:not(.status-toggle)');
    }

    function toggleActionBtns() {
        var checkedCount = getRowCheckboxes().filter(':checked').length;

        if (checkedCount > 0) {
            exportBtn.removeClass('d-none');
            deleteBtn.removeClass('d-none');
        } else {
            exportBtn.addClass('d-none');
            deleteBtn.addClass('d-none');
        }
    }

    // Select All
    checkAll.on('change', function () {
        getRowCheckboxes().prop('checked', $(this).is(':checked'));
        toggleActionBtns();
    });

    // Single Checkbox Change
    $(document).on('change', '.form-check-input[data-id]', function () {

        var rowCheckboxes = getRowCheckboxes();
        var total = rowCheckboxes.length;
        var checked = rowCheckboxes.filter(':checked').length;

        checkAll.prop('checked', total === checked);
        toggleActionBtns();
    });

    // Bulk Export
    exportBtn.on('click', function () {

        var ids = getRowCheckboxes().filter(':checked').map(function () {
            return $(this).data('id');
        }).get();

        if (ids.length > 0) {

            var form = $('<form>', {
                action: window.bulkRoutes.exportUrl,
                method: "POST",
                target: "_blank"
            });

            form.append('<input type="hidden" name="_token" value="' + csrfToken + '">');

            $.each(ids, function (index, id) {
                form.append('<input type="hidden" name="ids[]" value="' + id + '">');
            });

            $('body').append(form);
            form.submit();
            form.remove();
        }
    });

    // Bulk Delete
    deleteBtn.on('click', function () {

        var ids = getRowCheckboxes().filter(':checked').map(function () {
            return $(this).data('id');
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then(function (result) {

            if (result.isConfirmed) {

                $.ajax({
                    url: window.bulkRoutes.deleteUrl,
                    type: "POST",
                    data: {
                        ids: ids
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function (response) {

                        if (response.status) {

                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then(function () {
                                location.reload();
                            });

                        } else {

                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function () {

                        Swal.fire(
                            'Error!',
                            'Something went wrong.',
                            'error'
                        );
                    }
                });
            }
        });
    });

});

</script>
<script>
    // CK Editor – guard against missing element
    const editorEl = document.querySelector('#editor');
    if (editorEl) {
        ClassicEditor
            .create(editorEl)
            .catch(error => {
                console.error('CKEditor initialization error:', error);
            });
    }
</script>

@php
    $showVendorPolicyModal = false;
    $latestVendorPolicy = null;
    if(auth()->check() && auth()->user()->role == '2'){
        $latestVendorPolicy = \App\Models\VendorPolicy::where('status',1)->latest('created_at')->first();
        if($latestVendorPolicy){
            $showVendorPolicyModal = !\App\Models\VendorPolicyAcceptance::where('vendor_id', auth()->id())->where('policy_id', $latestVendorPolicy->id)->exists();
        }
    }
@endphp

@if($showVendorPolicyModal && $latestVendorPolicy)
<!-- Vendor Policy Modal -->
<div class="modal fade" id="vendorPolicyModal" tabindex="-1" aria-labelledby="vendorPolicyLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vendorPolicyLabel">{{ $latestVendorPolicy->title }}</h5>
      </div>
      <div class="modal-body">
        {!! $latestVendorPolicy->{"content_".app()->getLocale()} ?? $latestVendorPolicy->content !!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id="acceptVendorPolicyBtn" class="btn btn-primary" data-policy-id="{{ $latestVendorPolicy->id }}">Accept</button>
      </div>
    </div>
  </div>
  </div>
<script>
    $(function(){
        const modal = new bootstrap.Modal(document.getElementById('vendorPolicyModal'));
        modal.show();
        $('#acceptVendorPolicyBtn').on('click', function(){
            const policyId = $(this).data('policy-id');
            $.post("{{ route('vendor.policy.accept') }}", {
                _token: "{{ csrf_token() }}",
                policy_id: policyId
            }, function(res){
                if(res.status){
                    toastr.success(res.message || 'Policy accepted');
                    modal.hide();
                }else{
                    toastr.error(res.message || 'Unable to accept policy');
                }
            }).fail(function(){
                toastr.error('Something went wrong');
            });
        });
    });
</script>
@endif


<!-- ================= Firebase OR OneSignal (Single Instance Only) ================= -->
@php
    $firebaseEnabled = $notificationSetting && $notificationSetting->status && $notificationSetting->firebase_app_id;
    $oneSignalEnabled = $notificationSetting && $notificationSetting->status && $notificationSetting->onesignal_app_id;
@endphp

@if($firebaseEnabled)
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
<script>
    const firebaseConfig = {
        apiKey: "{{ $notificationSetting->firebase_api_key }}",
        authDomain: "{{ $notificationSetting->firebase_auth_domain }}",
        projectId: "{{ $notificationSetting->firebase_project_id }}",
        storageBucket: "{{ $notificationSetting->firebase_storage_bucket }}",
        messagingSenderId: "{{ $notificationSetting->firebase_messaging_sender_id }}",
        appId: "{{ $notificationSetting->firebase_app_id }}"
    };

    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    @auth
    messaging.getToken({
        vapidKey: "{{ $notificationSetting->fcm_vapid_key ?? '' }}"
    }).then((currentToken) => {
        if (currentToken) {
             updatePlayerId(currentToken);
        } else {
            console.log('No registration token available. Request permission to generate one.');
        }
    }).catch((err) => {
        if (err.code === 'messaging/permission-blocked') {
             console.warn('Notification permission was blocked.');
        } else {
             console.error('An error occurred while retrieving token. ', err);
        }
    });
    @endauth
    
</script>
@elseif($oneSignalEnabled)
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
window.OneSignalDeferred = window.OneSignalDeferred || [];
OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
        appId: "{{ $notificationSetting->onesignal_app_id }}",
        safari_web_id: "{{ $notificationSetting->onesignal_safari_web_id ?? '' }}",
        notifyButton: { enable: true },
        allowLocalhostAsSecureOrigin: true,
    });

    @auth
    const playerId = OneSignal.User.PushSubscription.id;
    if(playerId) updatePlayerId(playerId);

    OneSignal.User.PushSubscription.addEventListener("change", function(e){
        if(e.current.id) updatePlayerId(e.current.id);
    });
    @endauth
    
});
</script>
@endif


<!-- ================= Toastr Session Messages ================= -->
 
<script>
    $(document).ready(function() {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        @if($errors->any() && !isset($disableGlobalToastr))
            @foreach($errors->all() as $error)
                toastr.error(@json($error));
            @endforeach
        @endif
    });
</script>


<!-- ================= Status Toggle ================= -->
<script>
$(document).on('change','.status-toggle',function(){
    const checkbox = $(this);
    $.post(checkbox.data('url'),{
        _token:"{{ csrf_token() }}",
        id:checkbox.data('id'),
        status:checkbox.prop('checked')?1:0
    }, function(response){
        if(response.status){
            toastr.success(response.message);
        }else{
            toastr.error(response.message);
            checkbox.prop('checked',!checkbox.prop('checked'));
        }
    }).fail(function(){
        checkbox.prop('checked',!checkbox.prop('checked'));
        toastr.error('Something went wrong');
    });
});
</script>


<!-- ================= Toggle Variant ================= -->
<script>
$(document).on('click','.toggle-variant',function(){
    $('#variant_'+$(this).data('id')).slideToggle(200);
});
</script>
<script>
    //  WIzard form
$(function () {
    let currentStep = 0;
    const steps = $(".step-content");
    const dots = $(".step-dot");

    function showStep(step) {
        steps.removeClass("active").eq(step).addClass("active");
        dots.removeClass("active").eq(step).addClass("active");
    }

    $(".nextBtn").click(function () {
        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
        }
    });

    $(".prevBtn").click(function () {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    });

    $(".step-dot").click(function () {
        currentStep = $(this).data("step");
        showStep(currentStep);
    });
});

</script>

<!-- ================= Delete Variant ================= -->
<script>
$(document).on('click','.removeExistVariant',function(){
    let block = $(this).closest('.variant-block');
    let id = block.find('input[name="variant_id[]"]').val();

    if(!id) return block.remove();

    Swal.fire({
        title: "Are you sure?",
        text: "This variant will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("{{ route('delete.variant') }}",{
                _token:"{{ csrf_token() }}",
                variant_id:id
            },res=>{
                if(res.success){
                     block.remove();
                     toastr.success(res.message);
                } else {
                     toastr.error(res.message);
                }
            }).fail(function(){
                toastr.error('Something went wrong');
            });
        }
    });
});
</script>


<script>
    $(document).ready(function() {
        if (typeof $.fn.daterangepicker !== 'undefined') {
            $('.range-datepicker').attr('autocomplete', 'off');
            $('.range-datepicker').daterangepicker({
                autoUpdateInput: false,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 3 Months': [moment().subtract(3, 'month'), moment()],
                    'Last 6 Months': [moment().subtract(6, 'month'), moment()],
                    'This Year': [moment().startOf('year'), moment()],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
                },
                locale: {
                    format: 'Y-MM-DD',
                    cancelLabel: 'Clear'
                }
            });

            $('.range-datepicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
                $(this).closest('form').submit();
            });

            $('.range-datepicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $(this).closest('form').submit();
            });
        }
    });
</script>
<script>
    (function($){
        $(function(){
            if ($.fn && $.fn.mobiscroll) {
                try {
                    $('.multiple-select').mobiscroll().select({
                        inputElement: document.getElementById('my-input'),
                        touchUi: false
                    });
                } catch (e) {
                    console.warn('Mobiscroll init failed:', e);
                }
            } else {
                // Fallback: leave native select, or initialize Choices if available
                if (window.Choices) {
                    document.querySelectorAll('.multiple-select').forEach(function(el){
                        try { new Choices(el, { removeItemButton: true }); } catch(_){}
                    });
                }
            }
        });
    })(jQuery);
    </script>
<script>
    $(document).ready(function() {
        function pollNotifications() {
            $.ajax({
                url: '{{ route("notifications.poll") }}',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    var badge = $('#page-header-notifications-dropdown .topbar-badge');
                    var list = $('#page-header-notifications-dropdown').next('.dropdown-menu').find('[data-simplebar]');

                    if (response.count > 0) {
                        if (badge.length) {
                            badge.text(response.count);
                        } else {
                            $('#page-header-notifications-dropdown').append(
                                '<span class="position-absolute topbar-badge fs-10 translate-middle badge bg-danger rounded-pill">' +
                                response.count +
                                '<span class="visually-hidden">unread messages</span></span>'
                            );
                        }
                    } else if (badge.length) {
                        badge.remove();
                    }

                    if (response.notifications && response.notifications.length) {
                        var html = '';
                        response.notifications.forEach(function(n) {
                            html += '<a href="' + n.url + '" class="dropdown-item py-3 border-bottom text-wrap bg-light">' +
                                '<div class="d-flex">' +
                                '<div class="flex-shrink-0">' +
                                '<div class="avatar-sm me-2">' +
                                '<span class="avatar-title bg-soft-' + n.color + ' text-' + n.color + ' fs-20 rounded-circle">' +
                                '<iconify-icon icon="' + n.icon + '"></iconify-icon>' +
                                '</span></div></div>' +
                                '<div class="flex-grow-1">' +
                                '<p class="mb-0 fw-semibold">' + n.title + '</p>' +
                                '<p class="mb-0 text-wrap">' + n.message + '</p>' +
                                '<small class="text-muted">' + n.time + '</small>' +
                                '</div></div></a>';
                        });
                        list.html(html);
                    } else {
                        list.html('<div class="p-3 text-center"><p class="mb-0 text-muted">No notifications found</p></div>');
                    }
                }
            });
        }

        pollNotifications();
        setInterval(pollNotifications, 30000);
    });
</script>
@stack('scripts')

<!-- Location Select JS -->
</body>
</html>
