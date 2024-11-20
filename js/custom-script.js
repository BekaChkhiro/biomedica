jQuery(document).ready(function($) {
    $('.custom-mini-cart').on('change', '.product-quantity', function() {
        var $input = $(this);
        var cart_item_key = $input.closest('.cart-item').data('cart-item-key');
        var quantity = $input.val();

        $.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'update_cart_quantity',
                cart_item_key: cart_item_key,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    $.ajax({
                        url: wc_add_to_cart_params.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'refresh_mini_cart'
                        },
                        success: function(response) {
                            $('.custom-mini-cart').replaceWith(response.data);
                        }
                    });
                } else {
                    alert('Error updating cart');
                }
            }
        });
    });
});

/* product Carousel with category select and count */

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const mySlider = window.bricksData?.splideInstances['YOUR_SLIDER_ID']; // ჩაანაცვლე შენი კარუსელის ID
        const prevButton = document.querySelector('.my-prev');
        const nextButton = document.querySelector('.my-next');

        if (mySlider && prevButton && nextButton) {
            prevButton.addEventListener('click', function() {
                mySlider.go('-1'); // მარცხენა ისრისთვის
            });
            nextButton.addEventListener('click', function() {
                mySlider.go('+1'); // მარჯვენა ისრისთვის
            });
        }
    }, 250);
});






