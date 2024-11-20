// Save this as js/custom-mini-cart.js in your theme directory
jQuery(function($) {
  var $miniCart = $('.custom-mini-cart');
  var $body = $('body');

  // Function to open mini cart
  function openMiniCart() {
      $miniCart.addClass('open');
      $body.addClass('mini-cart-open');
  }

  // Function to close mini cart
  function closeMiniCart() {
      $miniCart.removeClass('open');
      $body.removeClass('mini-cart-open');
  }

  // Function to remove item from cart
  function removeItemFromCart($button) {
      var cart_item_key = $button.data('cart_item_key');

      $miniCart.block({
          message: null,
          overlayCSS: {
              background: '#fff',
              opacity: 0.6
          }
      });

      $.ajax({
          type: 'POST',
          dataType: 'json',
          url: ajax_object.ajax_url,
          data: {
              action: 'remove_item_from_cart',
              cart_item_key: cart_item_key
          },
          success: function(response) {
              if (response.success) {
                  $button.closest('.woocommerce-mini-cart-item').remove();
                  updateCartCount(response.data.cart_count);
                  updateCartTotal(response.data.cart_total);

                  if (response.data.cart_count === 0) {
                      showEmptyCartMessage();
                  }

                  $(document.body).trigger('removed_from_cart', [response.data.fragments, response.data.cart_hash, $button]);
              }
          },
          error: function(xhr, status, error) {
              console.log('Error:', error);
          },
          complete: function() {
              $miniCart.unblock();
          }
      });
  }

  // Function to update cart count
  function updateCartCount(count) {
      $('.custom-cart-item-count').text(count);
  }

  // Function to update cart total
  function updateCartTotal(total) {
      $('.total-amount').html(total);
  }

  // Function to show empty cart message
  function showEmptyCartMessage() {
      $('.custom-cart-content').html('<p class="woocommerce-mini-cart__empty-message">' + wc_add_to_cart_params.i18n_empty_cart + '</p>');
  }

  // Event listener for remove button click
  $(document).on('click', '.remove_from_cart_button', function(e) {
      e.preventDefault();
      removeItemFromCart($(this));
      return false;
  });

  // Event listener for adding product to cart
  $(document).on('click', '.ajax_add_to_cart', function(e) {
      e.preventDefault();

      var $thisbutton = $(this),
          product_id = $thisbutton.data('product_id'),
          quantity = $thisbutton.data('quantity') || 1;

      $thisbutton.removeClass('added').addClass('loading');

      $.ajax({
          type: 'POST',
          url: ajax_object.ajax_url,
          data: {
              action: 'woocommerce_ajax_add_to_cart',
              product_id: product_id,
              quantity: quantity
          },
          success: function(response) {
              if (response.fragments) {
                  $.each(response.fragments, function(key, value) {
                      $(key).replaceWith(value);
                  });
                  updateCartCount($(response.fragments['.widget_shopping_cart_content']).find('.custom-cart-item-count').text());
              }
              
              $thisbutton.removeClass('loading').addClass('added');
              $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
              openMiniCart(); // Open mini cart after adding product
          },
          error: function(xhr, status, error) {
              console.log('Error:', error);
              $thisbutton.removeClass('loading');
          }
      });

      return false;
  });

  // Event listener for opening mini cart
  $(document).on('click', '.open-mini-cart', function(e) {
      e.preventDefault();
      openMiniCart();
  });

  // Event listener for closing mini cart
  $(document).on('click', '.close-mini-cart', function(e) {
      e.preventDefault();
      closeMiniCart();
  });

  // Close mini cart when clicking outside
  $(document).on('click', function(e) {
      if ($miniCart.hasClass('open') && !$(e.target).closest('.custom-mini-cart, .open-mini-cart').length) {
          closeMiniCart();
      }
  });

  // Prevent closing when clicking inside mini cart
  $miniCart.on('click', function(e) {
      e.stopPropagation();
  });

  // Refresh mini cart periodically
  function refreshMiniCart() {
      $.ajax({
          url: ajax_object.ajax_url,
          type: 'POST',
          data: {
              action: 'refresh_mini_cart'
          },
          success: function(response) {
              if (response.success) {
                  $miniCart.replaceWith(response.data);
                  $miniCart = $('.custom-mini-cart'); // Update $miniCart reference
              }
          }
      });
  }

  // Refresh mini cart every 30 seconds
  setInterval(refreshMiniCart, 30000);
});