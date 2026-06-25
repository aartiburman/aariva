<!-- ========== Footer Start ========== -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <script>document.write(new Date().getFullYear())</script>
                &copy; Nepoora. Crafted with 
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
/* Stylish Circular Loader */
#page-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999999;
}

.loader-circle {
    width: 50px;
    height: 50px;
    border: 3px solid rgba(111, 66, 193, 0.1);
    border-radius: 50%;
    border-top-color: #6f42c1;
    animation: spin 1s ease-in-out infinite;
    -webkit-animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
@-webkit-keyframes spin {
    to { -webkit-transform: rotate(360deg); }
}
</style>

<div id="page-loader">
    <div class="loader-circle"></div>
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

    // ================= Chart Data =================
    window.chart_income_data  = @json($revenue_chart_data ?? []);
    window.chart_expense_data = @json($expense_chart_data ?? []);
    window.chart_labels       = @json($revenue_chart_labels ?? []);

});

$(window).on('load', function() {
    $('#page-loader').fadeOut('slow', function() {
        $(this).remove();
    });
});

// Global Button Loader on Form Submit
$(document).on('submit', 'form:not(.no-loader)', function() {
    var $form = $(this);
    
    // Check for jQuery Validation
    if (typeof $.fn.valid === 'function') {
        if (!$form.valid()) {
            return;
        }
    }
    
    var $btn = $form.find('.btn-primary[type="submit"]');
    if ($btn.length === 0) {
        $btn = $form.find('button.btn-primary').first();
    }

    if ($btn.length > 0 && !$btn.hasClass('no-loader')) {
        // Prevent double clicks
        $btn.prop('disabled', true);
        var originalHtml = $btn.html();
        
        // Add spinner - keeping original text/icon if possible
        $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + originalHtml);
        
        // Safety timeout to re-enable button if page doesn't unload (e.g. download or cancelled)
        setTimeout(function() {
            if ($btn.prop('disabled')) {
                $btn.prop('disabled', false);
                $btn.html(originalHtml);
            }
        }, 10000);
    }
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
    $(document).on('change', '.form-check-input[data-id]:not(.status-toggle)', function () {

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

<script>
    /**
     * Common function to update Player ID / Device Token for Push Notifications
     */
    window.updatePlayerId = function(playerId) {
        if (!playerId) return;
        
        @auth
        const userId = "{{ Auth::id() }}";
        const cacheKey = 'last_registered_token_' + userId;
        
        // Prevent redundant updates for the SAME user in the same session
        if (sessionStorage.getItem(cacheKey) === playerId) {
            return;
        }
        @else
        
        return;
        @endauth
        
        // console.log('Updating Player ID:', playerId);
        
        $.ajax({
            url: "{{ route('update.player.id') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                player_id: playerId,
                device_type: 'web'
            },
            success: function(response) {
                if (response.status) {
                    console.log('Token registered successfully');
                    @auth
                    sessionStorage.setItem(cacheKey, playerId);
                    @endauth
                }
            },
            error: function(err) {
                console.error('Token registration failed:', err);
            }
        });
    }
</script>

<!-- ================= Firebase FCM (FCM v1) ================= -->
@php
    $firebaseEnabled = $notificationSetting && $notificationSetting->status && $notificationSetting->firebase_app_id;
@endphp

@if($firebaseEnabled)
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.0/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.12.0/firebase-messaging.js";

    const firebaseConfig = {
        apiKey: "{{ $notificationSetting->firebase_api_key }}",
        authDomain: "{{ $notificationSetting->firebase_auth_domain }}",
        projectId: "{{ $notificationSetting->firebase_project_id }}",
        storageBucket: "{{ $notificationSetting->firebase_storage_bucket }}",
        messagingSenderId: "{{ $notificationSetting->firebase_messaging_sender_id }}",
        appId: "{{ $notificationSetting->firebase_app_id }}",
        measurementId: "{{ $notificationSetting->measurementId ?? '' }}"
    };

    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    @auth
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register("{{ url('firebase-messaging-sw.js') }}")
                    .then((registration) => {
                        return navigator.serviceWorker.ready.then(() => {
                            return getToken(messaging, {
                                vapidKey: "{{ $notificationSetting->fcm_vapid_key ?? '' }}",
                                serviceWorkerRegistration: registration
                            });
                        });
                    })
            .then((currentToken) => {
                if (currentToken) {
                    updatePlayerId(currentToken);
                }
            })
            .catch((err) => {
                console.error('Firebase error: ', err);
            });
    }
    @endauth

    onMessage(messaging, (payload) => {
        toastr.info(payload.notification.body, payload.notification.title);
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
        initSizeChoices(document.getElementById('sizeSelect'));
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
