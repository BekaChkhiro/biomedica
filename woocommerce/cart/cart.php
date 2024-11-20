<div class="w-full flex flex-col lg:flex-row justify-between gap-16 my-24">
    <div class="w-full lg:w-3/4 rounded-2xl">
        <div class="bg-white p-6 md:p-12 rounded-2xl">
            <div class="flex flex-row items-center gap-4">
                <h2 class="custom-cart-count-label text-4xl md:text-4xl">კალათა</h2>
                <span class="custom-cart-item-count text-2xl md:text-xl"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
            </div>

            <div class="flex flex-row p-4 mt-8 font-bold gap-8 text-sm md:text-lg" style="border-bottom: 1px solid rgba(227, 227, 227, 0.50); color: #9C9C9C;">
                <div class="w-6/12 md:w-6/12 text-xl">პროდუქტის სახელი</div>
                <div class="w-3/12 md:w-3/12 text-xl">რაოდენობა</div>
                <div class="w-3/12 md:w-3/12 text-xl">ფასი</div>
            </div>

            <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
                <div id="cart-items-container">
                    <?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $product = $cart_item['data'];
                        $product_id = $cart_item['product_id'];
                        $product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
                        
                        $thumbnail = $product->get_image();
                        $product_name = $product->get_name();
                        $product_price = $product->get_price();
                        $regular_price = $product->get_regular_price();
                        
                        $product_quantity = woocommerce_quantity_input( array(
                            'input_name'   => "cart[{$cart_item_key}][qty]",
                            'input_value'  => $cart_item['quantity'],
                            'max_value'    => $product->get_max_purchase_quantity(),
                            'min_value'    => '0',
                            'product_name' => $product->get_name(),
                            'input_class'  => 'text-sm w-16 cart-quantity-input',
                            'product_id'   => $product_id,
                            'cart_item_key'=> $cart_item_key
                        ), $product, false );
                        ?>
                        <div class="my-4 p-4 flex flex-row items-center justify-between gap-4 cart-item" data-cart-item-key="<?php echo $cart_item_key; ?>">
                            <div class="flex flex-row items-center gap-4 w-4/6 md:w-5/12">
                                <div class="flex-shrink-0 p-2 md:p-4 cart-product-item-image-bg w-3/12">
                                    <?php echo $thumbnail; ?>
                                </div>
                                <div class="flex-grow">
                                    <span class="text-lg md:text-lg font-bold product-name"><?php echo $product_name; ?></span>
                                </div>
                            </div>

                            <div class="md:w-3/12 desktop-quantity-input flex justify-items-start">
                                <?php echo $product_quantity; ?>
                            </div>

                            <div class="w-2/6 md:w-3/12 flex flex-row justify-between items-center">
                                <div class="flex-grow w-3/4">
                                    <?php
                                    if ( $product_price < $regular_price ) {
                                        echo '<span style="color: #00915A" class="font-bold text-2xl">' . wc_price( $product_price ) . '</span> ';
                                        echo '<span class="line-through font-bold text-2xl" style="color: #6B6B6B">' . wc_price( $regular_price ) . '</span>';
                                    } else {
                                        echo '<span class="font-bold text-2xl" style="color: #00915A">' . wc_price( $product_price ) . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="w-3/4 flex flex-row justify-center custom-cart-page-quantity">
                                    <?php
                                    echo apply_filters(
                                        'woocommerce_cart_item_remove_link',
                                        sprintf(
                                            '<a href="%s" class="remove text-red-500 cart-remove-item" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g id="hugeicons:delete-01">
                                                    <path id="Vector" d="M19.5 5.5L18.88 15.525C18.722 18.086 18.643 19.367 18 20.288C17.6826 20.7432 17.2739 21.1273 16.8 21.416C15.843 22 14.56 22 11.994 22C9.424 22 8.139 22 7.18 21.415C6.70589 21.1257 6.29721 20.7409 5.98 20.285C5.338 19.363 5.26 18.08 5.106 15.515L4.5 5.5M3 5.5H21M16.056 5.5L15.373 4.092C14.92 3.156 14.693 2.689 14.302 2.397C14.2151 2.33232 14.1232 2.27479 14.027 2.225C13.594 2 13.074 2 12.035 2C10.969 2 10.436 2 9.995 2.234C9.89752 2.28621 9.80453 2.34642 9.717 2.414C9.322 2.717 9.101 3.202 8.659 4.171L8.053 5.5" stroke="#4B4B4B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </g>
                                                </svg>
                                            </a>',
                                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                            esc_html__( 'Remove this item', 'woocommerce' ),
                                            esc_attr( $product->get_id() ),
                                            esc_attr( $cart_item_key )
                                        ),
                                        $cart_item_key
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="button mt-8 w-full md:w-auto" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" style="background-color: #00915b; color: white; border-radius: 20px">
						<span>განახლება</span>                               
                    </button>
                </div>
                <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
            </form>
        </div>
    </div>
    
    <div class="w-full lg:w-1/4 mt-6 lg:mt-0">
        <div class="bg-white p-6 md:p-12 rounded-2xl">
            <h3 class="text-2xl md:text-3xl font-bold">ღირებულება</h3>
            <div id="cart-totals" class="my-10 flex flex-col gap-2">
                <?php
                $cart_subtotal = WC()->cart->get_subtotal();
                $cart_items = WC()->cart->get_cart();
                $total_discount = 0;

                foreach ( $cart_items as $cart_item ) {
                    $product = $cart_item['data'];
                    $quantity = $cart_item['quantity'];

                    $original_price = $product->get_regular_price();
                    $sale_price = $product->get_price();
                    
                    $discount = ( $original_price - $sale_price ) * $quantity;
                    $total_discount += $discount;
                }

                $total = $cart_subtotal; // მთლიანი თანხა ტრანსპორტირების გარეშე
                ?>
                <div class="flex justify-between">
                    <span>პროდუქტის ღირებულება</span>
                    <span class="font-bold cart-subtotal"><?php echo wc_price($cart_subtotal); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>ფასდაკლება</span>
                    <span class="font-bold cart-discount" style="color: #ED1C24;"><?php echo wc_price($total_discount); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-2xl md:text-2xl font-bold">სულ გადასახდელი</span>
                    <span class="font-bold cart-total" style="color: #00915A; font-size: 24px;"><?php echo wc_price($total); ?></span>
                </div>
            </div>
            <a href="<?php echo wc_get_checkout_url(); ?>" class="button checkout wc-forward custom-mini-cart-checkout-button">
                <span>ყიდვა</span>
            </a>
        </div>
    </div>
</div>