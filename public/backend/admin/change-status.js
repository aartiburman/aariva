$(document).ready(function () {
    // CSRF Token
    const csrf = $('meta[name="csrf-token"]').attr('content') || window.csrf;

    // --- Order Status ---
    $(document).on("click", ".order-status-badge", function () {
        const $badge = $(this);
        if ($badge.css('cursor') === 'pointer') {
            $badge.addClass("d-none");
            $badge.siblings(".order-status-select").removeClass("d-none").focus();
        }
    });

    // Use a flag to prevent double submission
    let isUpdatingOrder = false;


    // --- Payment Status ---
    $(document).on("click", ".payment-status-badge", function () {
        const $badge = $(this);
        if ($badge.css('cursor') === 'pointer') {
            $badge.addClass("d-none");
            $badge.siblings(".payment-status-select").removeClass("d-none").focus();
        }
    });

    // Use a flag to prevent double submission
    let isUpdatingPayment = false;

    $(document).on("change", ".payment-status-select", function () {
        if (isUpdatingPayment) return;
        isUpdatingPayment = true;
        const $select = $(this);
        const id = $select.data("order-id");
        const status = $select.val();
        $.ajax({
            url: window.BaseUrl + "/update-payment-status",
            type: "POST",
            data: { 
                order_id: id, 
                payment_status: status, 
                _token: csrf 
            },
            success: function (res) {
                if (res.status) {
                    toastr.success(res.message || "Status updated");
                } else {
                    toastr.error(res.message || "Update failed");
                    $select.addClass("d-none");
                    $select.siblings(".payment-status-badge").removeClass("d-none");
                    isUpdatingPayment = false;
                }
            },
            error: function () {
                toastr.error("Something went wrong");
                $select.addClass("d-none");
                $select.siblings(".payment-status-badge").removeClass("d-none");
                isUpdatingPayment = false;
            }
        });
    });
});


