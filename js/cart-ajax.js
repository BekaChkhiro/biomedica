jQuery(function($) {
  // რაოდენობის ცვლილების დამუშავება
  $(document).on('change', '.cart-quantity-input', function() {
      var $this = $(this);
      var quantity = $this.val();
      var cartItemKey = $this.closest('.cart-item').data('cart-item-key');

      updateCartItem(cartItemKey, quantity);
  });

  // პროდუქტის წაშლის დამუშავება
  $(document).on('click', '.cart-remove-item', function(e) {
      e.preventDefault();
      var $this = $(this);
      var cartItemKey = $this.closest('.cart-item').data('cart-item-key');

      updateCartItem(cartItemKey, 0);
  });

  // კალათის ელემენტის განახლება
  function updateCartItem(cartItemKey, quantity) {
      $.ajax({
          type: 'POST',
          url: wc_add_to_cart_params.ajax_url,
          data: {
              action: 'update_cart_item',
              cart_item_key: cartItemKey,
              quantity: quantity,
              security: $('input[name="woocommerce-cart-nonce"]').val()
          },
          beforeSend: function() {
              $('#cart-container').block({
                  message: null,
                  overlayCSS: {
                      background: '#fff',
                      opacity: 0.6
                  }
              });
          },
          success: function(response) {
              if (response.success) {
                  $(document.body).trigger('updated_cart_totals');
                  $('#cart-container').replaceWith(response.data.cart_html);
              }
          },
          complete: function() {
              $('#cart-container').unblock();
          }
      });
  }
});