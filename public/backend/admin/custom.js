// add product wizard form
let currentStep = 1;

function showStep(step) {
    $(".step").removeClass("active");
    $("#step-" + step).addClass("active");

    $(".step-indicator span").removeClass("active");
    $('.step-indicator span[data-step="' + step + '"]').addClass("active");
}

$(".next-step").click(function () {
    if (currentStep < 3) {
        currentStep++;
        showStep(currentStep);
    }
});

$(".prev-step").click(function () {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
});

// name slug uniq
$(document).on("input", '[name="name"]', function () {
    const $nameField = $(this);
    const $form = $nameField.closest('form');
    const $slugField = $form.find('[name="slug"]');

    if ($slugField.length) {
        $slugField.val(
            $nameField.val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/^-+|-+$/g, "")
        );
    }
});

/* ==============================================
   GENERIC DATE RANGE PICKER
   ============================================== */
function initDateRangePicker(selector) {
    if (typeof $.fn.daterangepicker === 'undefined') return;

    $(selector).daterangepicker({
        autoUpdateInput: false,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 3 Months': [moment().subtract(3, 'month'), moment()],
            'Last 6 Months': [moment().subtract(6, 'month'), moment()],
            'This Year': [moment().startOf('year'), moment()],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        },
        locale: {
            format: 'Y-MM-DD',
            cancelLabel: 'Clear'
        }
    });

    $(selector).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        $(this).closest('form').submit();
    });

    $(selector).on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
        $(this).closest('form').submit();
    });
}

/* ==============================================
   VENDOR LIST SPECIFIC INIT
   ============================================== */
function initVendorList() {
    initDateRangePicker('.range-datepicker');

    const form = $('#vendor-filter-form');
    let debounceTimer;

    // Debounce search
    $('#vendor-search').on('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            form.submit();
        }, 3000);
    });

    // AJAX Filter Submit
    form.on('submit', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();
        let url = $(this).attr('action');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function (response) {
                // Get current active tab
                let activeTab = $('.nav-pills .nav-link.active').attr('href');

                $('#vendor-tabs-container').html(response);

                // Restore active tab content
                if (activeTab) {
                    let targetPaneId = activeTab.replace('#', '');
                    $('#vendor-tabs-container .tab-pane').removeClass('show active');
                    $('#vendor-tabs-container #' + targetPaneId).addClass('show active');
                }

                // Update count text
                let rowCount = $(response).find(activeTab + ' tbody tr').length;
                if ($(response).find(activeTab + ' tbody tr td').attr('colspan')) {
                    rowCount = 0;
                }

                if (activeTab === '#all-vendors') {
                    $('.card-footer p').text('Showing all ' + rowCount + ' vendors');
                } else if (activeTab === '#pending-approval') {
                    $('.card-footer p').text('Showing ' + rowCount + ' pending vendors');
                } else if (activeTab === '#active') {
                    $('.card-footer p').text('Showing ' + rowCount + ' active vendors');
                } else if (activeTab === '#rejected') {
                    $('.card-footer p').text('Showing ' + rowCount + ' rejected vendors');
                }

                // Re-init tooltips
                if (typeof $.fn.tooltip !== 'undefined') {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                toastr.error('Error filtering data');
            }
        });
    });

    // Handle pagination inside tabs via AJAX (preserve active tab)
    $(document)
        .off('click.vendorPaginate', '#vendor-tabs-container .pagination a')
        .on('click.vendorPaginate', '#vendor-tabs-container .pagination a', function (e) {
            e.preventDefault();
            const href = $(this).attr('href');
            if (!href) return;

            const activeTab = $('.nav-pills .nav-link.active').attr('href');

            $.ajax({
                url: href,
                type: 'GET',
                global: false, // Disable global AJAX loader for pagination
                success: function (response) {
                    $('#vendor-tabs-container').html(response);

                    if (activeTab) {
                        let targetPaneId = activeTab.replace('#', '');
                        $('#vendor-tabs-container .tab-pane').removeClass('show active');
                        $('#vendor-tabs-container #' + targetPaneId).addClass('show active');
                    }

                    if (typeof $.fn.tooltip !== 'undefined') {
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                },
                error: function () {
                    toastr.error('Failed to load page');
                }
            });
        });
}

// my multipe select add product
let variantIndex = 1;
window.choicesMap = new Map();

/* -----------------------------
   UNIVERSAL DYNAMIC CREATOR
   ----------------------------- */
window.saveDynamicItem = function (el, itemName, choicesInstance) {
    if (!itemName || !itemName.trim()) return;

    const type = el.getAttribute('data-type') || 'brand';
    const $container = $(el).closest('form, .card-body, .variant-block, .fetch-variant-section, .row');

    let url = '';
    let data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: itemName.trim()
    };

    // Determine URL and extra dependencies based on type
    if (type === 'size') {
        url = BaseUrl + '/create-size-ajax';
        data.category_id = $container.find('.UpdateSizeCategory, .similerSelectSizeCategory, .SelectSizeCategory, select[name^="size_category_id"], select[name^="size_cat_id"]').val();
        if (!data.category_id) {
            toastr.error('Please select Size Category first.');
            choicesInstance.removeActiveItemsByValue(itemName);
            return;
        }
    } else if (type === 'brand') {
        url = BaseUrl + '/create-brand';
        data.category_id = $container.find('.category_id').val();
        data.subcategory_id = $container.find('.subcategory_id').val();
        data.child_category_id = $container.find('.child_category_id').val();
    } else if (type === 'category') {
        url = BaseUrl + '/create-category-ajax';
    } else if (type === 'subcategory') {
        url = BaseUrl + '/create-subcategory-ajax';
        data.category_id = $container.find('.category_id').val();
        if (!data.category_id) {
            toastr.error('Please select Category first.');
            choicesInstance.removeActiveItemsByValue(itemName);
            return;
        }
    } else if (type === 'childcategory') {
        url = BaseUrl + '/create-child-category-ajax';
        data.category_id = $container.find('.category_id').val();
        data.subcategory_id = $container.find('.subcategory_id').val();
        if (!data.category_id || !data.subcategory_id) {
            toastr.error('Please select Category and Sub Category first.');
            choicesInstance.removeActiveItemsByValue(itemName);
            return;
        }
    } else if (type === 'product_variant') {
        url = BaseUrl + '/create-product-variant-ajax';
    } else if (type === 'size_category') {
        url = BaseUrl + '/create-size-category-ajax';
    }

    $.ajax({
        global: false,
        url: url,
        type: 'POST',
        data: data,
        success: function (response) {
            if (response.success) {
                toastr.success(response.message);
                choicesInstance.removeActiveItemsByValue(itemName);

                const newItem = response[type] || response.size || response.brand || response.category || response.subcategory || response.child || response.variant;

                if (newItem && newItem.id) {
                    choicesInstance.setChoices([{
                        value: String(newItem.id),
                        label: newItem.name,
                        selected: true
                    }], 'value', 'label', false);
                }

                $(el).trigger('change');
            } else {
                toastr.error(response.message || `Failed to create ${type}.`);
                choicesInstance.removeActiveItemsByValue(itemName);
            }
        },
        error: function () {
            toastr.error(`Error creating ${type}`);
            choicesInstance.removeActiveItemsByValue(itemName);
        }
    });
};

/* Helper: Initialize Choices.js */
// window.initChoices = function (el) {

//     if (!el) return;

//     if (window.choicesMap.has(el)) {
//         window.destroyChoices(el);
//     }

//     const isDynamic   = el.classList.contains('dynamic-create');
//     const type        = el.getAttribute('data-type') || 'size';
//     const placeholder = el.getAttribute('data-placeholder') || `Select ${type}`;

//     let currentSearchValue = '';

//     /* ================= CONFIG ================= */
//     const instance = new Choices(el, {
//         removeItemButton: true,
//         shouldSort: false,
//         placeholder: true,
//         placeholderValue: placeholder,
//         searchEnabled: true,
//         searchChoices: true,

//         addItems: true,
//         addChoices: true,
//         duplicateItemsAllowed: false,

//         maxItemCount: -1,
//         searchFloor: 1,
//         itemSelectText: '',
//         renderSelectedChoices: 'always',

//         addItemFilter: (value) => value && value.trim() !== '',

//         addItemText: (value) => {
//             return `Press Enter to create ${type} "<b>${value}</b>"`;
//         },

//         noResultsText: (value) => {
//             return `No results found. Press Enter to create <b>"${value}"</b>`;
//         },
//         noChoicesText: `No ${type} available`,
//     });

//     window.choicesMap.set(el, instance);
//     el.classList.add("choices-initialized");

//     /* ================= SEARCH VALUE ================= */
//     el.addEventListener('search', function (event) {
//         currentSearchValue = event.detail.value;
//     });

//     /* ================= CREATE FUNCTION ================= */
//     const createNewItem = function (value) {

//         if (!value || !value.trim()) return;

//         const cleanValue = value.trim();

//         // ✅ Step 1: UI me turant add karo
//         instance.setChoices([{
//             value: cleanValue,
//             label: cleanValue,
//             selected: true
//         }], 'value', 'label', false);

//         // ✅ Step 2: DB me save karo
//         const $container = $(el).closest('form, .card-body, .variant-block, .row');

//         let url = BaseUrl + '/create-size-ajax';

//         let sizeCatId = $container.find(
//             '.UpdateSizeCategory, .similerSelectSizeCategory, .SelectSizeCategory, select[name^="size_category_id"], select[name^="size_cat_id"]'
//         ).val();

//         if (!sizeCatId) {
//             toastr.error('Please select Size Category first.');
//             instance.removeActiveItemsByValue(cleanValue);
//             return;
//         }

//         $.ajax({
//             url: url,
//             type: 'POST',
//             data: {
//                 _token: $('meta[name="csrf-token"]').attr('content'),
//                 name: cleanValue,
//                 category_id: sizeCatId
//             },
//             success: function (response) {

//                 if (response.success) {

//                     // ✅ Step 3: temp value remove
//                     instance.removeActiveItemsByValue(cleanValue);

//                     // ✅ Step 4: DB value add (ID ke sath)
//                     instance.setChoices([{
//                         value: String(response.size.id),
//                         label: response.size.name,
//                         selected: true
//                     }], 'value', 'label', false);

//                     toastr.success(response.message);

//                 } else {
//                     toastr.error(response.message || 'Failed to save');
//                     instance.removeActiveItemsByValue(cleanValue);
//                 }
//             },
//             error: function () {
//                 toastr.error('Error saving');
//                 instance.removeActiveItemsByValue(cleanValue);
//             }
//         });
//     };

//     /* ================= ENTER KEY ================= */
//     $(el).closest('.choices').on('keydown', '.choices__input', function (e) {
//         if (e.key === 'Enter') {
//             const value = this.value.trim();
//             if (value) {
//                 e.preventDefault();
//                 e.stopPropagation();
                
//                 const existing = instance.getValue(true);
//                 let already = false;

//                 if (Array.isArray(existing)) {
//                     already = existing.includes(value);
//                 } else {
//                     already = existing === value;
//                 }

//                 if (!already) {
//                     createNewItem(value);
//                     instance.hideDropdown();
//                     this.value = ''; // Clear input after creation
//                 }
//             }
//         }
//     });

//     /* ================= ON SELECT ================= */
//     el.addEventListener('addItem', function (event) {

//         const val = event.detail.value;

//         if (val && typeof val === 'string' && isNaN(val)) {
//             createNewItem(val);
//         }

//     });
// };

window.initSizeChoices = function (el) {

    if (!el) return;

    if (window.choicesMap?.has(el)) return;

    let currentValue = '';

    const instance = new Choices(el, {
        removeItemButton: true,
        shouldSort: false,
        placeholder: true,
        placeholderValue: 'Select sizes',
        searchEnabled: true,

        addItems: true,
        addChoices: true,
        duplicateItemsAllowed: false,

        renderSelectedChoices: 'always',
        itemSelectText: '',
    });

    window.choicesMap = window.choicesMap || new Map();
    window.choicesMap.set(el, instance);

    /* ========= CAPTURE INPUT ========= */
    el.addEventListener('search', function (e) {
        currentValue = e.detail.value;
    });

    /* ========= CREATE + SAVE ========= */
    function createSize(value) {

        value = value.trim();
        if (!value) return;

        // ✅ 1. UI me turant add
        instance.setChoices([{
            value: value,
            label: value,
            selected: true
        }], 'value', 'label', false);

        // ✅ 2. AJAX save
        $.ajax({
            url: BaseUrl + '/create-size-ajax',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: value
            },
            success: function (res) {

                if (res.success) {

                    // ✅ 3. temp remove
                    instance.removeActiveItemsByValue(value);

                    // ✅ 4. DB value add
                    instance.setChoices([{
                        value: String(res.size.id),
                        label: res.size.name,
                        selected: true
                    }], 'value', 'label', false);

                    toastr.success(res.message);

                } else {
                    toastr.error(res.message || 'Failed');
                    instance.removeActiveItemsByValue(value);
                }
            },
            error: function () {
                toastr.error('Error saving');
                instance.removeActiveItemsByValue(value);
            }
        });
    }

    /* ========= ENTER KEY ========= */
    $(el).closest('.choices').on('keydown', '.choices__input', function (e) {

        if (e.key === 'Enter' && currentValue) {

            e.preventDefault();

            let values = instance.getValue(true);

            if (!Array.isArray(values)) values = [values];

            if (!values.includes(currentValue)) {
                createSize(currentValue);
                instance.hideDropdown();
            }
        }
    });

    /* ========= FALLBACK ========= */
    el.addEventListener('addItem', function (e) {
        let val = e.detail.value;

        if (val && isNaN(val)) {
            createSize(val);
        }
    });
};
/* Helper: Destroy Choices.js */
window.destroyChoices = function (selectElement) {
    if (window.choicesMap.has(selectElement)) {
        window.choicesMap.get(selectElement).destroy();
        window.choicesMap.delete(selectElement);
        selectElement.classList.remove("choices-initialized");
    }
}

/* Helper: Initialize Dynamic Choices (Brand, Category, etc.) */
window.initDynamicChoices = function(el) {
    if (!el || el.classList.contains("choices-initialized")) return;

    const type = el.getAttribute('data-type') || 'brand';
    const placeholder = el.getAttribute('data-placeholder') || `Create or select ${type}`;

    const choicesInstance = new Choices(el, {
        removeItemButton: false, 
        shouldSort: false,
        placeholder: true,
        placeholderValue: placeholder,
        searchEnabled: true,
        itemSelectText: '',
        addItems: true,
        maxItemCount: -1, 
        searchFloor: 1,
        renderSelectedChoices: 'always',
        renderChoiceLimit: -1,
        searchChoices: true,
        addItemText: (value) => {
            return `Press Enter to create ${type} <b>"${value}"</b>`;
        },
        noResultsText: `No existing ${type} found. Create or select another...`,
        noChoicesText: `No ${type} available`,
    });
    
    window.choicesMap.set(el, choicesInstance);
    el.classList.add("choices-initialized");

    // Pre-selection for edit page
    const selectedVal = el.getAttribute('data-selected');
    if (selectedVal) {
        choicesInstance.setChoiceByValue(String(selectedVal));
    }

    // Custom creation logic
    let currentSearchValue = '';
    el.addEventListener('search', function(event) {
        currentSearchValue = event.detail.value;
    }, false);

    const createNewItem = function(itemName) {
        if (!itemName || !itemName.trim()) return;
        const $container = $(el).closest('form, .card-body');
        const categoryId = $container.find('.category_id').val();
        const subcategoryId = $container.find('.subcategory_id').val();
        const childCategoryId = $container.find('.child_category_id').val();

        let url = '';
        let data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: itemName.trim()
        };

        if (type === 'brand') {
            url = BaseUrl + '/create-brand';
            data.category_id = categoryId;
            data.subcategory_id = subcategoryId;
            data.child_category_id = childCategoryId;
        } else if (type === 'category') {
            url = BaseUrl + '/create-category-ajax';
        } else if (type === 'subcategory') {
            url = BaseUrl + '/create-subcategory-ajax';
            data.category_id = categoryId;
            if (!categoryId) {
                toastr.error('Please select a Category first.');
                choicesInstance.removeActiveItemsByValue(itemName);
                return;
            }
        } else if (type === 'childcategory') {
            url = BaseUrl + '/create-child-category-ajax';
            data.category_id = categoryId;
            data.subcategory_id = subcategoryId;
            if (!categoryId || !subcategoryId) {
                toastr.error('Please select Category and Sub Category first.');
                choicesInstance.removeActiveItemsByValue(itemName);
                return;
            }
        }

        $.ajax({
            global: false,
            url: url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    choicesInstance.removeActiveItemsByValue(itemName);
                    
                    const newItem = response[type] || response.brand || response.category || response.subcategory || response.child;
                    
                    choicesInstance.setChoices([{ 
                        value: String(newItem.id), 
                        label: newItem.name, 
                        selected: true 
                    }], 'value', 'label', false); 
                    
                    $(el).trigger('change');
                } else {
                    toastr.error(response.message || `Failed to create ${type}.`);
                    choicesInstance.removeActiveItemsByValue(itemName);
                }
            },
            error: function(xhr) {
                toastr.error(`Error creating ${type}`);
                choicesInstance.removeActiveItemsByValue(itemName);
            }
        });
    };

    el.addEventListener('addItem', function(event) {
        const newValue = event.detail.value;
        if (newValue && isNaN(newValue)) {
            createNewItem(newValue);
        }
    }, false);

    $(el).closest('.choices').on('keydown', '.choices__input', function(e) {
        if (e.keyCode === 13 && currentSearchValue) {
            e.preventDefault();
            const activeItems = choicesInstance.getValue(true);
            let isAlreadySelected = false;
            if (Array.isArray(activeItems)) {
                isAlreadySelected = activeItems.includes(currentSearchValue);
            } else if (activeItems) {
                isAlreadySelected = (activeItems === currentSearchValue);
            }
            if (!isAlreadySelected) {
                createNewItem(currentSearchValue);
                choicesInstance.hideDropdown();
            }
        }
    });
}

// Initialize everything as soon as possible
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".size-select, .js-size-select").forEach(el => window.initChoices(el));
    document.querySelectorAll('.dynamic-create[data-allow-new="true"], .categoryBrand[data-allow-new="true"]').forEach(el => window.initDynamicChoices(el));
});

// CATEGORY → SUB CATEGORY
$(document).off("change", ".category_id").on("change", ".category_id", function (e, is_initial) {
    let $container = $(this).closest('form, .card-body');
    let categoryId = $(this).val();
    let subCategorySelect = $container.find(".subcategory_id");
    let childCategorySelect = $container.find(".child_category_id");
    let brandSelect = $container.find(".categoryBrand");  
    

    // Reset all dependent dropdowns immediately unless it's the initial load
    if (!is_initial) {
        const resetChoices = (el, placeholder) => {
            const instance = window.choicesMap.get(el);
            if (instance) {
                instance.clearChoices();
                instance.setChoices([{ value: '', label: placeholder, placeholder: true }], 'value', 'label', true);
            } else {
                $(el).html(`<option value="">${placeholder}</option>`);
            }
        };

        resetChoices(subCategorySelect[0], '-- Select Sub Category --');
        resetChoices(childCategorySelect[0], '-- Select Child Category --');
        resetChoices(brandSelect[0], '-- Select Brand --');
    }

    if (categoryId) {
        // Prevent multiple simultaneous calls
        if (subCategorySelect.data('loading')) return;
        subCategorySelect.data('loading', true);
        
        subCategorySelect.html('<option value="">Loading...</option>');
        
        // 1. Fetch Subcategories
        $.ajax({
            global: false,
            url: BaseUrl + "/get-subcategories/" + categoryId,
            type: "GET",
            success: function (response) {
                subCategorySelect.data('loading', false);
                const choicesInstance = window.choicesMap.get(subCategorySelect[0]);
                if (choicesInstance) {
                    const subChoices = response.map(sub => ({
                        value: String(sub.id),
                        label: sub.name,
                        selected: false
                    }));
                    choicesInstance.clearChoices();
                    choicesInstance.setChoices([{ value: '', label: 'Select Sub Category', placeholder: true }].concat(subChoices), 'value', 'label', true);
                } else {
                    subCategorySelect.empty().append('<option value="">-- Select Sub Category --</option>');
                    $.each(response, function (key, subcategory) {
                        subCategorySelect.append(`<option value="${subcategory.id}">${subcategory.name}</option>`);
                    });
                }

                // Auto-trigger preselection if editing
                let preselectedSub = subCategorySelect.data('selected');
                if (preselectedSub) {
                    if (choicesInstance) {
                        choicesInstance.setChoiceByValue(String(preselectedSub));
                    } else {
                        subCategorySelect.val(preselectedSub).trigger('change', [true]);
                    }
                    subCategorySelect.data('selected', ''); // Clear to prevent loops
                }
            },
            error: function () {
                subCategorySelect.data('loading', false);
                subCategorySelect.html('<option value="">No subcategories found</option>');
            }
        });

        // 2. Fetch Brands for this Category
        $.ajax({
            global: false,
            url: BaseUrl + "/get-brands-by-category/" + categoryId,
            type: "GET",
            success: function (response) {
                const choicesInstance = window.choicesMap.get(brandSelect[0]);
                if (choicesInstance) {
                    const brandChoices = response.map(brand => ({
                        value: String(brand.id),
                        label: brand.name,
                        selected: false
                    }));
                    choicesInstance.clearChoices();
                    choicesInstance.setChoices([{ value: '', label: 'Select Brand', placeholder: true }].concat(brandChoices), 'value', 'label', true);
                } else {
                    brandSelect.empty().append('<option value="">Select Brand</option>');
                    $.each(response, function (key, brand) {
                        brandSelect.append(`<option value="${brand.id}">${brand.name}</option>`);
                    });
                }
            }
        });
    }
});

// SUB CATEGORY → CHILD CATEGORY
$(document).off("change", ".subcategory_id").on("change", ".subcategory_id", function (e, is_initial) {
    let $container = $(this).closest('form, .card-body');
    let subCategoryId = $(this).val();
    let childCategorySelect = $container.find(".child_category_id");
    let brandSelect = $container.find(".categoryBrand");

    // Reset Child Category immediately unless it's the initial load
    if (!is_initial) {
        const resetChoices = (el, placeholder) => {
            const instance = window.choicesMap.get(el);
            if (instance) {
                instance.clearChoices();
                instance.setChoices([{ value: '', label: placeholder, placeholder: true }], 'value', 'label', true);
            } else {
                $(el).html(`<option value="">${placeholder}</option>`);
            }
        };
        resetChoices(childCategorySelect[0], '-- Select Child Category --');
    }

    if (subCategoryId) {
        if (childCategorySelect.data('loading')) return;
        childCategorySelect.data('loading', true);
        
        childCategorySelect.html('<option value="">Loading...</option>');

        // 1. Fetch Child Categories
        $.ajax({
            global: false,
            url: BaseUrl + "/get-child-categories/" + subCategoryId,
            type: "GET",
            success: function (response) {
                childCategorySelect.data('loading', false);
                const choicesInstance = window.choicesMap.get(childCategorySelect[0]);
                if (choicesInstance) {
                    const childChoices = response.map(child => ({
                        value: String(child.id),
                        label: child.name,
                        selected: false
                    }));
                    choicesInstance.clearChoices();
                    choicesInstance.setChoices([{ value: '', label: 'Select Child Category', placeholder: true }].concat(childChoices), 'value', 'label', true);
                } else {
                    childCategorySelect.empty().append('<option value="">-- Select Child Category --</option>');
                    $.each(response, function (key, child) {
                        childCategorySelect.append(`<option value="${child.id}">${child.name}</option>`);
                    });
                }

                let preselectedChild = childCategorySelect.data('selected');
                if (preselectedChild) {
                    if (choicesInstance) {
                        choicesInstance.setChoiceByValue(String(preselectedChild));
                    } else {
                        childCategorySelect.val(preselectedChild).trigger('change', [true]);
                    }
                    childCategorySelect.data('selected', '');
                }
            },
            error: function () {
                childCategorySelect.data('loading', false);
                childCategorySelect.html('<option value="">No child categories found</option>');
            }
        });

        // 2. Fetch Brands for Subcategory (Optional: only if you want to filter brands further)
        $.ajax({
            global: false,
            url: BaseUrl + "/get-brands-by-subcategory/" + subCategoryId,
            type: "GET",
            success: function (response) {
                const choicesInstance = window.choicesMap.get(brandSelect[0]);
                if (choicesInstance) {
                    const brandChoices = response.map(brand => ({
                        value: String(brand.id),
                        label: brand.name,
                        selected: false
                    }));
                    choicesInstance.clearChoices();
                    choicesInstance.setChoices([{ value: '', label: 'Select Brand', placeholder: true }].concat(brandChoices), 'value', 'label', true);
                    
                    let preselectedBrand = brandSelect.data('selected');
                    if (preselectedBrand) {
                        choicesInstance.setChoiceByValue(String(preselectedBrand));
                        brandSelect.data('selected', '');
                    }
                } else {
                    if (response.length > 0) {
                        brandSelect.empty().append('<option value="">Select Brand</option>');
                        $.each(response, function (key, brand) {
                            brandSelect.append(`<option value="${brand.id}">${brand.name}</option>`);
                        });
                        
                        let preselectedBrand = brandSelect.data('selected');
                        if (preselectedBrand) {
                            brandSelect.val(preselectedBrand);
                            brandSelect.data('selected', '');
                        }
                    }
                }
            }
        });
    }
});


// On page load, auto-fetch sub/child categories for Edit Brand
$(document).ready(function () {
    var $cat = $('.category_id');
    if ($cat.length && $cat.val()) {
        $cat.trigger('change', [true]);
    }
});

// child category -> brands
$(document).off("change", ".child_category_id").on("change", ".child_category_id", function (e, is_initial) {
    let $container = $(this).closest('form, .card-body');
    let ChildCategoryId = $(this).val();
    let brandSelect = $container.find(".categoryBrand");
    let childCategorySelect = $(this); // Use the element itself

    if (!is_initial) {
        // brandSelect.html('<option value="">Loading...</option>');
    }

    if (ChildCategoryId) {
        if (brandSelect.data('loading')) return;
        brandSelect.data('loading', true);

        $.ajax({
            global: false,
            url: BaseUrl + "/get-brands-by-childcategory/" + ChildCategoryId,
            type: "GET",
            success: function (response) {
                brandSelect.data('loading', false);
                const choicesInstance = window.choicesMap.get(brandSelect[0]);
                
                if (choicesInstance) {
                    const brandChoices = response.map(brand => ({
                        value: String(brand.id),
                        label: brand.name,
                        selected: false
                    }));
                    choicesInstance.clearChoices();
                    choicesInstance.setChoices([{ value: '', label: 'Select Brand', placeholder: true }].concat(brandChoices), 'value', 'label', true);
                    
                    var preselectedBrand = brandSelect.data('selected');
                    if (preselectedBrand) {
                        choicesInstance.setChoiceByValue(String(preselectedBrand));
                        brandSelect.data('selected', '');
                    }
                } else {
                    brandSelect.empty().append(
                        '<option value="">Select Brand</option>'
                    );

                    if (response.length > 0) {
                        $.each(response, function (key, brand) {
                            brandSelect.append(
                                `<option value="${brand.id}">${brand.name}</option>`
                            );
                        });
                    }

                    // Preselect brand if data-selected is present
                    var preselectedBrand = brandSelect.data('selected');
                    if (preselectedBrand) {
                        brandSelect.val(preselectedBrand);
                        brandSelect.data('selected', '');
                    }
                }
            },
            error: function () {
                brandSelect.data('loading', false);
                brandSelect.html('<option value="">Error loading brands</option>');
            }
        });
    } else {
        // brandSelect.html('<option value="">Select Brand</option>');
    }
});

/* Helper: Initialize Choices.js */
window.initChoices = function (el) {

    if (!el) return;

    // Destroy existing instance if it exists in our map
    if (window.choicesMap.has(el)) {
        window.destroyChoices(el);
    }

    const isDynamic = el.classList.contains('dynamic-create');
    const type = el.getAttribute('data-type') || 'size';
    const placeholder = el.getAttribute('data-placeholder') || `Select ${type}`;

    /* ================= CONFIG ================= */
    const config = {
        removeItemButton: true,
        shouldSort: false,
        placeholder: true,
        placeholderValue: placeholder,
        searchEnabled: true,
        searchChoices: true,
        addItems: true,
        addChoices: true, 
        duplicateItemsAllowed: false,
        maxItemCount: -1,
        searchFloor: 1,
        itemSelectText: '',
        renderSelectedChoices: 'always',
        searchFields: ['label', 'value'],
        addItemText: (value) => `Press Enter to create ${type} "<b>${value}</b>"`,
        noResultsText: (value) => `No results found. Press Enter to create <b>"${value}"</b>`,
    };

    const instance = new Choices(el, config);

    window.choicesMap.set(el, instance);
    el.classList.add("choices-initialized");

    /* ================= DYNAMIC CREATE ================= */
    if (isDynamic) {
        // Handle creation on Enter key
        $(el).closest('.choices').on('keydown', '.choices__input', function (e) {
            if (e.key === 'Enter') {
                const value = this.value.trim();
                if (value) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Check if already selected
                    const activeItems = instance.getValue(true);
                    const isAlready = Array.isArray(activeItems) ? activeItems.includes(value) : activeItems === value;

                    if (!isAlready) {
                        window.saveDynamicItem(el, value, instance);
                        this.value = '';
                        instance.hideDropdown();
                    }
                }
            }
        });

        // Fallback for click/select
        el.addEventListener('addItem', function (event) {
            const val = event.detail.value;
            // Only create if it's not a numeric ID (newly typed string)
            if (val && typeof val === 'string' && isNaN(val)) {
                window.saveDynamicItem(el, val, instance);
            }
        }, false);
    }
};

/* Helper: Destroy Choices.js */
window.destroyChoices = function (selectElement) {
    if (window.choicesMap.has(selectElement)) {
        window.choicesMap.get(selectElement).destroy();
        window.choicesMap.delete(selectElement);
        selectElement.classList.remove("choices-initialized");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // Init .size-select
    document.querySelectorAll(".size-select").forEach((select) => {
        if (!select.classList.contains("choices-initialized")) {
            window.initChoices(select);
        }
    });
});

$(document).ready(function () {
    // Init .js-size-select
    document.querySelectorAll('.js-size-select').forEach(el => {
        if (!el.classList.contains("choices-initialized")) {
            window.initChoices(el);
        }
    });

    // Init .categoryBrand for custom brand entry
    document.querySelectorAll('.categoryBrand[data-allow-new="true"]').forEach(el => {
        if (!el.classList.contains("choices-initialized")) {
            window.initBrandChoices(el);
        }
    });

    /* ADD VARIANT LOGIC */
    $(document).ready(function () {
        // Init the very first select on page load
        document.querySelectorAll('.js-size-select').forEach(el => initChoices(el));

        /* ADD VARIANT LOGIC */
        $(document).on('click', '.addVariant', function () {
            // 1. Clone the first block
            let $firstBlock = $('.variant-block:first');
            let $clone = $firstBlock.clone();

            // 2. CLEANUP: Choices.js creates a UI wrapper. We must remove it to get the raw select back.
            $clone.find('.choices').each(function () {
                let $originalSelect = $(this).find('select').clone();
                $originalSelect.removeClass('choices__input choices-initialized').removeAttr('data-choice').show();
                $(this).replaceWith($originalSelect);
            });

            // 3. RESET VALUES: Clear inputs so the clone is empty
            $clone.find('input:not([type=hidden])').val('');
            $clone.find('textarea').val('');
            $clone.find('select').val('');
            $clone.find('.image-box').remove();
            $clone.find('.sortable-images').empty();
            $clone.find('.image-order-input').val('');

            // Clear size selects explicitly and reset Choices UI
            $clone.find('.js-size-select, .update-size-select').each(function() {
                const select = this;
                if (window.choicesMap && window.choicesMap.has(select)) {
                    window.destroyChoices(select);
                }
                select.innerHTML = '<option value="">Select category first</option>';
                if (window.initChoices) {
                    window.initChoices(select);
                }
            });

            // New variant → clear ID if exists
            $clone.find('.variant_id_input').val('');

            // 4. SKU AUTO-INCREMENT
            let prevSku = $('.variant-block:last').find('.skugen').val();
            if (prevSku) {
                let baseSku = prevSku.split('-')[0];
                let match = prevSku.match(/-(\d+)$/);
                let next = match ? parseInt(match[1]) + 1 : 1;
                $clone.find('.skugen').val(baseSku + '-' + String(next).padStart(2, '0'));
            }

            // 5. UI: Show the remove button
            $clone.find('.removeVariant').show();
            $clone.find('h6').text('Variant');

            // 6. APPEND
            $('.variantWrapper').append($clone);

            // 7. INITIAL CALC & STATE
            if ($clone.find('.ve-discount-type').val() === "") {
                $clone.find('.ve-discount-value').val('').prop('disabled', true);
            }

            // 8. RE-INIT & RE-INDEX
            if (typeof window.reIndexVariants === 'function') {
                window.reIndexVariants();
            }

            // Re-apply validation rules if function exists (Add Product page)
            if (typeof window.addVariantRules === 'function') {
                window.addVariantRules();
            }

            variantIndex++;
        });

        /* REMOVE VARIANT LOGIC */
        $(document).on('click', '.removeVariant, .removeExistVariant', function () {
            const selectEl = $(this).closest('.variant-block').find('.js-size-select')[0];
            if (selectEl) {
                destroyChoices(selectEl);
            }
            $(this).closest('.variant-block').remove();
            reIndexVariants();
        });

        /* DYNAMIC AJAX SIZES */
        $(document).on('change', '.SelectSizeCategory, .UpdateSizeCategory, .similerSelectSizeCategory', function () {
            // alert("suatom");
            const categoryId = $(this).val();
            const $parentBlock = $(this).closest('.variant-block, .fetch-variant-section, .row');
            const sizeSelect = $parentBlock.find('.js-size-select, .update-size-select')[0];

            if (!sizeSelect) return;

            if (!categoryId) {
                if (window.choicesMap.has(sizeSelect)) {
                    const instance = window.choicesMap.get(sizeSelect);
                    instance.clearChoices();
                    instance.setChoices([{ value: '', label: 'Select Category first', placeholder: true, disabled: true }], 'value', 'label', true);
                } else {
                    sizeSelect.innerHTML = '<option value="">Select Category first</option>';
                    window.initChoices(sizeSelect);
                }
                return;
            }

            // Show loading state in Choices
            if (window.choicesMap.has(sizeSelect)) {
                const instance = window.choicesMap.get(sizeSelect);
                instance.clearChoices();
                instance.setChoices([{ value: '', label: 'Loading...', disabled: true }], 'value', 'label', true);
            }

            $.ajax({
                global: false,
                url: BaseUrl + '/get-sizes/' + categoryId,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (window.choicesMap.has(sizeSelect)) {
                        const instance = window.choicesMap.get(sizeSelect);
                        instance.clearChoices();
                        
                        if (res.length > 0) {
                            const choices = res.map(size => ({
                                value: String(size.id),
                                label: size.name,
                                selected: false
                            }));
                            // Add placeholder and concat with fetched sizes
                            instance.setChoices([{ value: '', label: 'Select sizes', placeholder: true }].concat(choices), 'value', 'label', true);
                        } else {
                            instance.setChoices([{ value: '', label: 'No sizes found. Type to create...', placeholder: true, disabled: true }], 'value', 'label', true);
                        }
                    } else {
                        let optionsHTML = '<option value="">Select sizes</option>';
                        if (res.length > 0) {
                            res.forEach(size => {
                                optionsHTML += `<option value="${size.id}">${size.name}</option>`;
                            });
                        }
                        sizeSelect.innerHTML = optionsHTML;
                        window.initChoices(sizeSelect);
                    }
                },
                error: function () {
                    toastr.error('Failed to load sizes.');
                }
            });
        });

        /* DISCOUNT TYPE CHANGE LOGIC */
        $(document).on('change', '.ve-discount-type', function () {
            let val = $(this).val();
            let $valueInput = $(this).closest('.variant-block').find('.ve-discount-value');
            if (val === "" || val === "none" || val === null) {
                $valueInput.val('').prop('disabled', true);
            } else {
                $valueInput.prop('disabled', false);
            }
        });

        // Initialize discount value input state on page load for existing variants
        $('.ve-discount-type').each(function () {
            let val = $(this).val();
            let $valueInput = $(this).closest('.variant-block').find('.ve-discount-value');
            if (val === "" || val === "none" || val === null) {
                $valueInput.prop('disabled', true);
            } else {
                $valueInput.prop('disabled', false);
            }
        });
    });





    /* -----------------------------
       IMAGE ORDER & DELETE
       ----------------------------- */
    window.updateImageOrder = function ($container) {
        var order = [];
        $container.find('.image-box').each(function () {
            order.push($(this).data('image'));
        });
        // Try to find the nearest input or one in the same variant block
        var $input = $container.siblings('.image-order-input');
        if (!$input.length) {
            $input = $container.closest('.variant-block').find('.image-order-input');
        }
        $input.val(JSON.stringify(order));
    }

    $(document).on('click', '.delete-image-btn', function () {

        var $btn = $(this);
        var $imageBox = $btn.closest('.image-box');
        var variantId = $btn.data('variant-id');
        var imageName = $btn.data('image');

        if (!variantId || !imageName) {
            toastr.error('Missing variant ID or image name.');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "This image will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: BaseUrl + '/delete-product-image',
                    type: "POST",
                    data: {
                        variant_id: variantId,
                        image_name: imageName
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function (response) {
                        if (response.success) {
                            $imageBox.remove();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },

                    error: function (xhr) {
                        toastr.error('Error: ' + xhr.status);
                    }
                });

            }
        });

    });
    /* -----------------------------
       REINDEX VARIANTS (IMPORTANT)
       Ensures all arrays like sku[0], color[0] match the server side loop
       ----------------------------- */
    window.reIndexVariants = function () {

        $('.variant-block').each(function (index) {
            let $block = $(this);

            $block.find('h6:first').text(`Variant #${index + 1}`);

            $block.find('input[name^="variant_id"]').attr('name', `variant_id[${index}]`);
            $block.find('input[name^="sku"]').attr('name', `sku[${index}]`);
            $block.find('input[name^="color"]').attr('name', `color[${index}]`);
            $block.find('input[name^="stock"]').attr('name', `stock[${index}]`);
            $block.find('input[name^="price"]').attr('name', `price[${index}]`);
            $block.find('select[name^="discount_type"]').attr('name', `discount_type[${index}]`);
            $block.find('input[name^="discount_value"]').attr('name', `discount_value[${index}]`);
            $block.find('select[name^="size_category_id"]').attr('name', `size_category_id[${index}]`);
            $block.find('input[name^="material"]').attr('name', `material[${index}]`);

            $block.find('input[type="file"]').attr('name', `product_image[${index}][]`);
            $block.find('.js-size-select, .update-size-select')
                .attr('name', `size[${index}][]`);

            $block.find('.image-order-input').attr('name', `image_order[${index}]`);

            // Re-initialize choices for this block if not already done
            $block.find('.js-size-select, .update-size-select').each(function () {
                if (!this.classList.contains('choices-initialized')) {
                    window.initChoices(this);
                } else if (!window.choicesMap.has(this)) {
                    // If it has the class but not in map (e.g. after cloning), force re-init
                    window.initChoices(this);
                }
            });

            // If this is the edit variant page, update image order input
            if (typeof updateImageOrder === "function" && $block.find('.sortable-images').length) {
                updateImageOrder($block.find('.sortable-images'));
            }
        });
    }

    /* DYNAMIC AJAX SIZES */
    $(document).on('change', '.SelectSizeCategory', function () {
        // alert("suatom");
        const categoryId = $(this).val();
        const $parentBlock = $(this).closest('.variant-block');
        const sizeSelect = $parentBlock.find('.js-size-select')[0];

        if (!categoryId) {
            window.destroyChoices(sizeSelect);
            sizeSelect.innerHTML = '<option value="">Select category first</option>';
            window.initChoices(sizeSelect);
            return;
        }

        // Show loading state in Choices
        if (window.choicesMap.has(sizeSelect)) {
            window.choicesMap.get(sizeSelect).setChoices([{ value: '', label: 'Loading...', disabled: true }], 'value', 'label', true);
        }

        $.ajax({
            global: false,
            url: BaseUrl + '/get-sizes/' + categoryId,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                window.destroyChoices(sizeSelect);

                let optionsHTML = '';
                if (res.length > 0) {
                    res.forEach(size => {
                        optionsHTML += `<option value="${size.id}">${size.name}</option>`;
                    });
                } else {
                    optionsHTML = '<option value="">No sizes found</option>';
                }

                sizeSelect.innerHTML = optionsHTML;
                window.initChoices(sizeSelect);
            },
            error: function () {
                alert('Failed to load sizes. Please check your route.');
            }
        });
    });

    // --- Blog List Handlers ---
    $(document).on('click', '.delete-blog-btn', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",        
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            backdrop: 'rgba(0, 0, 0, 0.5)',
            allowOutsideClick: false,
            allowEscapeKey: true,
           
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json',
                    global: false,
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message || 'Blog deleted successfully');
                            $row.remove();
                        } else {
                            toastr.error(response.message || 'Failed to delete blog.');
                        }
                    },
                    error: function() {
                        toastr.error('Delete failed. Please try again.');
                    }
                });
            }
        });
    });

    $(document).on('change', '.status-switch', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const url = $(this).data('url') || (window.BaseUrl + "/admin/blog/update-status");
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                id: id,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if(data.status) {
                    toastr.success(data.message);
                } else {
                    toastr.error('Failed to update status');
                    $(this).prop('checked', !$(this).is(':checked'));
                }
            },
            error: function() {
                toastr.error('An error occurred');
                $(this).prop('checked', !$(this).is(':checked'));
            }
        });
    });

    // General Status Toggle Switch
    $(document).off('change', '.status-toggle').on('change', '.status-toggle', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const url = $(this).data('url');
        const $switch = $(this);
        
        if(!url) return;

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                id: id,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.status) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Failed to update status');
                    $switch.prop('checked', !status);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating status');
                $switch.prop('checked', !status);
            }
        });
    });

    // Product Size Category Status Update
    $(document).on('change', '.change-product-size-category-status', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const $switch = $(this);

        $.ajax({
            url: BaseUrl + '/change-product-size-category-status',
            method: 'POST',
            data: {
                id: id,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.status) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Failed to update status');
                    $switch.prop('checked', !status);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating status');
                $switch.prop('checked', !status);
            }
        });
    });

    // Product Size Status Update
    $(document).on('change', '.change-product-size-status', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const $switch = $(this);

        $.ajax({
            url: BaseUrl + '/change-product-size-status',
            method: 'POST',
            data: {
                id: id,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.status) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Failed to update status');
                    $switch.prop('checked', !status);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating status');
                $switch.prop('checked', !status);
            }
        });
    });
});


/* ==============================================
   ORDER & PAYMENT STATUS UPDATE (INLINE)
   ============================================== */

// 1. Order Status
$(document).on('click', '.order-status-badge', function () {
    var $select = $(this).siblings('.order-status-select');
    if (!$select.length) {
        return;
    }
    var current = $select.val();
    if (String(current) === '3') {
        return;
    }
    $(this).addClass('d-none');
    $select.data('current', current); // Store original status
    $select.removeClass('d-none').prop('disabled', false).focus();
});

$(document).on('change', '.order-status-select', function () {
    var select = $(this);
    var badge = select.siblings('.order-status-badge');
    var orderId = select.data('order-id');
    var status = select.val();
    var currentStatus = select.data('current') || select.val();

    // Prevent changing from "In Dispute" to anything other than "Returned"
    if (String(currentStatus) === '6' && String(status) !== '5') {
        toastr.error('For disputed orders, only "Returned" status is allowed.');
        select.val(currentStatus); // Reset to current value
        select.addClass('d-none');
        badge.removeClass('d-none');
        return;
    }

    if (String(select.data('current')) === '3') {
        select.addClass('d-none');
        badge.removeClass('d-none');
        return;
    }

    $.ajax({
        url: BaseUrl + '/update-order-status',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            order_id: orderId,
            status: status
        },
        success: function (response) {
            if (response.status) {
                // Update badge text and class
                var statusText = select.find('option:selected').text();
                var statusClass = 'bg-soft-primary text-primary';

                if (status == '0') statusClass = 'bg-soft-warning text-warning';
                if (status == '1') statusClass = 'bg-soft-info text-info';
                if (status == '2') statusClass = 'bg-soft-primary text-primary';
                if (status == '3') statusClass = 'bg-soft-success text-success';
                if (status == '4') statusClass = 'bg-soft-secondary text-secondary';
                if (status == '5') statusClass = 'bg-soft-warning text-warning';

                badge.text(statusText)
                    .removeClass('bg-soft-warning text-warning bg-soft-info text-info bg-soft-primary text-primary bg-soft-success text-success bg-soft-secondary text-secondary')
                    .addClass(statusClass);

                // If delivered, lock further edits
                if (String(status) === '3') {
                    select.prop('disabled', true).data('current', '3');
                    badge.css('cursor', 'default');
                } else {
                    select.prop('disabled', false).data('current', String(status));
                    badge.css('cursor', 'pointer');
                }

                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                } else {
                    alert(response.message);
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(response.message);
                } else {
                    alert(response.message);
                }
            }
        },
        error: function () {
            if (typeof toastr !== 'undefined') {
                toastr.error('Something went wrong');
            } else {
                alert('Something went wrong');
            }
        },
        complete: function () {
            select.addClass('d-none');
            badge.removeClass('d-none');
        }
    });
});

$(document).on('blur', '.order-status-select', function () {
    $(this).addClass('d-none');
    $(this).siblings('.order-status-badge').removeClass('d-none');
});

// Initialize delivered locks on page load
$(document).ready(function () {
    $('.order-status-select').each(function () {
        var $sel = $(this);
        var val = String($sel.val());
        $sel.data('current', val);
        if (val === '3') {
            $sel.prop('disabled', true);
            $sel.siblings('.order-status-badge').css('cursor', 'default');
        }
    });
});

// 2. Payment Status
// $(document).on('click', '.payment-status-badge', function () {
//     $(this).addClass('d-none');
//     $(this).siblings('.payment-status-select').removeClass('d-none').focus();
// });

// $(document).on('blur', '.payment-status-select', function () {
//     $(this).addClass('d-none');
//     $(this).siblings('.payment-status-badge').removeClass('d-none');
// });


// add variant get sizes 


// update product
$(document).on('change', '.UpdateSizeCategory', function () {

    const categoryId = $(this).val();
    const $parentBlock = $(this).closest('.variant-block');
    const sizeSelect = $parentBlock.find('.update-size-select, .js-size-select')[0];

    if (!sizeSelect) return;

    // If fetchSizes is available (like on edit-variant page), use it
    if (typeof window.fetchSizes === 'function') {
        window.fetchSizes(categoryId, sizeSelect);
        return;
    }

    if (!categoryId) {
        window.destroyChoices(sizeSelect);
        sizeSelect.innerHTML = '<option value="">Select category first</option>';
        window.initChoices(sizeSelect);
        return;
    }

    $.ajax({
        global: false,
        url: BaseUrl + '/get-sizes/' + categoryId,
        type: 'GET',
        dataType: 'json',
        success: function (res) {

            window.destroyChoices(sizeSelect);
            let html = '';

            if (res.length > 0) {
                res.forEach(size => {
                    html += `<option value="${size.id}">${size.name}</option>`;
                });
            } else {
                html = '<option value="">No sizes found</option>';
            }

            sizeSelect.innerHTML = html;
            window.initChoices(sizeSelect);
        },
        error: function () {
            alert('Failed to load sizes.');
        }
    });
});

// Similar product add variant size category handler
$(document).on('change', '.similerSelectSizeCategory', function () {
    const categoryId = $(this).val();
    const $parentBlock = $(this).closest('.variant-block');
    const sizeSelect = $parentBlock.find('.js-size-select')[0];

    if (!sizeSelect) return;

    if (!categoryId) {
        window.destroyChoices(sizeSelect);
        sizeSelect.innerHTML = '<option value="">Select category first</option>';
        window.initChoices(sizeSelect);
        return;
    }

    $.ajax({
        global: false,
        url: BaseUrl + '/get-sizes/' + categoryId,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            window.destroyChoices(sizeSelect);
            let html = '';

            if (res.length > 0) {
                res.forEach(size => {
                    html += `<option value="${size.id}">${size.name}</option>`;
                });
            } else {
                html = '<option value="">No sizes found</option>';
            }

            sizeSelect.innerHTML = html;
            window.initChoices(sizeSelect);
        },
        error: function () {
            alert('Failed to load sizes.');
        }
    });
});

$(document).ready(function () {

    let countryId = $('.country_id').data('selected');
    let stateId = $('.state_id').data('selected');
    let cityId = $('.city_id').data('selected');

    if (countryId) {
        $('.country_id').val(countryId).trigger('change', [stateId, cityId]);
    }
});

/* Country change */
$(document).on('change', '.country_id', function (e, selectedState, selectedCity) {

    let countryId = $(this).val();

    $('.state_id').html('<option value="">Loading...</option>');
    $('.city_id').html('<option value="">Select City</option>');

    if (!countryId) return;

    $.ajax({
        global: false,
        url: BaseUrl + '/get-states/' + countryId,
        type: 'GET',
        dataType: 'json',
        success: function (states) {

            $('.state_id').html('<option value="">Select State</option>');

            $.each(states, function (i, state) {
                $('.state_id').append(
                    `<option value="${state.id}">${state.name}</option>`
                );
            });

            if (selectedState) {
                $('.state_id').val(selectedState).trigger('change', [selectedCity]);
            }
        }
    });
});

/* State change */
$(document).on('change', '.state_id', function (e, selectedCity) {

    let stateId = $(this).val();
    $('.city_id').html('<option value="">Loading...</option>');

    if (!stateId) return;

    $.ajax({
        global: false,
        url: BaseUrl + '/get-cities/' + stateId,
        type: 'GET',
        dataType: 'json',
        success: function (cities) {

            $('.city_id').html('<option value="">Select City</option>');

            $.each(cities, function (i, city) {
                $('.city_id').append(
                    `<option value="${city.id}">${city.name}</option>`
                );
            });

            if (selectedCity) {
                $('.city_id').val(selectedCity);
            }
        }
    });
});

/* ================================
   PRODUCT LIST HELPERS
   ================================ */
function initProductList() {
    if (!window.__variantToggleBound) {
        $(document).on('click', '.toggle-variant', function () {
            let id = $(this).data('id');
            let variantRow = $('#variant_' + id);
            if (variantRow.hasClass('d-none')) {
                variantRow.removeClass('d-none').hide().slideDown(200);
            } else {
                variantRow.slideUp(200, function () {
                    variantRow.addClass('d-none').show();
                });
            }
            let icon = $(this).find('iconify-icon');
            if (variantRow.hasClass('d-none')) {
                icon.attr('icon', 'solar:alt-arrow-down-linear');
            } else {
                icon.attr('icon', 'solar:alt-arrow-up-linear');
            }
        });
        window.__variantToggleBound = true;
    }
}

$(function () {
    if (!window.__variantInitCalled && typeof initProductList === 'function') {
        initProductList();
        window.__variantInitCalled = true;
    }

    $(document).on('click', '.password-toggle-btn', function () {
        const target = $(this).data('target');
        const $input = $(target);
        if (!$input.length) return;
        const $icon = $(this).find('iconify-icon');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            if ($icon.length) $icon.attr('icon', 'solar:eye-closed-linear');
        } else {
            $input.attr('type', 'password');
            if ($icon.length) $icon.attr('icon', 'solar:eye-linear');
        }
    });
});

/* ================================
   CHANGE PRODUCT STATUS
   ================================ */
function updateProductStatusAjax(id, status, reason, $select, $badge) {
    $.ajax({
        url: BaseUrl + '/admin/change-product-status',
        type: 'POST',
        data: {
            id: id,
            status: status,
            rejection_reason: reason,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status) {
                Swal.fire('Updated!', response.message, 'success');

                // Update stats if provided
                if (response.statusCounts) {
                    updateProductStats(response.statusCounts);
                }

                let badgeClass = '';
                let badgeText = '';
                let icon = '';
                if (status == 0) {
                    badgeClass = 'bg-warning-subtle text-warning';
                    badgeText = 'Pending';
                    icon = '<iconify-icon icon="solar:clock-circle-linear" class="align-middle fs-14 me-1"></iconify-icon>';
                } else if (status == 1) {
                    badgeClass = 'bg-success-subtle text-success';
                    badgeText = 'Approved';
                    icon = '<iconify-icon icon="solar:check-read-linear" class="align-middle fs-14 me-1"></iconify-icon>';
                } else if (status == 2) {
                    badgeClass = 'bg-danger-subtle text-danger';
                    badgeText = 'Rejected';
                    icon = '<iconify-icon icon="solar:close-circle-linear" class="align-middle fs-14 me-1"></iconify-icon>';
                }
                $badge.removeClass('bg-warning-subtle text-warning bg-success-subtle text-success bg-danger-subtle text-danger')
                    .addClass(badgeClass)
                    .html(icon + ' ' + badgeText);
                $select.data('original-status', status);
                // Refresh filter if needed (optional, or trigger a custom event)
                // $('#filter-form').submit(); 
            } else {
                Swal.fire('Error!', response.message, 'error');
                $select.val($select.data('original-status'));
            }
        },
        error: function () {
            Swal.fire('Error!', 'Something went wrong.', 'error');
            $select.val($select.data('original-status'));
        },
        complete: function () {
            $select.addClass('d-none');
            $badge.removeClass('d-none');
        }
    });
}

// Global Event Listeners for Product Status
$(document).on('click', '.product-status-container', function (e) {
    if ($(e.target).hasClass('product-status-select')) return;
    $(this).find('.product-status-badge').addClass('d-none');
    $(this).find('.product-status-select').removeClass('d-none').focus();
});

$(document).on('change', '.product-status-select', function () {
    let select = $(this);
    let container = select.closest('.product-status-container');
    let productId = container.data('id');
    let status = select.val();
    let badge = container.find('.product-status-badge');

    // Store original status if not set
    if (typeof select.data('original-status') === 'undefined') {
        // Infer from badge class or text? Or assume it was correct before change.
        // Better to set it when opening or assume current value before change was correct?
        // For now, let's just proceed.
    }

    if (status == 2) { // Rejected
        Swal.fire({
            title: 'Reject Product',
            text: 'Please provide a reason for rejection:',
            input: 'textarea',
            inputPlaceholder: 'Type your reason here...',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('Rejection reason is required');
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateProductStatusAjax(productId, status, result.value, select, badge);

            } else {
                select.val(select.data('original-status') || 0); // Revert
                select.addClass('d-none');
                badge.removeClass('d-none');
            }
        });
    } else {
        updateProductStatusAjax(productId, status, '', select, badge);
    }
});

$(document).on('blur', '.product-status-select', function () {
    let select = $(this);
    setTimeout(function () {
        if (!Swal.isVisible()) {
            select.addClass('d-none');
            select.siblings('.product-status-badge').removeClass('d-none');
        }
    }, 200);
});



// Global Event Listeners for Vendor Status (vendor header only)
$(document).on('click', '.status-container .status-badge:not(.no-loader)', function () {
    let container = $(this).closest('.status-container');
    $(this).addClass('d-none');
    let select = container.find('.status-select');
    select.data('original-status', select.val());
    select.removeClass('d-none').focus();
});



$(document).on('blur', '.status-select', function () {
    let select = $(this);
    setTimeout(() => {
        if (!Swal.isVisible()) {
            select.addClass('d-none');
            select.siblings('.status-badge').removeClass('d-none');
        }
    }, 200);
});

/* ================================
   DOCUMENT STATUS (VENDOR DETAIL)
   ================================ */
// Open document select on badge click (only when a document select exists)
$(document).on('click', '.status-badge-container .status-badge', function () {
    const container = $(this).closest('.status-badge-container');
    const select = container.find('.document-status-select');
    if (!select.length) return;
    $(this).addClass('d-none');
    select.data('original-status', select.val());
    select.removeClass('d-none').focus();
});

$(document).on('change', '.document-status-select', function () {
    const select = $(this);
    const container = select.closest('.status-badge-container');
    const badge = container.find('.status-badge');
    const docId = container.data('id');
    const newStatus = select.val();
    const oldStatus = select.data('original-status');

    if (newStatus === '2') {
        Swal.fire({
            title: 'Reason for Rejection',
            input: 'textarea',
            inputLabel: 'Please provide a reason',
            inputPlaceholder: 'Type your reason here...',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            preConfirm: (reason) => {
                if (!reason) Swal.showValidationMessage('Reason is required');
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateDocumentStatusAjax(docId, newStatus, result.value, select, badge);
            } else {
                select.val(oldStatus).addClass('d-none');
                badge.removeClass('d-none');
            }
        });
    } else {
        updateDocumentStatusAjax(docId, newStatus, '', select, badge);
    }
});

$(document).on('blur', '.document-status-select', function () {
    const select = $(this);
    setTimeout(() => {
        if (!(window.Swal && Swal.isVisible && Swal.isVisible())) {
            select.addClass('d-none');
            select.siblings('.status-badge').removeClass('d-none');
        }
    }, 200);
});

function updateDocumentStatusAjax(id, status, reason, select, badge) {
    $.ajax({
        url: BaseUrl + '/change-document-status',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: id,
            status: status,
            rejection_reason: reason
        },
        success: function (response) {
            if (response.status) {
                toastr.success(response.message || 'Document status updated successfully');

                // Update badge
                let statusText = 'Pending';
                let statusClass = 'badge bg-warning-subtle text-warning py-1 px-2 fs-11 text-uppercase status-badge w-100';
                if (status == '1') {
                    statusText = 'Verified';
                    statusClass = 'badge bg-success-subtle text-success py-1 px-2 fs-11 text-uppercase status-badge w-100';
                } else if (status == '2') {
                    statusText = 'Rejected';
                    statusClass = 'badge bg-danger-subtle text-danger py-1 px-2 fs-11 text-uppercase status-badge w-100';
                }
                badge.text(statusText).attr('class', statusClass);

                // Rejection reason text
                const container = badge.closest('.status-badge-container');
                container.find('.rejection-reason-text').remove();
                if (status == '2' && reason) {
                    container.append('<p class="text-danger mt-1 mb-0 fs-11 rejection-reason-text"><strong>Reason:</strong> ' + $('<div/>').text(reason).html() + '</p>');
                }

                // Hide select, show badge
                select.addClass('d-none');
                badge.removeClass('d-none');
                select.data('original-status', status);

                // If server approved the vendor, reflect in header badge (if present)
                if (response.vendor_status === 1) {
                    const vContainer = $('.status-container').first();
                    const vBadge = vContainer.find('.status-badge');
                    const vSelect = vContainer.find('.status-select');
                    if (vBadge.length && vSelect.length) {
                        vBadge
                            .removeClass('btn-warning text-warning btn-danger text-danger')
                            .addClass('btn-primary text-primary')
                            .text('Approved');
                        vSelect.val('1');
                    }
                }
            } else {
                toastr.error(response.message || 'Failed to update document status');
                select.val(select.data('original-status'));
                select.addClass('d-none');
                badge.removeClass('d-none');
            }
        },
        error: function () {
            toastr.error('Something went wrong');
            select.val(select.data('original-status'));
            select.addClass('d-none');
            badge.removeClass('d-none');
        }
    });
}

/* ================================
   GENERIC AJAX FILTER & BULK DELETE
   ================================ */
function initAjaxFilter(formSelector, tableBodySelector, countSelector, entityName) {
    const $form = $(formSelector);
    let searchTimer;
    let lastSerializedData = $form.serialize();
    let isSubmitting = false;

    // Search Trigger (Debounce + Blur)
    const $searchInput = $form.find('input[type="text"][name="search"]');
    if ($searchInput.length) {
        $searchInput.on('blur', function () {
            clearTimeout(searchTimer);
            if ($form.serialize() !== lastSerializedData) {
                $form.submit();
            }
        });

        $searchInput.on('keyup input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                if ($form.serialize() !== lastSerializedData) {
                    $form.submit();
                }
            }, 3000);
        });
    }

    // Select & Change events (excluding range-datepicker handled by common.js)
    $form.on('change', 'select, .filter-change', function () {
        if ($form.serialize() !== lastSerializedData) {
            $form.submit();
        }
    });

    // Form Submit
    $form.on('submit', function (e) {
        e.preventDefault();
        
        if (isSubmitting) return;
        
        let formData = $(this).serialize();
        
        // Prevent double submit if data hasn't changed since last request
        if (formData === lastSerializedData && $(tableBodySelector).find('tr').length > 0) {
            return;
        }
        
        isSubmitting = true;
        lastSerializedData = formData;

        let url = $(this).attr('action');

        // Check if any filter is applied (excluding page and _token)
        const isFiltered = formData.split('&').some(param => {
            const [key, val] = param.split('=');
            return val && !['page', '_token'].includes(key);
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                // Determine content to update
                // Some responses are just TRs, others might be full table or tab content
                // We'll try to handle both if selector is specific

                if ($(tableBodySelector).length) {
                    $(tableBodySelector).html(response);

                    // Update count
                    if (countSelector) {
                        let rowCount = $(tableBodySelector).find('tr').length;
                        if ($(tableBodySelector).find('td[colspan]').length > 0) rowCount = 0;
                        
                        if (isFiltered) {
                            $(countSelector).text('Showing all ' + rowCount + ' ' + entityName);
                            // Hide pagination controls when filtered
                            $('.pagination').closest('.col-auto').hide();
                        } else {
                            // Default count text or re-render footer if needed (for now just hide/show)
                            // $(countSelector).text('Showing ' + rowCount + ' ' + entityName); 
                            $('.pagination').closest('.col-auto').show();
                        }
                    }
                } else {
                    // Fallback or specific handler for complex views like vendor tabs
                    // For vendor tabs, the response might be the whole tab content
                    // But initAjaxFilter might be too simple for Vendor List tabs?
                    // Let's assume this is for simple lists for now.
                    console.warn('Table body selector not found:', tableBodySelector);
                }

                if (typeof $.fn.tooltip !== 'undefined') {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                toastr.error('Error filtering data');
            },
            complete: function () {
                isSubmitting = false;
            }
        });
    });
}



/* ==============================================
   GENERIC BULK DELETE
   ============================================== */
function updateProductStats(statusCounts) {
    if (statusCounts) {
        $('#total-products-count').text(statusCounts.total || 0);
        $('#pending-products-count').text(statusCounts.pending || 0);
        $('#approved-products-count').text(statusCounts.approved || 0);
        $('#rejected-products-count').text(statusCounts.rejected || 0);
    }
}

function initBulkDelete(checkboxSelector, buttonSelector, url) {
    // Toggle Button Visibility
    $(document).on('change', checkboxSelector, function () {
        let count = $(checkboxSelector + ':checked').length;
        if (count > 0) $(buttonSelector).removeClass('d-none');
        else $(buttonSelector).addClass('d-none');
    });

    // Check All
    $('#checkAll').on('change', function () {
        $(checkboxSelector).prop('checked', $(this).prop('checked')).trigger('change');
    });

    // Delete Action
    $(document).on('click', buttonSelector, function () {
        let ids = [];
        $(checkboxSelector + ':checked').each(function () {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        ids: ids,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status) {
                            toastr.success(response.message);

                            // Update stats if provided
                            if (response.statusCounts) {
                                updateProductStats(response.statusCounts);
                            }

                            // Refresh table
                            // Try to find the filter form in the same container or page
                            let $filterForm = $('form[id$="-filter-form"]');
                            if ($filterForm.length > 0) {
                                $filterForm.submit();
                            } else if ($('#filter-form').length > 0) {
                                $('#filter-form').submit();
                            } else {
                                location.reload();
                            }
                            $(buttonSelector).addClass('d-none');
                            $('#checkAll').prop('checked', false);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        toastr.error('Error deleting items');
                    }
                });
            }
        });
    });
}


/* ================================
   DELETE PRODUCT
   ================================ */
$(document).on('click', '.delete-product', function (e) {
    e.preventDefault();
    let productId = $(this).data('id');
    let $row = $('#row_' + productId);

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BaseUrl + '/delete-product',
                type: 'POST',
                data: {
                    id: productId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.status) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );

                            // Update stats if provided
                            if (response.statusCounts) {
                                updateProductStats(response.statusCounts);
                            }

                            $row.fadeOut(500, function () {
                            $(this).remove();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error!',
                        'Something went wrong.',
                        'error'
                    );
                }
            });
        }
    });
});


$(document).ready(function () {
    // Status Change Logic
    $(document).on('click', '.status-badge', function () {
        let container = $(this).closest('.status-container');
        $(this).addClass('d-none');
        container.find('.status-select').removeClass('d-none').focus();
    });

    $(document).on('change', '.status-select', function () {
        let select = $(this);
        let container = select.closest('.status-container');
        let badge = container.find('.status-badge');
        let vendorId = container.data('id');
        let newStatus = select.val();

        // Get old status based on badge text
        let oldStatusText = badge.text().trim();
        let oldStatus = '4'; // Default Pending
        if (oldStatusText === 'Approved') oldStatus = '1';
        else if (oldStatusText === 'Rejected') oldStatus = '2';
        else if (oldStatusText === 'Blocked') oldStatus = '3';

        if (newStatus === '2' || newStatus === '3') { // Rejected or Blocked
            let title = newStatus === '2' ? 'Reject Vendor?' : 'Block Vendor?';
            Swal.fire({
                title: title,
                text: "Please provide a reason:",
                input: 'text',
                inputPlaceholder: 'Reason...',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Confirm',
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('Reason is required');
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    updateVendorStatus(vendorId, newStatus, result.value, select, badge);
                } else {
                    // Revert select
                    select.val(oldStatus);
                    select.addClass('d-none');
                    badge.removeClass('d-none');
                }
            });
        } else {
            Swal.fire({
                title: 'Are you sure?',
                text: "Change vendor status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateVendorStatus(vendorId, newStatus, null, select, badge);
                } else {
                    // Revert select
                    select.val(oldStatus);
                    select.addClass('d-none');
                    badge.removeClass('d-none');
                }
            });
        }
    });

    // Hide select on blur if not changed (optional UX improvement)
    $(document).on('blur', '.status-select', function () {
        // Delay to check if it's not just a click on the dropdown itself
        // But usually blur happens when clicking outside
        // We'll keep it simple: if user clicks away without changing, revert view
        // However, we need to handle the case where 'change' event fires first.
        // If change fires, it opens Swal.

        // Let's rely on change event or manual revert via ESC or clicking elsewhere? 
        // Actually, if they click away, it should just close.
        // But if they changed the value, the change event fires BEFORE blur.

        setTimeout(() => {
            if (!$(this).is(':focus') && !Swal.isVisible()) {
                $(this).addClass('d-none');
                $(this).siblings('.status-badge').removeClass('d-none');
            }
        }, 200);
    });
});

function updateVendorStatus(id, status, reason, select, badge, force = false) {
    let data = {
        _token: typeof window.csrf !== 'undefined' ? window.csrf : $('meta[name="csrf-token"]').attr('content'),
        id: id,
        status: status,
        rejection_reason: reason
    };
    if (force) {
        data.force = 1;
    }

    $.ajax({
        url: BaseUrl + '/change-vendor-status',
        type: "POST",
        data: data,
        success: function (response) {
            if (response.status === 'confirm') {
                Swal.fire({
                    title: 'Warning',
                    text: response.message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, approve anyway!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateVendorStatus(id, status, reason, select, badge, true);
                    } else {
                        // Revert select
                        let oldStatusText = badge.text().trim();
                        let oldStatus = '4';
                        if (oldStatusText === 'Approved') oldStatus = '1';
                        else if (oldStatusText === 'Rejected') oldStatus = '2';
                        else if (oldStatusText === 'Blocked') oldStatus = '3';

                        select.val(oldStatus);
                        select.addClass('d-none');
                        badge.removeClass('d-none');
                    }
                });
                return;
            }

            if (response.status) {
                toastr.success(response.message);
                // Update badge text and class
                let statusText = status == '1' ? 'Approved' : (status == '4' ? 'Pending' : (status == '3' ? 'Blocked' : 'Rejected'));
                let statusClass = 'bg-warning-subtle text-warning';
                if (status == '1') statusClass = 'bg-success-subtle text-success';
                else if (status == '3') statusClass = 'bg-dark-subtle text-dark';
                else if (status == '2') statusClass = 'bg-danger-subtle text-danger';

                badge.text(statusText)
                    .removeClass('bg-success-subtle text-success bg-warning-subtle text-warning bg-danger-subtle text-danger bg-dark-subtle text-dark')
                    .addClass(statusClass);

                select.addClass('d-none');
                badge.removeClass('d-none');
            } else {
                toastr.error(response.message);
                // Revert
                select.addClass('d-none');
                badge.removeClass('d-none');
            }
        },
        error: function () {
            toastr.error('Something went wrong');
            select.addClass('d-none');
            badge.removeClass('d-none');
        }
    });


}

// benner page

  // Open Popup
            $('.preview-banner').on('click', function() {
                var title = $(this).data('title');
                var images = $(this).data('images'); // Array of objects {name:..., url:...}

                $('#bannerPreviewModalLabel').text(title);
                $('#bannerCarouselInner').empty();

                if (images && images.length > 0) {
                    $.each(images, function(index, imgObj) {
                        var activeClass = index === 0 ? 'active' : '';
                        var itemHtml = `
                        <div class="carousel-item ${activeClass}">
                            <img src="${imgObj.url}" class="d-block w-100" alt="${imgObj.name}" style="object-fit: contain; max-height: 500px;">
                        </div>
                    `;
                        $('#bannerCarouselInner').append(itemHtml);
                    });
                } else {
                    $('#bannerCarouselInner').html('<div class="text-center p-3">No images available</div>');
                }

                // Show Bootstrap Modal
                var modalEl = document.getElementById('bannerPreviewModal');
                var myModal = bootstrap.Modal.getInstance(modalEl);
                if (!myModal) {
                    myModal = new bootstrap.Modal(modalEl);
                }
                myModal.show();
            });

            // Check All
            $('#bannerCheckAll').on('change', function() {
                $('.banner-row-checkbox').prop('checked', $(this).prop('checked'));
                toggleBulkButtons();
            });

            // Individual Check
            $(document).on('change', '.banner-row-checkbox', function() {
                if ($('.banner-row-checkbox:checked').length === $('.banner-row-checkbox').length) {
                    $('#bannerCheckAll').prop('checked', true);
                } else {
                    $('#bannerCheckAll').prop('checked', false);
                }
                toggleBulkButtons();
            });

            // Toggle Bulk Buttons
            function toggleBulkButtons() {
                if ($('.banner-row-checkbox:checked').length > 0) {
                    $('#banner_bulk_delete_btn, #banner_bulk_active_btn, #banner_bulk_deactive_btn').show();
                    $('#banner_bulk_export_btn').show();
                } else {
                    $('#banner_bulk_delete_btn, #banner_bulk_active_btn, #banner_bulk_deactive_btn').hide();
                    $('#banner_bulk_export_btn').hide();
                }
            }

            // Bulk Status (Active/Deactive)
            $('#banner_bulk_active_btn, #banner_bulk_deactive_btn').on('click', function() {
                var status = $(this).attr('id') === 'banner_bulk_active_btn' ? 1 : 0;
                var ids = $('.banner-row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Update status for " + ids.length + " banners?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, update it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('bulk.banner.status') }}",
                                type: "POST",
                                data: {
                                    ids: ids,
                                    status: status,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.status) {
                                        toastr.success(response.message);
                                        location.reload();
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function() {
                                    toastr.error('Something went wrong!');
                                }
                            });
                        }
                    });
                }
            });

            // Bulk Export
            $('#banner_bulk_export_btn').on('click', function() {
                var ids = $('.banner-row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                var form = $('<form>', {
                    action: "{{ route('export.banners') }}",
                    method: "POST"
                });

                form.append($('<input>', {
                    type: "hidden",
                    name: "_token",
                    value: "{{ csrf_token() }}"
                }));

                $.each(ids, function(index, id) {
                    form.append($('<input>', {
                        type: "hidden",
                        name: "ids[]",
                        value: id
                    }));
                });

                $('body').append(form);
                form.submit();
                form.remove();
            });

            // Bulk Delete
            $('#banner_bulk_delete_btn').on('click', function() {
                var ids = $('.banner-row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('bulk.delete.banner') }}",
                                type: "POST",
                                data: {
                                    ids: ids,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.status) {
                                        toastr.success(response.message);
                                        location.reload();
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function() {
                                    toastr.error('Something went wrong!');
                                }
                            });
                        }
                    });
                }
            });
// end banner page

/* ==============================================
   SALES REPORT SPECIFIC INIT
   ============================================== */
function initSalesReport() {
    const filterForm = $('#sales-report-filter-form');
    const searchInput = $('#sales-report-search');
    const clearSearch = $('#clear-search');
    const searchIcon = $('#search-icon');
    const tableBody = $('tbody');
    const statsCards = $('.card.bg-primary-subtle, .card.bg-success-subtle, .card.bg-danger-subtle');
    let debounceTimer;

    function fetchSalesReport(url = null) {
        $.ajax({
            url: url || filterForm.attr('action'),
            type: 'POST',
            data: filterForm.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                tableBody.html('<tr><td colspan="12" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
            },
            success: function (response) {
                if (response.table !== undefined) {
                    tableBody.html(response.table);
                    $('.pagination-container').html(response.pagination);
                    $('.card-footer p').text(response.info);

                    if (response.stats) {
                        const statCards = statsCards.toArray();
                        if (statCards[0]) $(statCards[0]).find('h4').text(response.currency + ' ' + response.stats.formatted_total_sales);
                        if (statCards[1]) $(statCards[1]).find('h4').text(response.currency + ' ' + response.stats.formatted_total_revenue);
                        if (statCards[2]) $(statCards[2]).find('h4').text(response.currency + ' ' + response.stats.formatted_total_refund);
                    }
                } else {
                   alert(response.message);
                }
            },
            error: function () {
                location.reload();
            }
        });
    }

    searchInput.on('keyup', function () {
        if ($(this).val()) {
            clearSearch.show();
            searchIcon.hide();
        } else {
            clearSearch.hide();
            searchIcon.show();
        }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchSalesReport();
        }, 3000);
    });

    clearSearch.on('click', function () {
        searchInput.val('');
        $(this).hide();
        searchIcon.show();
        fetchSalesReport();
    });

    filterForm.find('select').on('change', function () {
        fetchSalesReport();
    });

    $('.range-datepicker').on('change', function () {
        fetchSalesReport();
    });

    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        fetchSalesReport($(this).attr('href'));
    });

    $('#export-sales-report').on('click', function () {
        const action = filterForm.attr('action');
        const exportForm = $('<form action="' + action + '" method="POST" style="display:none;">');
        exportForm.append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">');
        filterForm.find('input, select').each(function () {
            if ($(this).attr('name')) {
                exportForm.append($('<input>').attr({
                    type: 'hidden',
                    name: $(this).attr('name'),
                    value: $(this).val()
                }));
            }
        });
        exportForm.append($('<input>').attr({ type: 'hidden', name: 'export', value: '1' }));
        exportForm.appendTo('body');
        exportForm.submit();
        exportForm.remove();
    });
}

/* ==============================================
   PRODUCT LIST PAGE SPECIFIC INIT
   ============================================== */
function initProductListPage(config) {
    const {
        filterFormSelector = '#filter-form',
        tableBodySelector = 'tbody',
        countSelector = '.card-footer p',
        bulkDeleteUrl,
        bulkStatusUrl,
        exportUrl
    } = config;

    if (typeof initProductList === 'function') {
        initProductList();
    }
    if (typeof initAjaxFilter === 'function') {
        initAjaxFilter(filterFormSelector, tableBodySelector, countSelector, 'products');
    }
    if (typeof initBulkDelete === 'function') {
        initBulkDelete('.row-checkbox', '#bulk-delete-btn', bulkDeleteUrl);
    }

    // Row Checkbox Change
    $(document).on('change', '.row-checkbox', function () {
        var total = $('.row-checkbox').length;
        var checked = $('.row-checkbox:checked').length;

        $('#productCheckAll').prop('checked', total === checked);

        if (checked > 0) {
            $('#product_bulk_active_btn, #product_bulk_deactive_btn').show().removeClass('d-none');
        } else {
            $('#product_bulk_active_btn, #product_bulk_deactive_btn').hide().addClass('d-none');
        }
    });

    // Check All Change
    $(document).on('change', '#productCheckAll', function () {
        $('.row-checkbox').prop('checked', $(this).prop('checked')).trigger('change');
    });

    // Bulk Status Update (Active/Deactive)
    $('#product_bulk_active_btn, #product_bulk_deactive_btn').on('click', function () {
        var status = $(this).attr('id') === 'product_bulk_active_btn' ? 1 : 0;
        var ids = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Update status for " + ids.length + " products?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: bulkStatusUrl,
                        type: "POST",
                        data: {
                            ids: ids,
                            status: status,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status) {
                                toastr.success(response.message);

                                // Update stats if provided
                                if (response.statusCounts) {
                                    updateProductStats(response.statusCounts);
                                }

                                location.reload();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function () {
                            toastr.error('Something went wrong!');
                        }
                    });
                }
            });
        }
    });

    // Bulk Export
    $(document).on('click', '#product_export_btn', function () {
        var ids = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        var form = $('<form>', {
            action: exportUrl,
            method: "POST",
            class: "no-loader"
        });

        form.append($('<input>', {
            type: "hidden",
            name: "_token",
            value: $('meta[name="csrf-token"]').attr('content')
        }));

        // Append search/filter parameters
        var filterData = $(filterFormSelector).serializeArray();
        $.each(filterData, function (i, field) {
            form.append($('<input>', {
                type: "hidden",
                name: field.name,
                value: field.value
            }));
        });

        // Append selected IDs
        if (ids.length > 0) {
            $.each(ids, function (index, id) {
                form.append($('<input>', {
                    type: "hidden",
                    name: "ids[]",
                    value: id
                }));
            });
        }

        $('body').append(form);
        form.submit();
        form.remove();
    });
}

/* ==============================================
   PRODUCT REPORT SPECIFIC INIT
   ============================================== */
function initProductReport() {
    const filterForm = $('#product-report-filter-form');
    const searchInput = $('#product-report-search');
    const tableBody = $('tbody');
    let debounceTimer;

    function fetchProductReport(url = null) {
        $.ajax({
            url: url || filterForm.attr('action'),
            type: 'POST',
            data: filterForm.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                tableBody.html('<tr><td colspan="12" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
            },
            success: function (response) {
                if (response.table !== undefined) {
                    tableBody.html(response.table);
                    $('.card-footer .pagination').html(response.pagination);
                    $('.card-footer p').text(response.info);
                } else {
                    location.reload();
                }
            },
            error: function () {
                location.reload();
            }
        });
    }

    searchInput.on('blur', function () {
        fetchProductReport();
    });

    searchInput.on('keyup', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchProductReport();
        }, 3000);
    });

    filterForm.find('select').on('change', function () {
        fetchProductReport();
    });

    $('.range-datepicker').on('change', function () {
        fetchProductReport();
    });

    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        fetchProductReport($(this).attr('href'));
    });

    filterForm.find('button[type="submit"][name="export"]').on('click', function (e) {
        e.preventDefault();
        const action = filterForm.attr('action');

        const exportForm = $('<form action="' + action + '" method="POST" style="display:none;"></form>');
        exportForm.append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">');
        filterForm.find('input, select').each(function () {
            if ($(this).attr('name')) {
                exportForm.append($('<input>').attr({
                    type: 'hidden',
                    name: $(this).attr('name'),
                    value: $(this).val()
                }));
            }
        });
        exportForm.append($('<input>').attr({ type: 'hidden', name: 'export', value: '1' }));
        exportForm.appendTo('body');
        exportForm.submit();
        exportForm.remove();
    });
}

/* ==============================================
   VENDOR REPORT SPECIFIC INIT
   ============================================== */
function initVendorReport() {
    const filterForm = $('#vendor-report-filter-form');
    const searchInput = $('#vendor-report-search');
    const tableBody = $('tbody');
    const statsCards = $('.card.bg-primary-subtle, .card.bg-success-subtle, .card.bg-danger-subtle');
    let debounceTimer;

    function fetchVendorReport(url = null) {
        $.ajax({
            url: url || filterForm.attr('action'),
            type: 'POST',
            data: filterForm.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                tableBody.html('<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
            },
            success: function (response) {
                if (response.table !== undefined) {
                    tableBody.html(response.table);
                    $('.pagination-container').html(response.pagination);
                    $('.card-footer p').text(response.info);

                    if (response.reportStats) {
                        const statCards = statsCards.toArray();
                        if (statCards[0]) $(statCards[0]).find('h4').text(response.currency + ' ' + response.reportStats.formatted_total_sales);
                        if (statCards[1]) $(statCards[1]).find('h4').text(response.currency + ' ' + response.reportStats.formatted_total_revenue);
                        if (statCards[2]) $(statCards[2]).find('h4').text(response.currency + ' ' + response.reportStats.formatted_total_refund);
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                location.reload();
            }
        });
    }

    searchInput.on('blur', function () {
        fetchVendorReport();
    });

    searchInput.on('keyup', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchVendorReport();
        }, 3000);
    });

    filterForm.find('select').on('change', function () {
        fetchVendorReport();
    });

    $('.range-datepicker').on('change', function () {
        fetchVendorReport();
    });

    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        fetchVendorReport($(this).attr('href'));
    });

    filterForm.find('button[type="submit"][name="export"]').on('click', function (e) {
        e.preventDefault();
        const action = filterForm.attr('action');

        const exportForm = $('<form action="' + action + '" method="POST" style="display:none;"></form>');
        exportForm.append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">');
        filterForm.find('input, select').each(function () {
            if ($(this).attr('name')) {
                exportForm.append($('<input>').attr({
                    type: 'hidden',
                    name: $(this).attr('name'),
                    value: $(this).val()
                }));
            }
        });
        exportForm.append($('<input>').attr({ type: 'hidden', name: 'export', value: '1' }));
        exportForm.appendTo('body');
        exportForm.submit();
        exportForm.remove();
    });
}
