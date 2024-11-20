<div class="custom-mini-cart">
    <div class="custom-cart-count-container">
        <h2 class="custom-cart-count-label">კალათა</h2>
        <span class="custom-cart-item-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    </div>
    <div class="custom-cart-content">
    <?php if ( ! WC()->cart->is_empty() ) : ?>
        <div class="custom-cart-items-content">
            <ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
                <?php
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                        $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                        $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        ?>
                        <li class="woocommerce-mini-cart-item mini_cart_item cart-item" style="height: 90px;" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" >
                            <div class="product-thumbnail w-1/6">
                                <?php echo $thumbnail; ?>
                            </div>
                            <div class="product-info w-4/6">
                                <div class="product-name">
                                    <?php echo $product_name; ?>
                                </div>
                            </div>
                            <div class="product-remove mt-6 w-1/6">
                                <a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="remove remove_from_cart_button" aria-label="<?php esc_attr_e( 'Remove this item', 'woocommerce' ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>" data-product_sku="<?php echo esc_attr( $_product->get_sku() ); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="hugeicons:delete-01">
                                        <path id="Vector" d="M19.5 5.5L18.88 15.525C18.722 18.086 18.643 19.367 18 20.288C17.6826 20.7432 17.2739 21.1273 16.8 21.416C15.843 22 14.56 22 11.994 22C9.424 22 8.139 22 7.18 21.415C6.70589 21.1257 6.29721 20.7409 5.98 20.285C5.338 19.363 5.26 18.08 5.106 15.515L4.5 5.5M3 5.5H21M16.056 5.5L15.373 4.092C14.92 3.156 14.693 2.689 14.302 2.397C14.2151 2.33232 14.1232 2.27479 14.027 2.225C13.594 2 13.074 2 12.035 2C10.969 2 10.436 2 9.995 2.234C9.89752 2.28621 9.80453 2.34642 9.717 2.414C9.322 2.717 9.101 3.202 8.659 4.171L8.053 5.5" stroke="#4B4B4B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </g>
                                    </svg>
                                </a>
                                <div class="product-price">
                                    <?php echo $product_price; ?>
                                </div>
                            </div>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
        <div class="custom-mini-cart-order-review">
            <p class="woocommerce-mini-cart__total total" style="font-weight: 600;"><strong style="color: #707070;">სულ:</strong> <span class="total-amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span></p>
            <div class="mini-cart-buttons">
                <a href="<?php echo wc_get_checkout_url(); ?>" class="button checkout wc-forward custom-mini-cart-checkout-button">
                <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="Group">
                    <g id="Vector">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.16667 3.46683H16.5L14.8083 9.38683C14.6808 9.83247 14.4117 10.2245 14.0416 10.5035C13.6715 10.7826 13.2206 10.9335 12.7571 10.9335H5.6104C5.08503 10.9338 4.57802 10.7402 4.18653 10.3899C3.79504 10.0395 3.54659 9.55701 3.4888 9.03483L2.63333 1.3335H0.5" fill="white"/>
                    <path d="M3.16667 3.46683H16.5L14.8083 9.38683C14.6808 9.83247 14.4117 10.2245 14.0416 10.5035C13.6715 10.7826 13.2206 10.9335 12.7571 10.9335H5.6104C5.08503 10.9338 4.57802 10.7402 4.18653 10.3899C3.79504 10.0395 3.54659 9.55701 3.4888 9.03483L2.63333 1.3335H0.5" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <g id="Group_2">
                    <path id="Vector_2" d="M5.30007 14.667C5.88917 14.667 6.36673 14.1895 6.36673 13.6004C6.36673 13.0113 5.88917 12.5337 5.30007 12.5337C4.71096 12.5337 4.2334 13.0113 4.2334 13.6004C4.2334 14.1895 4.71096 14.667 5.30007 14.667Z" fill="white"/>
                    <path id="Vector_3" d="M13.8333 14.667C14.4224 14.667 14.8999 14.1895 14.8999 13.6004C14.8999 13.0113 14.4224 12.5337 13.8333 12.5337C13.2442 12.5337 12.7666 13.0113 12.7666 13.6004C12.7666 14.1895 13.2442 14.667 13.8333 14.667Z" fill="white"/>
                    </g>
                    </g>
                </svg>
                ყიდვა
                </a>
                <a href="<?php echo wc_get_cart_url(); ?>" class="button wc-forward custom-mini-cart-cart-button">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="ph:bag">
                        <path id="Vector" d="M13.1562 4.25H10.8125C10.8125 3.50408 10.5162 2.78871 9.98874 2.26126C9.46129 1.73382 8.74592 1.4375 8 1.4375C7.25408 1.4375 6.53871 1.73382 6.01126 2.26126C5.48382 2.78871 5.1875 3.50408 5.1875 4.25H2.84375C2.59511 4.25 2.35665 4.34877 2.18084 4.52459C2.00502 4.7004 1.90625 4.93886 1.90625 5.1875V12.2188C1.90625 12.4674 2.00502 12.7058 2.18084 12.8817C2.35665 13.0575 2.59511 13.1562 2.84375 13.1562H13.1562C13.4049 13.1562 13.6433 13.0575 13.8192 12.8817C13.995 12.7058 14.0938 12.4674 14.0938 12.2188V5.1875C14.0938 4.93886 13.995 4.7004 13.8192 4.52459C13.6433 4.34877 13.4049 4.25 13.1562 4.25ZM8 2.375C8.49728 2.375 8.97419 2.57254 9.32582 2.92417C9.67746 3.27581 9.875 3.75272 9.875 4.25H6.125C6.125 3.75272 6.32254 3.27581 6.67417 2.92417C7.02581 2.57254 7.50272 2.375 8 2.375ZM13.1562 12.2188H2.84375V5.1875H5.1875V6.125C5.1875 6.24932 5.23689 6.36855 5.32479 6.45646C5.4127 6.54436 5.53193 6.59375 5.65625 6.59375C5.78057 6.59375 5.8998 6.54436 5.98771 6.45646C6.07561 6.36855 6.125 6.24932 6.125 6.125V5.1875H9.875V6.125C9.875 6.24932 9.92439 6.36855 10.0123 6.45646C10.1002 6.54436 10.2194 6.59375 10.3438 6.59375C10.4681 6.59375 10.5873 6.54436 10.6752 6.45646C10.7631 6.36855 10.8125 6.24932 10.8125 6.125V5.1875H13.1562V12.2188Z" fill="#00915A"/>
                        </g>
                    </svg>
                    კალათაში გადასვლა
                </a>
            </div>
        </div>
    <?php else : ?>
        <p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>
    <?php endif; ?>
    </div>
</div>

<!-- JavaScript part -->
<script type="text/javascript">
jQuery(function($) {
  $(document).on('click', '.remove_from_cart_button', function(e) {
      e.preventDefault();

      var $thisbutton = $(this),
          $miniCart = $('.custom-mini-cart'),
          cart_item_key = $thisbutton.data('cart_item_key');

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
          url: wc_add_to_cart_params.ajax_url,
          data: {
              action: 'remove_item_from_cart',
              cart_item_key: cart_item_key
          },
          success: function(response) {
              if (response.success) {
                  $thisbutton.closest('li').remove();
                  $('.custom-cart-item-count').text(response.data.cart_count);
                  $('.total-amount').html(response.data.cart_total);

                  if (parseInt(response.data.cart_count) === 0) {
                      $('.custom-cart-content').html('<p class="text-center text-gray-600">' + (response.data.empty_cart_message || 'თქვენ კალათში პროდუქტები არ არის.') + '</p>');
                  }

                  $(document.body).trigger('removed_from_cart', [response.data.fragments, response.data.cart_hash, $thisbutton]);
                  
                  // Update all elements with class 'custom-cart-item-count'
                  $('.custom-cart-item-count').text(response.data.cart_count);
              }
          },
          error: function(xhr, status, error) {
              console.log('Error:', error);
          },
          complete: function() {
              $miniCart.unblock();
          }
      });

      return false;
  });

  // Add to cart AJAX
  $(document).on('click', '.ajax_add_to_cart', function(e) {
      e.preventDefault();

      var $thisbutton = $(this),
          product_id = $thisbutton.data('product_id'),
          quantity = $thisbutton.data('quantity') || 1;

      $thisbutton.removeClass('added');
      $thisbutton.addClass('loading');

      $.ajax({
          type: 'POST',
          url: wc_add_to_cart_params.ajax_url,
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
                  
                  // Update cart count
                  var cartCount = $(response.fragments['.widget_shopping_cart_content'])
                      .find('.custom-cart-item-count').text();
                  $('.custom-cart-item-count').text(cartCount);
              }
              
              $thisbutton.removeClass('loading');
              $thisbutton.addClass('added');
              
              $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
          },
          error: function(xhr, status, error) {
              console.log('Error:', error);
              $thisbutton.removeClass('loading');
          }
      });

      return false;
  });
});
</script>