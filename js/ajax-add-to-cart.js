jQuery(function($) {
    // კალათაში დამატების ფუნქციონალი
    $(document).on('click', '.custom-add-to-cart-button .ajax-add-to-cart', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $thisbutton = $(this);
        var product_id = $thisbutton.data('product_id');
        var quantity = $thisbutton.data('quantity') || 1;
        var product_name = $thisbutton.closest('.product').find('.product-title').text() || 'პროდუქტი'; // პროდუქტის სახელის წამოღება

        $thisbutton.addClass('loading');

        $('.cart-detail-d14a59').addClass('active');

        $.ajax({
            type: 'POST',
            url: bricks_ajax.ajaxurl,
            data: {
                action: 'bricks_ajax_add_to_cart',
                product_id: product_id,
                quantity: quantity
            },
            success: function(response) {
                if (response.error) {
                    window.location = response.product_url;
                    return;
                }

                $thisbutton.removeClass('loading');

                if (response.fragments) {
                    $.each(response.fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });

                    $('.cart-detail-d14a59').each(function() {
                        var $this = $(this);
                        $this.html($this.html());
                    });
                }

                // მობილურზე შეტყობინების გამოტანა
                if (window.innerWidth <= 1024) { // ტაბლეტისა და მობილურის ზომა
                    // შეტყობინების ელემენტის შექმნა თუ არ არსებობს
                    if (!$('.cart-notification').length) {
                        $('body').append('<div class="cart-notification"></div>');
                    }
                    
                    // შეტყობინების გამოტანა
                    $('.cart-notification')
                        .html('პროდუქტი დამატებულია კალათაში')
                        .addClass('show');

                    // შეტყობინების გაქრობა 3 წამში
                    setTimeout(function() {
                        $('.cart-notification').removeClass('show');
                    }, 3000);
                }

                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);

                $('.cart-detail-d14a59').addClass('updated');
                setTimeout(function() {
                    $('.cart-detail-d14a59').removeClass('updated');
                }, 1000);
            },
            error: function() {
                console.log('Ajax add to cart failed');
                $thisbutton.removeClass('loading');
                $('.cart-detail-d14a59').removeClass('active');
            }
        });
    });

    // Prevent default WooCommerce behavior
    $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        if ($button) {
            $button.removeClass('added');
        }
    });
});