<script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_SANDBOX_CLIENT_ID') }}"></script>

<div id="paypal-button-container"></div>

<script>
    const baseUrl = "{{ url('/') }}";

paypal.Buttons({

    createOrder: function () {
        return fetch(baseUrl +"/paypal/create-order", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(data => data.id);
    },

    onApprove: function (data) {
        return fetch(baseUrl +"/paypal/capture-order", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                orderID: data.orderID
            })
        })
        .then(res => res.json())
        .then(details => {
            Swal.fire(
                'Payment completed!',
                'You clicked the button!',
                'success'
            );
        });
    }

}).render('#paypal-button-container');
</script>
