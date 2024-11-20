<?php defined('ABSPATH') || exit; ?>

<table class="shop_table woocommerce-checkout-review-order-table bg-white p-8 rounded-xl">
    <thead>
        <tr class="flex flex-row items-center gap-2">
            <th class="custom-cart-count-label" style="padding: 12px;">კალათა</th>
            <th class="custom-cart-item-count" style="padding: 2px;"><?php echo WC()->cart->get_cart_contents_count(); ?></th>
        </tr>
    </thead>
    <tbody>
        <thead>
            <tr>
                <th class="product-name"><?php esc_html_e('პროდუქტი', 'woocommerce'); ?></th>
                <th class="product-name"><?php esc_html_e('რაოდენობა', 'woocommerce'); ?></th>
                <th class="product-total"><?php esc_html_e('ჯამი', 'woocommerce'); ?></th>
            </tr>
        </thead>
        
        <?php do_action('woocommerce_review_order_before_cart_contents');

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                ?>
                <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                    <td class="product-name flex flex-row gap-6 items-center">
                        <div class="w-1/6 p-6 cart-product-item-image-bg">
                            <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key)); ?>
                        </div>
                        <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;'; ?>
                    </td>
                    <td>
                        <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); ?>
                        <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                    </td>
                    <td class="product-total">
                        <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                    </td>
                </tr>
                <?php
            }
        }

        do_action('woocommerce_review_order_after_cart_contents'); ?>
    </tbody>
    <tfoot>
        <tr class="cart-subtotal">
            <th><?php esc_html_e('შუალედური ჯამი', 'woocommerce'); ?></th>
            <td colspan="2"><?php wc_cart_totals_subtotal_html(); ?></td>
        </tr>

        <!-- მიწოდების ინფორმაციის სექცია -->
        <tr class="delivery-info" id="delivery-info-row">
            <th>მიწოდების საფასური</th>
            <td colspan="2" id="delivery-info-content">
                <?php
                $delivery_method = isset($_POST['delivery_method']) ? $_POST['delivery_method'] : 'delivery';
                $chosen_city = isset($_POST['billing_city_select']) ? $_POST['billing_city_select'] : '';
                $cart_total = WC()->cart->subtotal;

                if ($delivery_method === 'pickup') {
                    echo '<span class="free-pickup">გაიტანეთ აფთიაქიდან</span>';
                } elseif ($chosen_city === 'Tbilisi') {
                    if ($cart_total >= 100) {
                        echo '<span class="free-delivery">უფასო მიწოდება თბილისში</span>';
                    } else {
                        $remaining = 100 - $cart_total;
                        echo '<span class="delivery-cost">მიწოდება თბილისში: 5₾</span>';
                        echo '<div class="delivery-note">უფასო მიწოდება ქალაქში ხელმისაწვდომია 100 ლარზე მეტ შენაძენზე</div>';
                    }
                } elseif (!empty($chosen_city)) {
                    echo '<span class="delivery-cost">მიწოდება რეგიონში: 10₾</span>';
                }
                ?>
            </td>
        </tr>

        <?php foreach (WC()->cart->get_fees() as $fee) : ?>
            <tr class="fee">
                <th><?php echo esc_html($fee->name); ?></th>
                <td colspan="2"><?php wc_cart_totals_fee_html($fee); ?></td>
            </tr>
        <?php endforeach;

        do_action('woocommerce_review_order_before_order_total'); ?>

        <tr class="order-total">
            <th><?php esc_html_e('სულ', 'woocommerce'); ?></th>
            <td colspan="2"><?php wc_cart_totals_order_total_html(); ?></td>
        </tr>

        <?php do_action('woocommerce_review_order_after_order_total'); ?>
    </tfoot>
</table>

<style>
.delivery-info {
    background: #f8f8f8;
}

.delivery-info td {
    padding: 15px !important;
}

.free-pickup {
    color: #00905a;
    font-weight: 600;
}

.free-delivery {
    color: #00905a;
    font-weight: 600;
}

.delivery-cost {
    font-weight: 600;
}

.delivery-note {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
    font-style: italic;
}

/* მობილური ვერსიისთვის */
@media (max-width: 768px) {
    .woocommerce-checkout-review-order-table td,
    .woocommerce-checkout-review-order-table th {
        padding: 10px 5px;
    }
    
    .cart-product-item-image-bg {
        width: 60px !important;
        padding: 5px !important;
    }
}
</style>

<script>
jQuery(function($) {
    // მიწოდების ინფორმაციის განახლება
    function updateDeliveryInfo() {
        var method = $('input[name="delivery_method"]:checked').val();
        var city = $('#billing_city_select').val();
        var cartTotal = parseFloat(<?php echo WC()->cart->subtotal; ?>);
        var content = '';

        // სათაურის განახლება მეთოდის მიხედვით
        var titleText = method === 'pickup' ? 'აფთიაქიდან გატანა' : 'მიწოდების საფასური';
        $('#delivery-info-row th').text(titleText);

        if (method === 'pickup') {
            content = '<span class="free-pickup">გაიტანეთ აფთიაქიდან</span>';
        } else if (city === 'Tbilisi') {
            if (cartTotal >= 100) {
                content = '<span class="free-delivery">უფასო მიწოდება თბილისში</span>';
            } else {
                content = '<span class="delivery-cost">მიწოდება თბილისში: 5₾</span>' +
                         '<div class="delivery-note">უფასო მიწოდება ქალაქში ხელმისაწვდომია 100 ლარზე მეტ შენაძენზე</div>';
            }
        } else if (city) {
            content = '<span class="delivery-cost">მიწოდება რეგიონში: 10₾</span>';
        }

        $('#delivery-info-content').html(content);
    }

    // ივენთების მოსმენა
    $(document).on('change', 'input[name="delivery_method"], #billing_city_select', function() {
        updateDeliveryInfo();
        $(document.body).trigger('update_checkout');
    });

    // WooCommerce-ის განახლების შემდეგ
    $(document.body).on('updated_checkout', function() {
        updateDeliveryInfo();
    });

    // საწყისი მდგომარეობა
    updateDeliveryInfo();
});
</script>