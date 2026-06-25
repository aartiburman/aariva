/**
 * =====================================================
 * PRODUCT LIST SIDEBAR FILTERS
 * =====================================================
 * This file contains filter configuration and rendering
 * functions for the product list sidebar.
 */

// =====================================================
// PRODUCT LIST SIDEBAR FILTER CONFIGURATION
// =====================================================
const productListFilters = {
    categories: {
        type: 'checkbox',
        title: 'CATEGORIES',
        collapsible: true,
        items: [
            { id: 'wearable-smart', label: 'Wearable Smart Devices', parent: null, collapsed: false,
                children: [
                    { id: 'smart-watches', label: 'Smart Watches', parent: 'wearable-smart' }
                ]
            },
            { id: 'electronics', label: 'Electronics', parent: null, collapsed: true,
                children: [
                    { id: 'phones', label: 'Phones', parent: 'electronics' },
                    { id: 'tablets', label: 'Tablets', parent: 'electronics' }
                ]
            }
        ]
    },

    brand: {
        type: 'checkbox',
        title: 'BRAND',
        collapsible: true,
        items: [
            { id: 'apple', label: 'Apple', count: 45 },
            { id: 'samsung', label: 'Samsung', count: 38 },
            { id: 'sony', label: 'Sony', count: 22 },
            { id: 'lg', label: 'LG', count: 15 },
            { id: 'xiaomi', label: 'Xiaomi', count: 32 }
        ],
        searchable: true,
        showCount: true
    },

    discount: {
        type: 'checkbox',
        title: 'DISCOUNT',
        collapsible: true,
        items: [
            { id: 'discount-50', label: '50% or more', min: 50, max: 100 },
            { id: 'discount-30', label: '30% - 49%', min: 30, max: 49 },
            { id: 'discount-20', label: '20% - 29%', min: 20, max: 29 },
            { id: 'discount-10', label: '10% - 19%', min: 10, max: 19 }
        ]
    },

    price: {
        type: 'slider',
        title: 'PRICE',
        collapsible: false,
        min: 0,
        max: 100000,
        step: 100,
        currency: '₹',
        labels: ['Min', 'to', 'Max']
    },

    rating: {
        type: 'checkbox',
        title: 'CUSTOMER RATINGS',
        collapsible: true,
        items: [
            { id: 'rating-4', label: '4★ & above', stars: 4, count: 156 },
            { id: 'rating-3', label: '3★ & above', stars: 3, count: 298 },
            { id: 'rating-assured', label: 'Assured', icon: 'shield-check', count: 89 }
        ],
        showCount: true
    },

    offers: {
        type: 'checkbox',
        title: 'OFFERS',
        collapsible: true,
        items: [
            { id: 'offer-special', label: 'Special Price', icon: 'tag' },
            { id: 'offer-save', label: 'Buy More, Save More', icon: 'percent' },
            { id: 'offer-cashback', label: 'Cashback', icon: 'wallet' }
        ]
    },

    availability: {
        type: 'checkbox',
        title: 'AVAILABILITY',
        collapsible: true,
        items: [
            { id: 'in-stock', label: 'In Stock', count: 512, available: true },
            { id: 'out-stock', label: 'Out of Stock', count: 45, available: false }
        ],
        showCount: true
    }
};

/**
 * =====================================================
 * FILTER RENDERING FUNCTIONS
 * =====================================================
 */

/**
 * Render the entire sidebar with all filters
 * @param {Object} config - Filter configuration object
 * @param {string} containerId - ID of the container to render in
 */
function renderProductListSidebar(config, containerId = '#product-filters-sidebar') {
    const container = $(containerId);
    if (!container.length) {
        console.error('Sidebar container not found:', containerId);
        return;
    }

    container.html('');

    // Render each filter section
    Object.keys(config).forEach(filterKey => {
        const filter = config[filterKey];
        const filterSection = buildFilterSection(filterKey, filter);
        container.append(filterSection);
    });

    // Initialize event listeners
    initFilterEventListeners();
}

/**
 * Build individual filter section HTML
 * @param {string} key - Filter key identifier
 * @param {Object} filter - Filter configuration object
 */
function buildFilterSection(key, filter) {
    const sectionId = `filter-${key}`;
    let html = `
        <div class="filter-section" id="${sectionId}" data-filter="${key}">
            <div class="filter-header d-flex justify-content-between align-items-center ${filter.collapsible ? 'cursor-pointer' : ''}">
                <h6 class="filter-title mb-0">${filter.title}</h6>
    `;

    if (filter.collapsible) {
        html += `
                <iconify-icon icon="solar:alt-arrow-down-linear" class="filter-toggle" style="font-size: 18px;"></iconify-icon>
        `;
    }

    html += `
            </div>
            <div class="filter-content ${!filter.collapsible ? '' : 'collapsible'}">
    `;

    // Build based on filter type
    switch (filter.type) {
        case 'checkbox':
            html += buildCheckboxFilter(key, filter);
            break;
        case 'slider':
            html += buildSliderFilter(key, filter);
            break;
        case 'radio':
            html += buildRadioFilter(key, filter);
            break;
    }

    html += `
            </div>
        </div>
    `;

    return $(html);
}

/**
 * Build checkbox filter HTML
 * @param {string} key - Filter key
 * @param {Object} filter - Filter configuration
 */
function buildCheckboxFilter(key, filter) {
    let html = '';

    // Add search box if searchable
    if (filter.searchable) {
        html += `
            <div class="filter-search mb-2">
                <input type="text" class="form-control form-control-sm filter-search-input" 
                       placeholder="Search ${filter.title.toLowerCase()}..." 
                       data-filter="${key}">
            </div>
        `;
    }

    // Check if items have children (nested structure)
    const hasChildren = filter.items.some(item => item.children);

    html += '<div class="filter-items">';

    filter.items.forEach((item, index) => {
        const itemId = `${key}-${item.id}`;

        if (item.children) {
            // Nested category
            html += `
                <div class="filter-item-group mb-2">
                    <div class="form-check filter-checkbox">
                        <input class="form-check-input filter-item" type="checkbox" 
                               id="${itemId}" value="${item.id}" data-filter="${key}">
                        <label class="form-check-label d-flex justify-content-between w-100" for="${itemId}">
                            <span>${item.label}</span>
                            <iconify-icon icon="solar:alt-arrow-right-linear" class="toggle-children" style="font-size: 14px;"></iconify-icon>
                        </label>
                    </div>
                    <div class="filter-items-nested ps-3 ${item.collapsed ? 'd-none' : ''}">
            `;

            item.children.forEach(child => {
                const childId = `${key}-${child.id}`;
                html += `
                    <div class="form-check filter-checkbox">
                        <input class="form-check-input filter-item" type="checkbox" 
                               id="${childId}" value="${child.id}" data-filter="${key}">
                        <label class="form-check-label" for="${childId}">${child.label}</label>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        } else {
            // Regular checkbox item
            html += `
                <div class="form-check filter-checkbox">
                    <input class="form-check-input filter-item" type="checkbox" 
                           id="${itemId}" value="${item.id}" data-filter="${key}">
                    <label class="form-check-label d-flex justify-content-between w-100" for="${itemId}">
                        <span>
            `;

            // Add icon if present
            if (item.icon) {
                html += `<iconify-icon icon="solar:${item.icon}-linear" style="margin-right: 8px;"></iconify-icon>`;
            }

            // Add rating stars if present
            if (item.stars) {
                html += `<span class="rating-stars">`;
                for (let i = 0; i < item.stars; i++) {
                    html += `<iconify-icon icon="solar:star-bold" class="text-warning" style="font-size: 12px;"></iconify-icon>`;
                }
                html += `</span>`;
            }

            html += `${item.label}</span>`;

            // Add count if available
            if (filter.showCount && item.count) {
                html += `<small class="text-muted">(${item.count})</small>`;
            }

            html += `
                    </label>
                </div>
            `;
        }
    });

    html += '</div>';

    return html;
}

/**
 * Build price slider filter HTML
 * @param {string} key - Filter key
 * @param {Object} filter - Filter configuration
 */
function buildSliderFilter(key, filter) {
    const html = `
        <div class="price-slider mb-3">
            <div class="range-slider-wrapper">
                <div class="range-track"></div>
                <input type="range" min="${filter.min}" max="${filter.max}" value="${filter.min}" 
                       class="range-input range-min" data-filter="${key}">
                <input type="range" min="${filter.min}" max="${filter.max}" value="${filter.max}" 
                       class="range-input range-max" data-filter="${key}">
            </div>

            <div class="price-inputs mt-3 d-flex align-items-center gap-2">
                <select class="form-select form-select-sm" style="max-width: 70px;">
                    <option>${filter.labels[0]}</option>
                </select>
                <input type="number" class="form-control form-control-sm price-min" 
                       min="${filter.min}" max="${filter.max}" value="${filter.min}" placeholder="Min">
                <span class="text-muted">${filter.labels[1]}</span>
                <input type="number" class="form-control form-control-sm price-max" 
                       min="${filter.min}" max="${filter.max}" value="${filter.max}" placeholder="Max">
                <select class="form-select form-select-sm" style="max-width: 70px;">
                    <option>${filter.labels[2]}</option>
                </select>
            </div>
        </div>
    `;

    return html;
}

/**
 * Build radio filter HTML
 * @param {string} key - Filter key
 * @param {Object} filter - Filter configuration
 */
function buildRadioFilter(key, filter) {
    let html = '<div class="filter-items">';

    filter.items.forEach(item => {
        const itemId = `${key}-${item.id}`;
        html += `
            <div class="form-check filter-radio">
                <input class="form-check-input filter-item" type="radio" 
                       name="${key}" id="${itemId}" value="${item.id}" data-filter="${key}">
                <label class="form-check-label" for="${itemId}">${item.label}</label>
            </div>
        `;
    });

    html += '</div>';
    return html;
}

/**
 * =====================================================
 * EVENT LISTENERS & FILTERING LOGIC
 * =====================================================
 */

/**
 * Initialize all filter event listeners
 */
function initFilterEventListeners() {
    // Collapsible filter sections
    $(document).off('click', '.filter-header.cursor-pointer').on('click', '.filter-header.cursor-pointer', function() {
        const content = $(this).next('.filter-content');
        const icon = $(this).find('.filter-toggle');

        content.slideToggle(300, function() {
            icon.attr('icon', content.is(':visible') 
                ? 'solar:alt-arrow-down-linear' 
                : 'solar:alt-arrow-right-linear');
        });
    });

    // Filter item changes (checkbox/radio)
    $(document).off('change', '.filter-item').on('change', '.filter-item', function() {
        const selectedFilters = getSelectedFilters();
        applyFilters(selectedFilters);
    });

    // Price range inputs
    $(document).off('input', '.range-input').on('input', '.range-input', function() {
        updatePriceRange();
    });

    $(document).off('change', '.price-min, .price-max').on('change', '.price-min, .price-max', function() {
        const minVal = parseInt($('.price-min').val()) || 0;
        const maxVal = parseInt($('.price-max').val()) || 100000;
        
        if (minVal <= maxVal) {
            applyFilters(getSelectedFilters());
        }
    });

    // Filter search functionality
    $(document).off('keyup', '.filter-search-input').on('keyup', '.filter-search-input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const filterKey = $(this).data('filter');
        const filterSection = $(`#filter-${filterKey}`);

        filterSection.find('.filter-item').each(function() {
            const label = $(this).next('label').text().toLowerCase();
            const itemGroup = $(this).closest('.form-check');
            
            if (label.includes(searchTerm)) {
                itemGroup.show();
            } else {
                itemGroup.hide();
            }
        });
    });

    // Nested category toggle
    $(document).off('click', '.toggle-children').on('click', '.toggle-children', function(e) {
        e.preventDefault();
        const nestedItems = $(this).closest('.filter-item-group').find('.filter-items-nested');
        nestedItems.slideToggle(200);
        $(this).toggleClass('rotate-90');
    });
}

/**
 * Get all selected filters
 * @returns {Object} Selected filter values
 */
function getSelectedFilters() {
    const filters = {};

    // Get checked items for each filter
    Object.keys(productListFilters).forEach(filterKey => {
        const filterSection = $(`#filter-${filterKey}`);
        const checked = filterSection.find('.filter-item:checked');

        if (checked.length > 0) {
            filters[filterKey] = checked.map(function() {
                return $(this).val();
            }).get();
        }
    });

    // Get price range
    const minPrice = parseInt($('.price-min').val()) || 0;
    const maxPrice = parseInt($('.price-max').val()) || 100000;
    if (minPrice > 0 || maxPrice < 100000) {
        filters.price = {
            min: minPrice,
            max: maxPrice
        };
    }

    return filters;
}

/**
 * Update price range display
 */
function updatePriceRange() {
    const rangeMin = $('.range-min');
    const rangeMax = $('.range-max');
    const priceMin = $('.price-min');
    const priceMax = $('.price-max');

    let min = parseInt(rangeMin.val());
    let max = parseInt(rangeMax.val());

    if (min > max) {
        [min, max] = [max, min];
        rangeMin.val(min);
        rangeMax.val(max);
    }

    priceMin.val(min);
    priceMax.val(max);

    // Update track position (CSS can handle this with custom properties)
    const range = rangeMax.attr('max') - rangeMax.attr('min');
    const newMinPercent = (min - rangeMax.attr('min')) / range * 100;
    const newMaxPercent = 100 - (max - rangeMax.attr('min')) / range * 100;

    $('.range-track').css({
        'left': newMinPercent + '%',
        'right': newMaxPercent + '%'
    });
}

/**
 * Apply filters and update product list
 * @param {Object} filters - Selected filter values
 */
function applyFilters(filters) {
    console.log('Applying filters:', filters);

    // Send AJAX request or update product list
    const data = {
        filters: JSON.stringify(filters),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: window.BaseUrl + '/filter-products',
        type: 'POST',
        data: data,
        global: false,
        success: function(response) {
            if (response.status) {
                updateProductList(response.products);
                updateFilterCounts(response.counts);
            } else {
                console.error('Filter error:', response.message);
            }
        },
        error: function(xhr) {
            console.error('AJAX error:', xhr);
        }
    });
}

/**
 * Update product list display
 * @param {Array} products - Array of product objects
 */
function updateProductList(products) {
    const container = $('#products-container');
    
    if (!container.length) return;

    if (products.length === 0) {
        container.html('<div class="alert alert-info">No products found matching your filters.</div>');
        return;
    }

    let html = '';
    products.forEach(product => {
        html += `
            <div class="product-card">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="product-info">
                    <h6>${product.name}</h6>
                    <p class="price">${product.price}</p>
                </div>
            </div>
        `;
    });

    container.html(html);
}

/**
 * Update filter item counts based on available products
 * @param {Object} counts - Count data for each filter option
 */
function updateFilterCounts(counts) {
    Object.keys(counts).forEach(filterKey => {
        const filterCount = counts[filterKey];
        Object.keys(filterCount).forEach(itemId => {
            const count = filterCount[itemId];
            const element = $(`[value="${itemId}"]`).next('label').find('small');
            if (element.length) {
                element.text(`(${count})`);
            }
        });
    });
}

/**
 * Reset all filters
 */
function resetAllFilters() {
    $('[id^="filter-"] .filter-item').prop('checked', false);
    $('.price-min').val(productListFilters.price.min);
    $('.price-max').val(productListFilters.price.max);
    updatePriceRange();
    applyFilters({});
}

/**
 * Export for use in other modules
 */
window.productListFilters = productListFilters;
window.renderProductListSidebar = renderProductListSidebar;
window.getSelectedFilters = getSelectedFilters;
window.applyFilters = applyFilters;
window.resetAllFilters = resetAllFilters;
