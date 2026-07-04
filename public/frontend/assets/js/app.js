
$(document).ready(function() {
  // =============================================
  // HEADER
  // =============================================
  // Search form
  $('.search-icon').on('click', function() {
    $('.search-area').addClass('open');
  });
  $('.search-close').on('click', function() {
    $('.search-area').removeClass('open');
  });
  // Sidebar menu
  $('.bar-icon').on('click', function() {
    $('.sidebar-menu').addClass('open');
    $('.overlay').addClass('open');
  });
  $('.menu-close, .overlay').on('click', function() {
    $('.sidebar-menu').removeClass('open');
    $('.overlay').removeClass('open');
  });
  // Mobile menu
  $('.mobile-menu').on('click', function() {
    $('.mobile-menu ul').slideToggle('slow');
  });
  // Sidebar dropdown
  $('.sidebar-menu .dropdown-submenu').parent('li').children('a').append('<span class="arrow"></span>');
  $('.sidebar-menu .dropdown-submenu > a').on('click', function(e) {
    e.preventDefault();
    $(this).next('.sidebar-menu .dropdown-submenu').slideToggle('slow');
    $(this).parent('li').siblings().children('.sidebar-menu .dropdown-submenu').slideUp('slow');
  });
  // =============================================
  // PRODUCT CAROUSEL
  // =============================================
  $('.product-carousel').owlCarousel({
    loop: true,
    margin: 30,
    nav: true,
    dots: false,
    responsive: {
      0: { items: 1 },
      576: { items: 2 },
      768: { items: 3 },
      992: { items: 4 },
      1200: { items: 4 }
    }
  });
  // =============================================
  // TOOLTIP
  // =============================================
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });
  // =============================================
  // SIDEBAR FILTER
  // =============================================
  $('.filter-btn').on('click', function() {
    $('.filter-sidebar').addClass('open');
    $('.overlay').addClass('open');
  });
  $('.filter-close').on('click', function() {
    $('.filter-sidebar').removeClass('open');
    $('.overlay').removeClass('open');
  });
  // =============================================
  // THEME SWITCHER
  // =============================================
  $('.switcher-btn').on('click', function() {
    $('.switcher-wrapper').toggleClass('switcher-toggled')
  });
  $('.close-switcher').on('click', function() {
    $('.switcher-wrapper').removeClass('switcher-toggled')
  });
  $('#theme1').click(theme1);
  $('#theme2').click(theme2);
  $('#theme3').click(theme3);
  $('#theme4').click(theme4);
  $('#theme5').click(theme5);
  $('#theme6').click(theme6);
  $('#theme7').click(theme7);
  $('#theme8').click(theme8);
  $('#theme9').click(theme9);
  $('#theme10').click(theme10);
  $('#theme11').click(theme11);
  $('#theme12').click(theme12);
  $('#theme13').click(theme13);
  $('#theme14').click(theme14);
  $('#theme15').click(theme15);
  function theme1() {
    $('body').attr('class', 'bg-theme bg-theme1');
  }
  function theme2() {
    $('body').attr('class', 'bg-theme bg-theme2');
  }
  function theme3() {
    $('body').attr('class', 'bg-theme bg-theme3');
  }
  function theme4() {
    $('body').attr('class', 'bg-theme bg-theme4');
  }
  function theme5() {
    $('body').attr('class', 'bg-theme bg-theme5');
  }
  function theme6() {
    $('body').attr('class', 'bg-theme bg-theme6');
  }
  function theme7() {
    $('body').attr('class', 'bg-theme bg-theme7');
  }
  function theme8() {
    $('body').attr('class', 'bg-theme bg-theme8');
  }
  function theme9() {
    $('body').attr('class', 'bg-theme bg-theme9');
  }
  function theme10() {
    $('body').attr('class', 'bg-theme bg-theme10');
  }
  function theme11() {
    $('body').attr('class', 'bg-theme bg-theme11');
  }
  function theme12() {
    $('body').attr('class', 'bg-theme bg-theme12');
  }
  function theme13() {
    $('body').attr('class', 'bg-theme bg-theme13');
  }
  function theme14() {
    $('body').attr('class', 'bg-theme bg-theme14');
  }
  function theme15() {
    $('body').attr('class', 'bg-theme bg-theme15');
  }
});
// =============================================
// AJAX - ADD TO CART / REMOVE FROM CART
// =============================================
$(document).on('click', '.add-to-cart', function(e) {
  e.preventDefault();
  var $btn = $(this);
  var productId = $btn.attr('data-product-id');
  var variantId = $btn.attr('data-variant-id');
  var qty = $btn.attr('data-qty') || 1;
  if (!productId) {
    alert('Product ID is required');
    return;
  }
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Adding...');
  $.ajax({
    url: addToCartUrl,
    method: 'POST',
    data: {
      _token: csrfToken,
      product_id: productId,
      variant_id: variantId,
      qty: qty
    },
    success: function(res) {
      if (res.status) {
        showToast('success', res.message || 'Item added to cart');
        // Toggle ALL buttons for this product to remove state
        $('[data-product-id="' + productId + '"]').not('.add-to-wishlist').each(function() {
          var $b = $(this);
          $b.removeClass('add-to-cart').addClass('remove-from-cart');
          if ($b.hasClass('btn-ecomm')) {
            $b.html('<i class="bx bx-cart-x"></i> Remove from Cart');
          } else {
            $b.html('<i class="bx bx-cart-x"></i>');
          }
        });
        refreshCartCount();
      } else {
        showToast('error', res.message || 'Failed to add item');
        $btn.prop('disabled', false);
      }
    },
    error: function(xhr) {
      var msg = 'Failed to add item';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showToast('error', msg);
      $btn.prop('disabled', false);
    }
  });
});
$(document).on('click', '.remove-from-cart', function(e) {
  e.preventDefault();
  var $btn = $(this);
  var productId = $btn.attr('data-product-id');
  if (!productId) {
    alert('Product ID is required');
    return;
  }
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Removing...');
  $.ajax({
    url: removeFromCartUrl,
    method: 'POST',
    data: {
      _token: csrfToken,
      product_id: productId
    },
    success: function(res) {
      if (res.status) {
        showToast('success', res.message || 'Item removed from cart');
        // Toggle ALL buttons for this product back to add state
        $('[data-product-id="' + productId + '"]').not('.add-to-wishlist').each(function() {
          var $b = $(this);
          $b.removeClass('remove-from-cart').addClass('add-to-cart');
          if ($b.hasClass('btn-ecomm')) {
            $b.html('<i class="bx bx-cart-add"></i> Add to Cart');
          } else {
            $b.html('<i class="bx bx-cart-add"></i>');
          }
        });
        refreshCartCount();
      } else {
        showToast('error', res.message || 'Failed to remove item');
        $btn.prop('disabled', false);
      }
    },
    error: function(xhr) {
      var msg = 'Failed to remove item';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showToast('error', msg);
      $btn.prop('disabled', false);
    }
  });
});
// =============================================
// AJAX - WISHLIST TOGGLE
// =============================================
$(document).on('click', '.add-to-wishlist', function(e) {
  e.preventDefault();
  var $btn = $(this);
  var productId = $btn.attr('data-product-id');
  if (!productId) {
    productId = $btn.closest('[data-product-id]').attr('data-product-id');
  }
  if (!productId) {
    alert('Product ID is required');
    return;
  }
  $btn.prop('disabled', true);
  $.ajax({
    url: wishlistToggleUrl,
    method: 'POST',
    data: {
      _token: csrfToken,
      product_id: productId
    },
    success: function(res) {
      if (res.status) {
        $btn.prop('disabled', false);
        // Update ALL wishlist buttons for this product
        $('.add-to-wishlist[data-product-id="' + productId + '"]').each(function() {
          var $b = $(this);
          if (res.is_in_wishlist) {
            $b.addClass('active text-danger');
            if ($b.hasClass('btn-ecomm')) {
              $b.html('<i class="bx bxs-heart"></i> Remove from Wishlist');
            } else {
              $b.find('i').attr('class', 'bx bxs-heart');
            }
          } else {
            $b.removeClass('active text-danger');
            if ($b.hasClass('btn-ecomm')) {
              $b.html('<i class="bx bx-heart"></i> Add to Wishlist');
            } else {
              $b.find('i').attr('class', 'bx bx-heart');
            }
          }
        });
        showToast('success', res.message || 'Wishlist updated');
      } else {
        showToast('error', res.message || 'Failed to update wishlist');
        $btn.prop('disabled', false);
      }
    },
    error: function(xhr) {
      var msg = 'Failed to update wishlist';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showToast('error', msg);
      $btn.prop('disabled', false);
    }
  });
});
// =============================================
// MINI CART - REMOVE ITEM
// =============================================
$(document).on('click', '.mini-cart-remove', function(e) {
  e.preventDefault();
  e.stopPropagation();
  var $btn = $(this);
  var id = $btn.attr('data-cart-id');
  var $item = $btn.closest('.dropdown-item');
  $.ajax({
    url: '/cart/remove/' + id,
    method: 'GET',
    data: { _token: csrfToken || $('meta[name="csrf-token"]').attr('content') },
    success: function(res) {
      if (res.status) {
        $item.fadeOut(300, function() { $(this).remove(); });
        if (typeof refreshCartCount === 'function') {
          refreshCartCount();
        }
      }
    }
  });
});
// =============================================
// CART COUNT REFRESH
// =============================================
function refreshCartCount() {
  $.ajax({
    url: cartCountUrl || '/cart/count',
    method: 'GET',
    success: function(res) {
      if (res.status && res.count !== undefined) {
        $('.cart-count, .cart-count-header').text(res.count);
      }
    }
  });
}
// =============================================
// TOAST NOTIFICATION
// =============================================
function showToast(type, message) {
  // Create toast element
  var toastEl = document.createElement('div');
  toastEl.className = 'toast position-fixed top-0 end-0 zindex-popover bg-white shadow p-3';
  toastEl.style.marginTop = '100px';
  toastEl.style.marginRight = '20px';
  toastEl.setAttribute('role', 'alert');
  toastEl.setAttribute('aria-live', 'assertive');
  toastEl.setAttribute('aria-atomic', 'true');
  // Add content
  var icon = 'bx-info-circle';
  var iconColor = 'text-primary';
  if (type === 'success') {
    icon = 'bx-check-circle';
    iconColor = 'text-success';
  } else if (type === 'error') {
    icon = 'bx-error-circle';
    iconColor = 'text-danger';
  } else if (type === 'warning') {
    icon = 'bx-warning';
    iconColor = 'text-warning';
  }
  toastEl.innerHTML = '\
    <div class="d-flex align-items-center">\
      <i class="bx ' + icon + ' fs-4 ' + iconColor + '"></i>\
      <div class="ms-3 flex-grow-1">\
        <p class="mb-0">' + message + '</p>\
      </div>\
      <button type="button" class="ms-2 mb-1 close" data-bs-dismiss="toast" aria-label="Close">\
        <span aria-hidden="true">&times;</span>\
      </button>\
    </div>\
  ';
  // Append to body
  document.body.appendChild(toastEl);
  // Initialize and show
  var toast = new bootstrap.Toast(toastEl, {
    delay: 5000
  });
  toast.show();
  // Remove after it's hidden
  toastEl.addEventListener('hidden.bs.toast', function() {
    toastEl.remove();
  });
}
