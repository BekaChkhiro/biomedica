jQuery(document).ready(function($) {
    $('.custom-cart').on('change', '.product-quantity', function() {
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
                    location.reload(); // გადატვირთავს გვერდს კალათის განახლების შემდეგ
                } else {
                    alert('Error updating cart');
                }
            }
        });
    });
});
