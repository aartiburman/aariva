$(document).ready(function() {
    // Refresh NCM Tracking
    $('#refresh_tracking').on('click', function() {
        var $btn = $(this);
        var url = $btn.data('url');
        var token = $btn.data('token');
        var originalHtml = $btn.html();
        
        $btn.html('<i class="bx bx-loader bx-spin fs-16"></i> Syncing...').prop('disabled', true);

        $.ajax({
            url: url,
            type: "POST",
            data: {
                _token: token
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                    $btn.html(originalHtml).prop('disabled', false);
                }
            },
            error: function() {
                toastr.error('Sync failed. Please try again.');
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
});
