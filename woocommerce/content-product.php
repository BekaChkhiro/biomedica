<?php
/**
 * The template for displaying product content within loops.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
    <div class="custom-product-item p-8 bg-white rounded-2xl">
        <a href="<?php the_permalink(); ?>" class="product-link">
            <div class="product-thumbnail">
                <?php woocommerce_template_loop_product_thumbnail(); ?>
                <div class="custom-buttons-hover">
                    <div class="custom-add-to-cart-button">
                        <?php woocommerce_template_loop_add_to_cart(); ?>
                                                
                    </div>
                    <?php echo do_shortcode('[sw_wishlist_icon]'); ?>
                </div>
            </div>
        </a>
        <div class="product-details" >
            <a href="<?php the_permalink(); ?>" class="product-title text-base text-black font-semibold" style="marging-top: 20px;">
                <?php woocommerce_template_loop_product_title(); ?>
            </a>
            <div class="product-price font-semibold" style="color: #00905a; font-size: 16px; margin-top: 20px;">
                <?php woocommerce_template_loop_price(); ?>
            </div>
            
        </div>
    </div>
</li>
