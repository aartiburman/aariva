$(function() {
	"use strict";


  new PerfectScrollbar('.cart-list');

// Prevent closing from click inside dropdown

/*$(document).on('click', '.dropdown-menu', function (e) {
  e.stopPropagation();
});*/



 // jquery ready start
 $(document).ready(function() {
  // jQuery code

  $("[data-trigger]").on("click", function(e){
    e.preventDefault();
    e.stopPropagation();
    var offcanvas_id =  $(this).attr('data-trigger');
    $(offcanvas_id).toggleClass("show");
    $('body').toggleClass("offcanvas-active");
    $(".screen-overlay").toggleClass("show");
  }); 

  // Close menu when pressing ESC
  $(document).on('keydown', function(event) {
    if(event.keyCode === 27) {
    $(".mobile-offcanvas").removeClass("show");
    $("body").removeClass("overlay-active");
    }
  });

  $(".btn-close, .screen-overlay").click(function(e){
    $(".screen-overlay").removeClass("show");
    $(".mobile-offcanvas").removeClass("show");
    $("body").removeClass("offcanvas-active");


  }); 


}); // jquery end




$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
  if (!$(this).next().hasClass('show')) {
    $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
  }
  var $subMenu = $(this).next(".dropdown-menu");
  $subMenu.toggleClass('show');


  $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
    $('.submenu .show').removeClass("show");
  });


  return false;
});




	$(document).ready(function() {
		$(window).on("scroll", function() {
			$(this).scrollTop() > 300 ? $(".back-to-top").fadeIn() : $(".back-to-top").fadeOut()
		}), $(".back-to-top").on("click", function() {
			return $("html, body").animate({
				scrollTop: 0
			}, 600), !1
		})
	}),

	var megaMenuTimer;

	$(document).on('mouseenter', '.mega-menu', function() {
		if ($(window).width() < 1200) return;
		clearTimeout(megaMenuTimer);
		var $menu = $(this);
		var $content = $menu.children('.mega-menu-content');
		if (!$content.length) return;
		var offset = $menu.offset();
		var navBottom = offset.top + $menu.outerHeight();
		$content.css({
			'position': 'fixed',
			'top': navBottom,
			'left': 0,
			'width': '100vw'
		}).addClass('show');
	});

	$(document).on('mouseleave', '.mega-menu', function() {
		if ($(window).width() < 1200) return;
		var $menu = $(this);
		megaMenuTimer = setTimeout(function() {
			$menu.children('.mega-menu-content').removeClass('show');
		}, 150);
	});

	$(document).on('mouseenter', '.mega-menu-content', function() {
		clearTimeout(megaMenuTimer);
	});

	$(document).on('mouseleave', '.mega-menu-content', function() {
		$(this).removeClass('show');
	}),



	$(".btn-mobile-filter").on("click", function() {
		$(".filter-sidebar").removeClass("d-none")
	}),
  
    $(".btn-mobile-filter-close").on("click", function() {
		$(".filter-sidebar").addClass("d-none")
	}),


	
	$(".switcher-btn").on("click", function() {
		$(".switcher-wrapper").toggleClass("switcher-toggled")
	}),
  
  $(".close-switcher").on("click", function() {
		$(".switcher-wrapper").removeClass("switcher-toggled")
	}),


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
    var productId = $btn.data('product-id');

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
    var productId = $btn.data('product-id');

    if (!productId) {
        productId = $btn.closest('[data-product-id]').data('product-id');
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
    var id = $btn.data('cart-id');
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
            if (res.status) {
                $('.alert-count, .cart-count-badge').text(res.count);
            }
        }
    });
}

// =============================================
// TOAST NOTIFICATION
// =============================================
function showToast(type, message) {
    var icon = type === 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle';
    var bg = type === 'success' ? 'bg-success' : 'bg-danger';
    var toast = $(
        '<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999;">' +
        '<div class="toast align-items-center text-white ' + bg + ' border-0 show" role="alert">' +
        '<div class="d-flex">' +
        '<div class="toast-body d-flex align-items-center gap-2"><i class="' + icon + ' fs-5"></i> ' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div>' +
        '</div>' +
        '</div>'
    );
    $('body').append(toast);
    setTimeout(function() {
        toast.find('.toast').removeClass('show');
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}