@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // For Add Product Page
        const productForm = document.getElementById('productForm');
        if (productForm) {
            productForm.addEventListener('submit', function(e) {
                const discountFields = productForm.querySelectorAll('.ve-discount-value');
                discountFields.forEach(field => {
                    const variantBlock = field.closest('.variant-block');
                    const discountType = variantBlock.querySelector('.ve-discount-type').value;
                    if ((discountType === '%' || discountType === 'percent'|| discountType === 'Percent') && parseFloat(field.value) > 100) {
                        toastr.error('Discount value cannot exceed 100 for percentage type');
                        field.classList.add('is-invalid');
                        e.preventDefault();
                    }
                });
            });
        }

        // For Product List Page (Inline Edit)
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('ve-discount-type') || e.target.classList.contains('ve-discount-value')) {
                const container = e.target.closest('.single-variant-container');
                if (container) {
                    const discountType = container.querySelector('.ve-discount-type').value;
                    const discountValueField = container.querySelector('.ve-discount-value');
                    if ((discountType === '%' || discountType === 'percent'|| discountType === 'Percent') && parseFloat(discountValueField.value) > 100) {
                        toastr.error('Discount value cannot exceed 100 for percentage type');
                        discountValueField.classList.add('is-invalid');
                    } else {
                        discountValueField.classList.remove('is-invalid');
                    }
                }
            }
        });
    });
</script>
@endpush
