//    add Vendor
   $('#email_input').on('blur keyup', function() {
        var email = $(this).val();
        if (email.length > 5) {
            $.ajax({
                url: BaseUrl + "/check-email-availability",
                method: "POST",
                data: {
                    email: email
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function(response) {
                    if (response.available) {
                        $('#email_check_msg').text(response.message).removeClass('text-danger').addClass('text-success');
                        $('#email_input').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#email_check_msg').text(response.message).removeClass('text-success').addClass('text-danger');
                        $('#email_input').removeClass('is-valid').addClass('is-invalid');
                    }
                }
            });
        } else {
            $('#email_check_msg').text('');
            $('#email_input').removeClass('is-valid is-invalid');
        }
    });

    $("#VendorForm").validate({
        rules: {
            "category_ids[]": {
                required: true
            }
        },
        messages: {
            "category_ids[]": "Please select at least one category."
        },
        ignore: [],
        errorElement: 'small',
        errorPlacement: function(error, element) {
            error.addClass('text-danger');
            if (element.attr("name") == "category_ids[]") {
                element.next('.choices').after(error);
            } else {
                element.closest('.mb-3, .mb-4').append(error);
            }
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr("name") == "category_ids[]") {
                $(element).next('.choices').find('.choices__inner').addClass('is-invalid');
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            if ($(element).attr("name") == "category_ids[]") {
                $(element).next('.choices').find('.choices__inner').removeClass('is-invalid');
            }
        }
    });


    // edit vendor 
    
    // Custom validation for file extension and size
    $.validator.addMethod("extension", function(value, element, param) {
        param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
        return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
    }, "Please upload a valid image file (png, jpg, jpeg, gif).");

    $.validator.addMethod("filesize", function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, "File size must be less than 2MB.");

    $("#VendorEditForm").validate({
        rules: {
            owner_name: {
                required: true,
                minlength: 3
            },
            store_name: {
                required: true
            },
            business_name: {
                required: true
            },
            image: {
                extension: "png|jpe?g|gif",
                filesize: 2 * 1024 * 1024 // 2MB
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10
            },
            address: {
                required: true
            },
            country_id: {
                required: true
            },
            state_id: {
                required: true
            },
            city_id: {
                required: true
            },
            zip: {
                required: true
            },
            pan_no: {
                required: true
            },
            vendor_tax: {
                required: true
            },
            bank_name: {
                required: true
            },
            account_number: {
                required: true
            },
            account_holder_name: {
                required: true
            },
            ifsc_code: {
                required: true
            },
            status: {
                required: true
            },
            "category_ids[]": {
                required: true
            }
        },
        messages: {
            owner_name: {
                required: "Please enter the owner name",
                minlength: "Owner name must be at least 3 characters"
            },
            store_name: "Please enter the store name",
            business_name: "Please enter the business name",
            image: {
                extension: "Only png, jpg, jpeg, and gif files are allowed.",
                filesize: "Image size must not exceed 2MB."
            },
            email: "Please enter a valid email address",
            phone: {
                required: "Please enter the phone number",
                digits: "Please enter only digits",
                minlength: "Phone number must be exactly 10 digits",
                maxlength: "Phone number must be exactly 10 digits"
            },
            address: "Please enter the address",
            country_id: "Please select a country",
            state_id: "Please select a state",
            city_id: "Please select a city",
            zip: "Please enter the zip code",
            pan_no: "Please enter the PAN number",
            vendor_tax: "Please enter the VAT/Tax number",
            bank_name: "Please enter the bank name",
            account_number: "Please enter the account number",
            account_holder_name: "Please enter the account holder name",
            ifsc_code: "Please enter the IFSC/SWIFT code",
            status: "Please select a status",
            "category_ids[]": "Please select at least one category."
        },
        ignore: [],
        errorElement: 'small',
        errorPlacement: function(error, element) {
            error.addClass('text-danger');
            if (element.attr("name") == "category_ids[]") {
                element.next('.choices').after(error);
            } else {
                element.closest('.mb-3, .mb-4').append(error);
            }
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr("name") == "category_ids[]") {
                $(element).next('.choices').find('.choices__inner').addClass('is-invalid');
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            if ($(element).attr("name") == "category_ids[]") {
                $(element).next('.choices').find('.choices__inner').removeClass('is-invalid');
            }
        }
    });
