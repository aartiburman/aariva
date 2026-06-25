$(document).ready(function () {

    $("#VendorForm").validate({

        rules: {
            owner_name: { required: true, minlength: 2 },
            store_name: { required: true },
            email: { required: true, email: true },
            phone: { required: true, digits: true, minlength: 10, maxlength: 10 },
            password: { required: true, minlength: 6 },
            password_confirmation: { required: true, equalTo: "[name='password']" },
            address: { required: true },
            city: { required: true },
            state: { required: true },
            zip: { required: true, digits: true },
            business_name: { required: true },
            tax_id: { required: true },
            bank_name: { required: true },
            account_number: { required: true, digits: true },
            ifsc_code: { required: true },
            status: { required: true },
            agreement: { required: true }
        },

        errorClass: "invalid-feedback",
        errorElement: "div",

        errorPlacement: function (error, element) {
            element.closest(".mb-3").append(error);
        },

        highlight: function (element) {
            $(element).addClass("is-invalid");
        },

        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },

        submitHandler: function (form) {

            let formData = new FormData(form);

            $.ajax({
                url: $(form).attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $(".btn-primary").prop("disabled", true).text("Saving...");
                },
                success: function (response) {

                    toastr.success("Vendor created successfully!");

                    $("#VendorForm")[0].reset();
                    $(".is-invalid").removeClass("is-invalid");
                },
                error: function (xhr) {

                    $(".btn-primary").prop("disabled", false).text("Create Vendor");

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function (key, value) {
                            let input = $('[name="' + key + '"]');
                            input.addClass("is-invalid");
                            input.closest(".mb-3").find(".invalid-feedback").remove();
                            input.closest(".mb-3").append(
                                '<div class="invalid-feedback">' + value[0] + '</div>'
                            );
                        });
                    } else {
                        toastr.error("Something went wrong!");
                    }
                },
                complete: function () {
                    $(".btn-primary").prop("disabled", false).text("Create Vendor");
                }
            });

            return false;
        }
    });



      $("#CategoryForm").validate({

        rules: {
            name: { required: true, minlength: 2 },
            slug: { required: true },
            // description: { required: true },
            // meta_title :{required: true},
            // meta_description :{required: true},
            // is_active: { required: true }

        },

        errorClass: "invalid-feedback",
        errorElement: "div",

        errorPlacement: function (error, element) {
            element.closest(".mb-3").append(error);
        },

        highlight: function (element) {
            $(element).addClass("is-invalid");
        },

        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },

        submitHandler: function (form) {

            let formData = new FormData(form);

            $.ajax({
                url: $(form).attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $(".btn-primary").prop("disabled", true).text("Saving...");
                },
                success: function (response) {

                    toastr.success("Category created successfully!");

                    $("#CategoryForm")[0].reset();
                    $(".is-invalid").removeClass("is-invalid");
                },
                error: function (xhr) {

                    $(".btn-primary").prop("disabled", false).text("Create Categroy");

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function (key, value) {
                            let input = $('[name="' + key + '"]');
                            input.addClass("is-invalid");
                            input.closest(".mb-3").find(".invalid-feedback").remove();
                            input.closest(".mb-3").append(
                                '<div class="invalid-feedback">' + value[0] + '</div>'
                            );
                        });
                    } else {
                        toastr.error("Something went wrong!");
                    }
                },
                complete: function () {
                    $(".btn-primary").prop("disabled", false).text("Create Category");
                }
            });

            return false;
        }
    });


    

});

$(document).ready(function () {

    $("#BrandForm").validate({

        rules: {
            name: {
                required: true,
                minlength: 2
            },
            slug: {
                required: true
            },
            logo: {
                extension: "jpg|jpeg|png|webp"
            },
            status: {
                required: true
            }
        },

        messages: {
            name: {
                required: "Brand name is required",
                minlength: "Brand name must be at least 3 characters"
            },
            slug: {
                required: "Slug is required"
            },
            logo: {
                extension: "Only JPG, PNG, JPEG, WEBP files allowed"
            },
            status: {
                required: "Please select status"
            }
        },

        errorClass: "invalid-feedback",
        errorElement: "div",

        errorPlacement: function (error, element) {
            element.closest(".mb-3").append(error);
        },

        highlight: function (element) {
            $(element).addClass("is-invalid");
        },

        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },

        submitHandler: function (form) {

            let formData = new FormData(form);

            $.ajax({
                url: $(form).attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function () {
                    $(".btn-primary").prop("disabled", true).text("Saving...");
                },

                success: function (response) {

                    toastr.success("Brand saved successfully!");

                    $("#BrandForm")[0].reset();
                    $(".is-invalid").removeClass("is-invalid");
                },

                error: function (xhr) {

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function (key, value) {
                            let input = $('[name="' + key + '"]');
                            input.addClass("is-invalid");
                            input.closest(".mb-3").find(".invalid-feedback").remove();
                            input.closest(".mb-3").append(
                                '<div class="invalid-feedback">' + value[0] + '</div>'
                            );
                        });
                    } else {
                        toastr.error("Something went wrong!");
                    }
                },

                complete: function () {
                    $(".btn-primary").prop("disabled", false).text("Save Brand");
                }
            });

            return false;
        }
    });

});

