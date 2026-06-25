$(document).ready(function () {
    // Custom validation for file extension and size
    $.validator.addMethod("extension", function(value, element, param) {
        param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
        return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
    }, "Please upload a valid image file (png, jpg, jpeg, gif).");

    $.validator.addMethod("filesize", function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, "File size must be less than 2MB.");

    if ($("#VendorForm").length > 0) {
        $("#VendorForm").validate({
            rules: {
                owner_name: { required: true, minlength: 3 },
                store_name: { required: true },
                email: { required: true, email: true },
                phone: { required: true, digits: true, minlength: 10, maxlength: 10 },
                password: { required: true, minlength: 6 },
                password_confirmation: { required: true, equalTo: "[name='password']" },
                image: {
                    extension: "png|jpe?g|gif",
                    filesize: 2 * 1024 * 1024 // 2MB
                },
                address: { required: true },
                country_id: { required: true },
                state_id: { required: true },
                city_id: { required: true },
                zip: { required: true },
                status: { required: true },
                business_name: { required: true },
                "category_ids[]": { required: true }
            },
            messages: {
                owner_name: {
                    required: "Please enter owner name",
                    minlength: "Name must be at least 3 characters"
                },
                store_name: "Please enter store name",
                email: "Please enter a valid email address",
                phone: "Please enter a valid 10-digit phone number",
                password: {
                    required: "Please provide a password",
                    minlength: "Your password must be at least 6 characters long"
                },
                password_confirmation: {
                    required: "Please confirm your password",
                    equalTo: "Passwords do not match"
                },
                image: {
                    extension: "Only png, jpg, jpeg, and gif files are allowed.",
                    filesize: "Image size must not exceed 2MB."
                },
                address: "Please enter address",
                country_id: "Please select a country",
                state_id: "Please select a state",
                city_id: "Please select a city",
                zip: "Please enter zip code",
                status: "Please select vendor status",
                business_name: "Please enter business name",
                "category_ids[]": "Please select at least one category"
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.hasClass('select2') || element.next('.select2-container').length) {
                    error.insertAfter(element.next('.select2-container'));
                } else if (element.hasClass('form-check-input')) {
                    error.insertAfter(element.closest('.form-check'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    }

    // Vendor Edit Form Validation
    if ($("#VendorEditForm").length > 0) {
        $("#VendorEditForm").validate({
            rules: {
                owner_name: { required: true, minlength: 3 },
                store_name: { required: true },
                business_name: { required: true },
                email: { required: true, email: true },
                phone: { required: true, digits: true, minlength: 10, maxlength: 10 },
                address: { required: true },
                country_id: { required: true },
                state_id: { required: true },
                city_id: { required: true },
                zip: { required: true },
                pan_no: { required: true },
                vendor_tax: { required: true },
                bank_name: { required: true },
                account_number: { required: true },
                account_holder_name: { required: true },
                branch_location: { required: true },
                "category_ids[]": { required: true }
            },
            messages: {
                owner_name: { required: "Please enter owner name", minlength: "Name must be at least 3 characters" },
                store_name: "Please enter store name",
                business_name: "Please enter business name",
                email: "Please enter a valid email address",
                phone: { required: "Please enter phone number", digits: "Please enter only digits", minlength: "Phone number must be 10 digits", maxlength: "Phone number must be 10 digits" },
                address: "Please enter address",
                country_id: "Please select country",
                state_id: "Please select state",
                city_id: "Please select city",
                zip: "Please enter zip code",
                pan_no: "Please enter PAN number",
                vendor_tax: "Please enter VAT/Tax number",
                bank_name: "Please enter bank name",
                account_number: "Please enter account number",
                account_holder_name: "Please enter account holder name",
                "category_ids[]": "Please select at least one category"
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.hasClass('select2') || element.next('.select2-container').length) {
                    error.insertAfter(element.next('.select2-container'));
                } else if (element.hasClass('form-check-input')) {
                    error.insertAfter(element.closest('.form-check'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to update this vendor profile!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#5d1a8f',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let formData = new FormData(form);
                        let submitBtn = $(form).find('button[type="submit"]');
                        let originalBtnText = submitBtn.html();

                        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');

                        $.ajax({
                            url: $(form).attr('action'),
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.status) {
                                    toastr.success(response.message);
                                    setTimeout(() => {
                                        window.location.href = BaseUrl + "/vendors-list";
                                    }, 1500);
                                } else {
                                    toastr.error(response.message || "Something went wrong!");
                                    submitBtn.prop('disabled', false).html(originalBtnText);
                                }
                            },
                            error: function(xhr) {
                                submitBtn.prop('disabled', false).html(originalBtnText);
                                if (xhr.status === 422) {
                                    let errors = xhr.responseJSON.errors;
                                    Object.keys(errors).forEach(key => {
                                        toastr.error(errors[key][0]);
                                    });
                                } else {
                                    toastr.error("An error occurred. Please try again.");
                                }
                            }
                        });
                    }
                });
                return false;
            }
        });
    }
});
