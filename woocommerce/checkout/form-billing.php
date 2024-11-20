<?php
defined('ABSPATH') || exit;

// Add shipping fee calculation
add_action('woocommerce_cart_calculate_fees', 'add_custom_shipping_fee');

function add_custom_shipping_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Clear existing fees
    $cart->remove_all_fees();

    // Get delivery method and city
    $delivery_method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'delivery';
    $chosen_city = isset($_POST['billing_city_select']) ? sanitize_text_field($_POST['billing_city_select']) : '';

    // Calculate shipping fee
    if ($delivery_method === 'delivery') {
        if ($chosen_city === 'Tbilisi') {
            if ($cart->subtotal < 100) {
                $cart->add_fee('მიწოდების საფასური', 5);
            }
        } elseif (!empty($chosen_city)) {
            $cart->add_fee('მიწოდების საფასური', 10);
        }
    }
}

// Add AJAX handler for shipping updates
add_action('wp_ajax_update_shipping_fee', 'update_shipping_fee_callback');
add_action('wp_ajax_nopriv_update_shipping_fee', 'update_shipping_fee_callback');

function update_shipping_fee_callback() {
    WC()->cart->calculate_totals();
    wp_die();
}
?>

<div class="woocommerce-billing-fields">
    <?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>

    <div class="woocommerce-billing-fields__field-wrapper">
        <?php
        $billing_fields = array(
            'billing_first_name' => array(
                'label'       => 'სახელი',
                'placeholder' => 'თქვენი სახელი',
                'required'    => true,
                'class'       => array('form-row-first'),
            ),
            'billing_last_name' => array(
                'label'       => 'გვარი',
                'placeholder' => 'თქვენი გვარი',
                'required'    => true,
                'class'       => array('form-row-last'),
            ),
            'billing_phone' => array(
                'label'       => 'ტელეფონი',
                'placeholder' => 'თქვენი ტელეფონის ნომერი',
                'required'    => true,
                'class'       => array('form-row-wide'),
            ),
            'billing_email' => array(
                'label'       => 'ელ-ფოსტა',
                'placeholder' => 'თქვენი ელ-ფოსტა',
                'required'    => true,
                'class'       => array('form-row-wide'),
            )
        );

        foreach ($billing_fields as $key => $field) {
            $field_data = array_merge($checkout->get_checkout_fields('billing')[$key], $field);
            woocommerce_form_field($key, $field_data, $checkout->get_value($key));
        }
        ?>
    </div>

    <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
</div>

<!-- მიწოდების მეთოდის სექცია -->
<div class="woocommerce-shipping-fields">
    <h3>მიწოდების მეთოდი</h3>
    
    <div id="delivery_method_wrapper" class="form-row form-row-wide delivery-method-wrapper">
        <div class="delivery-method-buttons">
            <label class="delivery-method-button">
                <input type="radio" name="delivery_method" value="delivery" checked>
                <span class="button-content">
                    <span class="method-label">მიწოდება</span>
                </span>
            </label>
            <label class="delivery-method-button">
                <input type="radio" name="delivery_method" value="pickup">
                <span class="button-content">
                    <span class="method-label">აფთიაქიდან გატანა</span>
                </span>
            </label>
        </div>
    </div>

    <!-- მიწოდების დეტალების სექცია -->
    <div id="delivery_details_wrapper" class="delivery-details-wrapper">
        <!-- ქალაქის არჩევა -->
        <div id="city_select_wrapper" class="form-row form-row-wide city-select-wrapper">
            <label for="billing_city_select">აირჩიეთ ქალაქი</label>
            <select name="billing_city_select" id="billing_city_select" class="select">
                <option value="">აირჩიეთ ქალაქი</option>
                <option value="Tbilisi">თბილისი</option>
                <option value="Batumi">ბათუმი</option>
                <option value="Kutaisi">ქუთაისი</option>
                <option value="Rustavi">რუსთავი</option>
                <option value="Zugdidi">ზუგდიდი</option>
                <option value="Telavi">თელავი</option>
                <option value="Poti">ფოთი</option>
                <option value="Gori">გორი</option>
                <option value="Bakuriani">ბაკურიანი</option>
                <option value="Borjomi">ბორჯომი</option>
                <option value="Kazbegi">ყაზბეგი</option>
                <option value="Mtskheta">მცხეთა</option>
                <option value="Senaki">სენაკი</option>
                <option value="Martvili">მარტვილი</option>
                <option value="Sighnaghi">სიღნაღი</option>
                <option value="Lagodekhi">ლაგოდეხი</option>
                <option value="Gurjaani">გურჯაანი</option>
                <option value="Khashuri">ხაშური</option>
                <option value="Oni">ონი</option>
                <option value="Aspindza">ასპინძა</option>
                <option value="Tskaltubo">წყალტუბო</option>
                <option value="Ambrolauri">ამბროლაური</option>
                <option value="Dedoplistskaro">დედოფლისწყარო</option>
                <option value="Ozurgeti">ოზურგეთი</option>
                <option value="Khoni">ხონი</option>
                <option value="Tsageri">ცაგერი</option>
                <option value="Vani">ვანი</option>
                <option value="Kobuleti">ქობულეთი</option>
                <option value="Chokhatauri">ჩოხატაური</option>
                <option value="Sachkhere">საჩხერე</option>
                <option value="Tkibuli">ტყიბული</option>
                <option value="Chiatura">ჭიათურა</option>
            </select>
        </div>

        <!-- მისამართი -->
        <div id="address_wrapper" class="form-row form-row-wide address-wrapper">
            <label for="billing_address_1">მისამართი</label>
            <input type="text" class="input-text" name="billing_address_1" id="billing_address_1" placeholder="თქვენი მისამართი" value="<?php echo esc_attr($checkout->get_value('billing_address_1')); ?>">
        </div>
    </div>
</div>

<style>
/* მიწოდების მეთოდის სტილები */
.woocommerce-shipping-fields {
    margin-top: 40px !important;
    padding: 30px !important;
    background: #f7f7f7 !important;
    border-radius: 8px !important;
}

.woocommerce-shipping-fields h3 {
    margin-bottom: 20px !important;
    padding-bottom: 15px !important;
    border-bottom: 1px solid #ddd !important;
}

.delivery-method-wrapper {
    margin-bottom: 30px !important;
}

.delivery-method-buttons {
    display: flex !important;
    gap: 15px !important;
}

.delivery-method-button {
    flex: 1 !important;
    position: relative !important;
    cursor: pointer !important;
    margin: 0 !important;
}

.delivery-method-button input[type="radio"] {
    position: absolute !important;
    opacity: 0 !important;
}

.button-content {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    padding: 15px 20px !important;
    background: #fff !important;
    border: 2px solid #ddd !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
}

.delivery-method-button input[type="radio"]:checked + .button-content {
    border-color: #00905a !important;
    background: #00905a !important;
    color: #fff !important;
}

.method-icon {
    font-size: 20px !important;
}

.method-label {
    font-size: 16px !important;
    font-weight: 500 !important;
}

.delivery-details-wrapper {
    padding-top: 20px !important;
    border-top: 1px solid #eee !important;
}

.city-select-wrapper,
.address-wrapper {
    margin-bottom: 20px !important;
}

.city-select-wrapper select,
.address-wrapper input {
    width: 100% !important;
    padding: 10px !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
}

@media (max-width: 768px) {
    .delivery-method-buttons {
        flex-direction: column !important;
    }
    
    .delivery-method-button {
        width: 100% !important;
    }
}

.delivery-details-wrapper {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out !important;
}

.delivery-details-wrapper.show {
    max-height: 500px;
}
</style>

<script>
jQuery(function($) {
    function updateDeliveryFields() {
        var method = $('input[name="delivery_method"]:checked').val();
        var detailsWrapper = $('#delivery_details_wrapper');
        var city = $('#billing_city_select').val();
        var cartTotal = parseFloat(<?php echo WC()->cart->subtotal; ?>);
        
        if (method === 'delivery') {
            detailsWrapper.addClass('show');
            $('#billing_city_select, #billing_address_1').prop('required', true);
        } else {
            detailsWrapper.removeClass('show');
            $('#billing_city_select, #billing_address_1').prop('required', false);
        }

        // Send AJAX request to update shipping fee
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                'action': 'update_shipping_fee',
                'delivery_method': method,
                'billing_city_select': city
            },
            success: function() {
                $('body').trigger('update_checkout');
            }
        });
    }

    // Event listeners
    $(document).on('change', 'input[name="delivery_method"]', updateDeliveryFields);
    $(document).on('change', '#billing_city_select', updateDeliveryFields);

    // Update delivery info display
    $(document.body).on('updated_checkout', function() {
        var method = $('input[name="delivery_method"]:checked').val();
        var city = $('#billing_city_select').val();
        var cartTotal = parseFloat(<?php echo WC()->cart->subtotal; ?>);
        var infoContent = '';

        if (method === 'pickup') {
            infoContent = '<span class="free-delivery">აფთიაქიდან გატანა უფასოა</span>';
        } else if (city === 'Tbilisi') {
            if (cartTotal >= 100) {
                infoContent = '<span class="free-delivery">უფასო მიწოდება თბილისში</span>';
            } else {
                var remaining = 100 - cartTotal;
                infoContent = '<span class="shipping-cost">მიწოდება თბილისში: 5₾</span>' +
                            '<div class="delivery-note">კიდევ ' + remaining.toFixed(2) + '₾ და მიიღებთ უფასო მიწოდებას</div>';
            }
        } else if (city) {
            infoContent = '<span class="shipping-cost">მიწოდება რეგიონში: 10₾</span>';
        }

        $('#shipping-info-content').html(infoContent);
    });

    // Initialize
    updateDeliveryFields();
});
</script>