$(function() {
    "use strict";


	$('.banner-slider').owlCarousel({
		loop:true,
		margin:0,
		responsiveClass:true,
		nav:true,
		navText: [
			"<i class='bx bx-chevron-left'></i>",
		    "<i class='bx bx-chevron-right' ></i>"
		 ],
		dots: false,
		responsive:{
			0:{
				nav:false,
				margin:0,
				items:1
			},
			576:{
				nav:false,
				items:1
			},
			768:{
				nav:false,
				items:1
			},
			1024:{
				nav:false,
				items:1
			},
			1366:{
				items:1
			},
			1400:{
				items:1
			}
	     },
    	})


	
	$('.new-arrivals').owlCarousel({
		loop:false,
		margin:24,
		responsiveClass:true,
		nav:true,
		navText: [
			"<i class='bx bx-chevron-left'></i>",
		    "<i class='bx bx-chevron-right' ></i>"
		 ],
		dots: false,
		responsive:{
			0:{
				nav:false,
				margin:16,
				items:2
			},
			576:{
				nav:false,
				items:2
			},
			768:{
				nav:false,
				items:3
			},
			1024:{
				nav:false,
				items:4
			},
			1366:{
				items:4
			},
			1400:{
				items:5
			}
	     },
    	})




		$('.browse-category').owlCarousel({
			loop:true,
			margin:24,
			responsiveClass:true,
			nav:true,
			navText: [
				"<i class='bx bx-chevron-left'></i>",
				"<i class='bx bx-chevron-right' ></i>"
			],
			dots: false,
			responsive:{
				0:{
					nav:false,
					margin:16,
					items:2
				},
				576:{
					nav:false,
					items:2
				},
				768:{
					nav:false,
					items:3
				},
				1024:{
					nav:false,
					items:4
				},
				1366:{
					items:5
				},
				1400:{
					items:5
				}
			 },
			})


			$('.latest-news').owlCarousel({
				loop:true,
				margin:24,
				responsiveClass:true,
				nav:true,
				navText: [
					"<i class='bx bx-chevron-left'></i>",
					"<i class='bx bx-chevron-right' ></i>"
				],
				dots: false,
				responsive:{
					0:{
						nav:false,
						margin:16,
						items:2
					},
					576:{
						nav:false,
						items:2
					},
					768:{
						nav:false,
						items:3
					},
					1024:{
						nav:false,
						items:4
					},
					1366:{
						items:4
					},
					1400:{
						items:4
					}
				 },
				})




				$('.brands-shops').owlCarousel({
					loop:true,
					margin:0,
					responsiveClass:true,
					nav:true,
					navText: [
						"<i class='bx bx-chevron-left'></i>",
						"<i class='bx bx-chevron-right' ></i>"
					],
					autoplay:true,
					autoplayTimeout:5000,
					dots: false,
					responsive:{
						0:{
							nav:false,
							items:2
						},
						576:{
							nav:false,
							items:3
						},
						768:{
							nav:false,
							items:4
						},
						1024:{
							nav:false,
							items:5
						},
						1366:{
							items:5
						},
						1400:{
							items:6
						}
						 },
					})


					$('.product-gallery').owlCarousel({
						loop:true,
						margin:10,
						responsiveClass:true,
						nav:false,
						dots: false,
						thumbs: true,
						thumbsPrerendered: true,
						responsive:{
							0:{
								items:1
							},
							600:{
								items:1
							},
							1000:{
								items:1
							 }
						  }
						})


					// ==================== AJAX FOR CART & WISHLIST ====================
					function showToast(message, type = 'success') {
						alert(message); // Simple toast, can be replaced with better UI
					}

					// Add to Cart
					$(document).on('click', '.add-to-cart', function(e) {
						e.preventDefault();
						var productId = $(this).data('product-id');
						
						$.ajax({
							url: addToCartUrl,
							type: 'POST',
							data: {
								product_id: productId,
								qty: 1,
								_token: csrfToken
							},
							success: function(response) {
								if (response.status) {
									showToast(response.message, 'success');
									updateCartCount();
									
									// Update ALL buttons for this product
									var $allButtons = $('[data-product-id="' + productId + '"]');
									$allButtons.each(function() {
										var $btn = $(this);
										$btn.removeClass('add-to-cart').addClass('remove-from-cart');
										if ($btn.hasClass('action-btn')) {
											$btn.html('<i class="bx bx-cart-x"></i>');
										} else {
											$btn.html('<i class="bx bx-cart-x"></i> Remove from Cart');
										}
									});
								} else {
									showToast(response.message, 'error');
								}
							},
							error: function(xhr) {
								showToast('An error occurred. Please try again.', 'error');
							}
						});
					});

					// Remove from Cart
					$(document).on('click', '.remove-from-cart', function(e) {
						e.preventDefault();
						var productId = $(this).data('product-id');
						
						$.ajax({
							url: removeFromCartUrl,
							type: 'POST',
							data: {
								product_id: productId,
								_token: csrfToken
							},
							success: function(response) {
								if (response.status) {
									showToast(response.message, 'success');
									updateCartCount();
									
									// Update ALL buttons for this product
									var $allButtons = $('[data-product-id="' + productId + '"]');
									$allButtons.each(function() {
										var $btn = $(this);
										$btn.removeClass('remove-from-cart').addClass('add-to-cart');
										if ($btn.hasClass('action-btn')) {
											$btn.html('<i class="bx bx-cart-add"></i>');
										} else {
											$btn.html('<i class="bx bx-cart-add"></i> Add to Cart');
										}
									});
								} else {
									showToast(response.message, 'error');
								}
							},
							error: function(xhr) {
								showToast('Please go to cart page to remove item', 'info');
							}
						});
					});

					// Toggle Wishlist
					$(document).on('click', '.add-to-wishlist', function(e) {
						e.preventDefault();
						var productId = $(this).data('product-id');
						
						$.ajax({
							url: wishlistToggleUrl,
							type: 'POST',
							data: {
								product_id: productId,
								_token: csrfToken
							},
							success: function(response) {
								if (response.status) {
									showToast(response.message, 'success');
									updateWishlistCount();
									
									// Update ALL wishlist buttons for this product
									var $allButtons = $('.add-to-wishlist[data-product-id="' + productId + '"]');
									$allButtons.each(function() {
										var $btn = $(this);
										if (response.is_in_wishlist) {
											$btn.addClass('active');
											$btn.find('i').removeClass('bx-heart').addClass('bxs-heart');
											// Update text if it's not an action-btn
											if (!$btn.hasClass('action-btn')) {
												$btn.html('<i class="bx bxs-heart"></i> Remove from Wishlist');
											}
										} else {
											$btn.removeClass('active');
											$btn.find('i').removeClass('bxs-heart').addClass('bx-heart');
											// Update text if it's not an action-btn
											if (!$btn.hasClass('action-btn')) {
												$btn.html('<i class="bx bx-heart"></i> Add to Wishlist');
											}
										}
									});
								}
							},
							error: function(xhr) {
								showToast('An error occurred. Please try again.', 'error');
							}
						});
					});

					function updateCartCount() {
						$.ajax({
							url: cartCountUrl,
							type: 'GET',
							success: function(response) {
								if (response.status) {
									// Update cart count in header (you can add a cart count element)
									// Example: $('.cart-count').text(response.count);
									$('.alert-count').text(response.count);
								}
							}
						});
					}

					function updateWishlistCount() {
						$.ajax({
							url: wishlistToggleUrl.replace('/toggle', '/count'),
							type: 'GET',
							success: function(response) {
								if (response.status) {
									// Update wishlist count in header (you can add a wishlist count element)
									// Example: $('.wishlist-count').text(response.count);
								}
							}
						});
					}

   });	 
   