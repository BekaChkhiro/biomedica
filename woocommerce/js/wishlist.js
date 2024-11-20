jQuery(document).ready(function($) {
    $('#custom-wishlist-button').on('click', function(e) {
        e.preventDefault();

        var product_id = $(this).data('product-id');
        var button = $(this);

        $.ajax({
            url: yith_wcwl_l10n.ajax_url,
            method: 'POST',
            data: {
                action: 'yith_wcwl_add_to_wishlist',
                add_to_wishlist: product_id,
            },
            beforeSend: function() {
                button.prop('disabled', true).text('დამატება...');
            },
            success: function(response) {
                if (response.result == 'true') {
                    button.addClass('added').text('დამატებულია!');
                } else {
                    button.text('გადაფასება');
                }
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
});
