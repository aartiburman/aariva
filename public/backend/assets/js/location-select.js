$(document).ready(function() {
    // Helper to get base URL if not defined
    var baseUrl = (typeof BaseUrl !== 'undefined') ? BaseUrl : window.location.origin;

    // Handle Country Change
    $(document).on('change', '.country_id', function() {
        var countryId = $(this).val();
        var stateSelect = $(this).closest('form').find('.state_id'); // Find within the same form
        var citySelect = $(this).closest('form').find('.city_id');

        // If not found in form (e.g. widely separated), try global class
        if (stateSelect.length === 0) stateSelect = $('.state_id');
        if (citySelect.length === 0) citySelect = $('.city_id');

        stateSelect.html('<option value="">Loading...</option>');
        citySelect.html('<option value="">Select City</option>');

        if (countryId) {
            // Disable selects and show local loader
            stateSelect.prop('disabled', true).html('<option value="">Loading...</option>');
            citySelect.prop('disabled', true).html('<option value="">Select City</option>');

            $.ajax({
                url: baseUrl + '/get-states/' + countryId,
                type: 'GET',
                dataType: 'json',
                global: false, // Prevent global loader
                success: function(data) {
                    stateSelect.empty().append('<option value="">Select State</option>');
                    $.each(data, function(key, value) {
                        stateSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching states:", error);
                    stateSelect.html('<option value="">Select State</option>');
                },
                complete: function() {
                    // Re-enable selects
                    stateSelect.prop('disabled', false);
                    citySelect.prop('disabled', false);
                }
            });
        } else {
            stateSelect.html('<option value="">Select State</option>');
        }
    });

    // Handle State Change
    $(document).on('change', '.state_id', function() {
        var stateId = $(this).val();
        var citySelect = $(this).closest('form').find('.city_id');

        if (citySelect.length === 0) citySelect = $('.city_id');

        citySelect.html('<option value="">Loading...</option>');

        if (stateId) {
            // Disable city select and show local loader
            citySelect.prop('disabled', true).html('<option value="">Loading...</option>');

            $.ajax({
                url: baseUrl + '/get-cities/' + stateId,
                type: 'GET',
                dataType: 'json',
                global: false, // Prevent global loader
                success: function(data) {
                    citySelect.empty().append('<option value="">Select City</option>');
                    $.each(data, function(key, value) {
                        citySelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching cities:", error);
                    citySelect.html('<option value="">Select City</option>');
                },
                complete: function() {
                    // Re-enable city select
                    citySelect.prop('disabled', false);
                }
            });
        } else {
            citySelect.html('<option value="">Select City</option>');
        }
    });
});
