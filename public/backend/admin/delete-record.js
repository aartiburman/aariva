// Generic AJAX Delete Function
function deleteAjax(url, id, rowIdPrefix = "#row_") {
    // Ensure URL doesn't have double slashes
    let cleanUrl = url.startsWith('/') ? url : '/' + url;
    let finalUrl = BaseUrl.replace(/\/$/, '') + cleanUrl;

    Swal.fire({
        title: "Are you sure?",
        text: "This record will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: finalUrl,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: {
                    id: id
                },
                success: function (response) {
                    if (response.status === true) {
                        toastr.success(response.message);
                        $(rowIdPrefix + id).fadeOut(500, function () {
                            $(this).remove();
                        });
                    } else {
                        toastr.error(response.message || "Failed to delete record.");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Delete error:", xhr.responseText);
                    toastr.error("Something went wrong!");
                }
            });
        }
    });
}

$(document).on("click", ".delete-coupon", function (e) {
    e.preventDefault();
    deleteAjax("/delete-coupon", $(this).data("id"));
});

$(document).on("click", ".delete-product-size", function (e) {
    e.preventDefault();
    deleteAjax("/delete-product-size", $(this).data("id"));
});

$(document).on("click", ".delete-product-size-category", function (e) {
    e.preventDefault();
    deleteAjax("/delete-product-size-category", $(this).data("id"));
});

$(document).on("click", ".delete-vendor", function (e) {
    e.preventDefault();
    deleteAjax("/delete-vendor", $(this).data("id"));
});

$(document).on("click", ".delete-brand", function (e) {
    e.preventDefault();
    deleteAjax("/delete-brand", $(this).data("id"));
});

$(document).on("click", ".delete-category", function (e) {
    e.preventDefault();
    deleteAjax("/delete-category", $(this).data("id"));
});

$(document).on("click", ".delete-subcategory", function (e) {
    e.preventDefault();
    deleteAjax("/delete-subcategory", $(this).data("id"));
});

$(document).on("click", ".delete-childcategory", function (e) {
    e.preventDefault();
    deleteAjax("/delete-child-category", $(this).data("id"));
});

$(document).on("click", ".delete-product", function (e) {
    e.preventDefault();
    deleteAjax("/delete-product", $(this).data("id"));
});

$(document).on("click", ".delete-offer", function (e) {
    e.preventDefault();
    deleteAjax("/delete-offer", $(this).data("id"));
});

$(document).on("click", ".delete-faq", function (e) {
    e.preventDefault();
    let id = $(this).data("id");
    console.log("FAQ delete clicked, ID:", id);
    deleteAjax("/faq-delete", id);
});
