@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Product Section -->
            <div class="col-lg-7">
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                    </span>
                                    <input type="text" id="product-search" class="form-control border-start-0" placeholder="Search product by name or SKU...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="category-filter" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('pos.history') }}" class="btn btn-outline-info w-100">
                                    <iconify-icon icon="solar:history-linear" class="me-1"></iconify-icon> History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3" id="product-grid">
                    <!-- Products will be loaded here via AJAX -->
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    <div id="pagination-links"></div>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="col-lg-5">
                <div class="card sticky-top" style="top: 80px; z-index: 10;">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Current Order</h5>
                        <button class="btn btn-sm btn-outline-danger" id="clear-cart">Clear All</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-nowrap align-middle mb-0" id="cart-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th style="width: 100px;">Qty</th>
                                        <th class="text-end">Total</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Cart items will be added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold" id="cart-subtotal">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax (0%)</span>
                            <span class="fw-bold" id="cart-tax">0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fs-16 fw-bold">Total</span>
                            <span class="fs-16 fw-bold text-primary" id="cart-total">0.00</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <div class="d-flex flex-wrap gap-2">
                                <input type="radio" class="btn-check" name="payment_method" id="pay-cash" value="cash" checked>
                                <label class="btn btn-outline-primary flex-fill" for="pay-cash">
                                    <iconify-icon icon="solar:wad-of-money-linear" class="me-1"></iconify-icon> Cash
                                </label>

                                <!-- <input type="radio" class="btn-check" name="payment_method" id="pay-card" value="card">
                                <label class="btn btn-outline-primary flex-fill" for="pay-card">
                                    <iconify-icon icon="solar:card-linear" class="me-1"></iconify-icon> Card
                                </label>

                                <input type="radio" class="btn-check" name="payment_method" id="pay-online" value="online">
                                <label class="btn btn-outline-primary flex-fill" for="pay-online">
                                    <iconify-icon icon="solar:qr-code-linear" class="me-1"></iconify-icon> Online
                                </label> -->
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 py-2 fs-15" id="checkout-btn" disabled>
                            <iconify-icon icon="solar:cart-check-linear" class="me-1"></iconify-icon> Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Variant Selection Modal -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-product-name">Select Variant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="variant-options">
                <!-- Variant options will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="add-to-cart-confirm">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let currentProduct = null;
    let currencySymbol = '{{ $currencySymbol }}';

    // Load Products
    window.loadProducts = function(page = 1) {
        let search = $('#product-search').val();
        let categoryId = $('#category-filter').val();

        $.ajax({
            url: BaseUrl + "/pos/search-products",
            type: "GET",
            global: false, // Stop global loading spinner
            data: {
                page: page,
                search: search,
                category_id: categoryId
            },
            success: function(response) {
                if (response.status) {
                    renderProductGrid(response.data.data);
                    renderPagination(response.data);
                }
            }
        });
    }

    function renderProductGrid(products) {
        let grid = $('#product-grid');
        grid.empty();

        if (products.length === 0) {
            grid.append('<div class="col-12 text-center py-5"><h5 class="text-muted">No products found</h5></div>');
            return;
        }

        products.forEach(product => {
            let image = product.thumbnail_url;
            let price = product.variants.length > 0 ? product.variants[0].price : '0.00';
            
            // Calculate total stock
            let totalStock = 0;
            if (product.variants && product.variants.length > 0) {
                totalStock = product.variants.reduce((sum, v) => sum + (parseInt(v.stock) || 0), 0);
            }

            grid.append(`
                <div class="col-md-3">
                    <div class="card product-card h-100 cursor-pointer" onclick="handleProductClick(${product.id})">
                        <img src="${image}" class="card-img-top p-2" alt="${product.name}" style="height: 150px; object-fit: contain;">
                        <div class="card-body p-2 text-center">
                            <h6 class="fs-13 mb-1 text-truncate">${product.name}</h6>
                            <p class="text-primary fw-bold mb-0">${currencySymbol}${price}</p>
                            <div class="mt-1">
                                <span class="badge ${totalStock > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} fs-11">
                                    Stock: ${totalStock}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    }

    function renderPagination(data) {
        let links = $('#pagination-links');
        links.empty();

        if (data.last_page <= 1) return;

        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        
        // Previous
        html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadProducts(${data.current_page - 1})">Previous</a>
                 </li>`;

        // Pages
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0)" onclick="loadProducts(${i})">${i}</a>
                         </li>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next
        html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadProducts(${data.current_page + 1})">Next</a>
                 </li>`;

        html += '</ul></nav>';
        links.append(html);
    }

    // Product Click Handler
    window.handleProductClick = function(id) {
        $.ajax({
            url: BaseUrl + `/pos/product-details/${id}`,
            type: "GET",
            global: false, // Stop global loading spinner
            success: function(response) {
                if (response.status) {
                    currentProduct = response.data;
                    
                    // If multiple variants OR a single variant with multiple sizes (JSON array)
                    let needsModal = false;
                    if (currentProduct.variants && currentProduct.variants.length > 1) {
                        needsModal = true;
                    } else if (currentProduct.variants && currentProduct.variants.length === 1) {
                        let sizeValue = currentProduct.variants[0].size || '';
                        if (sizeValue && sizeValue.startsWith('[') && sizeValue.endsWith(']')) {
                            try {
                                let sizes = JSON.parse(sizeValue);
                                if (sizes.length > 1) needsModal = true;
                            } catch(e) {}
                        }
                    }

                    if (needsModal) {
                        showVariantModal(currentProduct);
                    } else if (currentProduct.variants && currentProduct.variants.length === 1) {
                        // Single variant, single (or no) size
                        let variant = currentProduct.variants[0];
                        
                        if (variant.stock <= 0) {
                            toastr.error('This product is out of stock');
                            return;
                        }

                        let sizeValue = variant.size || '';
                        let size = '';
                        if (sizeValue && sizeValue.startsWith('[') && sizeValue.endsWith(']')) {
                             try {
                                let sizes = JSON.parse(sizeValue);
                                size = sizes.length > 0 ? sizes[0] : '';
                            } catch(e) { size = sizeValue; }
                        } else if (sizeValue !== '[]') {
                            size = sizeValue;
                        }
                        addToCart(currentProduct, variant, size);
                    } else {
                        toastr.error('This product has no variants available.');
                    }
                }
            }
        });
    };

    function showVariantModal(product) {
        $('#modal-product-name').text(product.name);
        let options = $('#variant-options');
        options.empty();

        product.variants.forEach((variant, index) => {
            let variantName = '';
            let color = variant.color || '';
            let sizeValue = variant.size || '';
            let sizes = [];
            
            // Check if size is a JSON array like ["52","3","4"]
            if (sizeValue && sizeValue.startsWith('[') && sizeValue.endsWith(']')) {
                try {
                    sizes = JSON.parse(sizeValue);
                } catch(e) {
                    sizes = [sizeValue];
                }
            } else if (sizeValue && sizeValue !== '[]') {
                sizes = [sizeValue];
            }

            let variantLabel = color || 'Standard';
            
            let sizeHtml = '';
            if (sizes.length > 1) {
                sizeHtml = `<div class="mt-2 d-flex flex-wrap gap-2 size-selection-container" style="display:none;">`;
                sizes.forEach((s, sIndex) => {
                    sizeHtml += `
                        <input type="radio" class="btn-check" name="size_selection_${variant.id}" id="size_${variant.id}_${sIndex}" value="${s}" ${sIndex === 0 ? 'checked' : ''}>
                        <label class="btn btn-sm btn-outline-secondary" for="size_${variant.id}_${sIndex}">${s}</label>
                    `;
                });
                sizeHtml += `</div>`;
            } else if (sizes.length === 1) {
                variantLabel += (color ? ' - ' : '') + sizes[0];
            }

            options.append(`
                <div class="mb-3 p-3 border rounded variant-option-container ${index === 0 ? 'border-primary bg-light' : ''}">
                    <div class="form-check cursor-pointer d-flex justify-content-between align-items-center" onclick="selectVariantRow(this, ${variant.id})">
                        <div class="d-flex align-items-center">
                            <input class="form-check-input me-2" type="radio" name="variant_selection" id="variant_${variant.id}" value="${variant.id}" ${index === 0 ? 'checked' : ''} ${variant.stock <= 0 ? 'disabled' : ''}>
                            <label class="form-check-label fw-bold" for="variant_${variant.id}">
                                ${variantLabel}
                            </label>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-primary d-block">${currencySymbol}${variant.price}</span>
                            <small class="${variant.stock > 0 ? 'text-success' : 'text-danger'} fw-medium">
                                Stock: ${variant.stock}
                            </small>
                        </div>
                    </div>
                    ${sizeHtml}
                </div>
            `);
        });

        $('#variantModal').modal('show');
    }

    window.selectVariantRow = function(element, variantId) {
        // Uncheck others, check this one
        $(element).closest('#variant-options').find('.variant-option-container').removeClass('border-primary bg-light');
        $(element).closest('.variant-option-container').addClass('border-primary bg-light');
        $(element).find('input[name="variant_selection"]').prop('checked', true);
        
        // Show/hide size containers
        $('.size-selection-container').hide();
        $(element).siblings('.size-selection-container').show();
    };

    // Auto-show first variant's sizes if any
    $('#variantModal').on('shown.bs.modal', function () {
        $('.variant-option-container.border-primary .size-selection-container').show();
    });

    $('#add-to-cart-confirm').click(function() {
        let variantId = $('input[name="variant_selection"]:checked').val();
        if (!variantId) {
            toastr.error('Please select a valid variant');
            return;
        }

        let variant = currentProduct.variants.find(v => v.id == variantId);
        
        if (variant.stock <= 0) {
            toastr.error('This variant is out of stock');
            return;
        }
        // Check for sub-size selection
        let selectedSize = '';
        let sizeInput = $(`input[name="size_selection_${variantId}"]:checked`);
        if (sizeInput.length) {
            selectedSize = sizeInput.val();
        } else {
            // If only one size or no sizes
            let sizeValue = variant.size || '';
            if (sizeValue && sizeValue.startsWith('[') && sizeValue.endsWith(']')) {
                 try {
                    let sizes = JSON.parse(sizeValue);
                    selectedSize = sizes.length > 0 ? sizes[0] : '';
                } catch(e) {
                    selectedSize = sizeValue;
                }
            } else if (sizeValue !== '[]') {
                selectedSize = sizeValue;
            }
        }

        addToCart(currentProduct, variant, selectedSize);
        $('#variantModal').modal('hide');
    });

    // Cart Management
    function addToCart(product, variant, selectedSize = '') {
        // Unique key for cart items: variant_id + selected_size
        let itemKey = variant.id + '_' + selectedSize;
        let existingItem = cart.find(item => (item.variant_id + '_' + (item.selected_size || '')) === itemKey);
        
        let color = variant.color || '';
        let variantName = color;
        if (selectedSize) {
            variantName += (variantName ? ' - ' : '') + selectedSize;
        }

        if (existingItem) {
            existingItem.qty += 1;
        } else {
            cart.push({
                product_id: product.id,
                variant_id: variant.id,
                selected_size: selectedSize,
                name: product.name,
                variant_name: variantName,
                price: parseFloat(variant.price),
                qty: 1
            });
        }

        renderCart();
        toastr.success('Item added to cart');
    }

    function renderCart() {
        let tbody = $('#cart-table tbody');
        tbody.empty();

        let subtotal = 0;

        cart.forEach((item, index) => {
            let itemTotal = item.price * item.qty;
            subtotal += itemTotal;

            tbody.append(`
                <tr>
                    <td>
                        <div class="fs-13 fw-medium text-truncate" style="max-width: 150px;">${item.name}</div>
                        <div class="text-muted fs-11">${item.variant_name}</div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary px-1" onclick="updateQty(${index}, -1)">-</button>
                            <input type="text" class="form-control text-center p-0" value="${item.qty}" readonly>
                            <button class="btn btn-outline-secondary px-1" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td class="text-end fw-bold">${currencySymbol}${itemTotal.toFixed(2)}</td>
                    <td class="text-end">
                        <button class="btn btn-link text-danger p-0" onclick="removeItem(${index})">
                            <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#cart-subtotal').text(currencySymbol + subtotal.toFixed(2));
        $('#cart-total').text(currencySymbol + subtotal.toFixed(2));
        $('#checkout-btn').prop('disabled', cart.length === 0);
    }

    window.updateQty = function(index, change) {
        cart[index].qty += change;
        if (cart[index].qty <= 0) {
            cart.splice(index, 1);
        }
        renderCart();
    };

    window.removeItem = function(index) {
        cart.splice(index, 1);
        renderCart();
    };

    $('#clear-cart').click(function() {
        if (cart.length > 0 && confirm('Clear all items from cart?')) {
            cart = [];
            renderCart();
        }
    });

    // Checkout
    $('#checkout-btn').click(function() {
        let paymentMethod = $('input[name="payment_method"]:checked').val();
        
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: BaseUrl + "/pos/place-order",
            type: "POST",
            global: false, // Stop global loading spinner
            data: {
                _token: window.csrf,
                cart: cart,
                payment_method: paymentMethod
            },
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Print Invoice',
                        cancelButtonText: 'New Order'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(BaseUrl + `/pos/invoice/${response.order_id}`, '_blank');
                        }
                        // Reset cart
                        cart = [];
                        renderCart();
                    });
                } else {
                    toastr.error(response.message);
                }
            },
            complete: function() {
                $('#checkout-btn').prop('disabled', false).html('<iconify-icon icon="solar:cart-check-linear" class="me-1"></iconify-icon> Complete Order');
            }
        });
    });

    // Initial Load
    loadProducts();

    // Filters
    $('#product-search').on('input', function() {
        loadProducts();
    });

    $('#category-filter').on('change', function() {
        loadProducts();
    });

    // Handle Pagination Clicks
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        let page = $(this).text();
        if ($(this).text() === 'Previous') {
            // Handled by onclick in HTML
        } else if ($(this).text() === 'Next') {
            // Handled by onclick in HTML
        } else {
            loadProducts(page);
        }
    });
});
</script>
@endpush
