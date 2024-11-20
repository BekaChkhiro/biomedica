<?php
if (!defined('ABSPATH')) exit;

class Custom_Related_Product_Carousel extends \Bricks\Element {
    public $category = 'woocommerce';
    public $name = 'Related-Product-Carousel';
    public $icon = 'ti-shopping-cart';

    public function set_controls() {
        $this->controls['products_count'] = [
            'label' => esc_html__('Products Count', 'bricks'),
            'type' => 'number',
            'default' => 4,
        ];

        $this->controls['cards_per_row'] = [
            'label' => esc_html__('Cards Per Row', 'bricks'),
            'type' => 'number',
            'min' => 1,
            'max' => 6,
            'default' => 3,
        ];

        $this->controls['arrows_color'] = [
            'label' => esc_html__('Arrows Color', 'bricks'),
            'type' => 'color',
            'default' => '#000000',
        ];

        $this->controls['arrows_bg'] = [
            'label' => esc_html__('Arrows Background', 'bricks'),
            'type' => 'color',
            'default' => '#ffffff',
        ];

        $this->controls['arrows_padding'] = [
            'label' => esc_html__('Arrows Padding', 'bricks'),
            'type' => 'number',
            'default' => 10,
        ];
    }

    private function get_stock_status_html($product) {
        $stock_status = $product->get_stock_status();
        if ($stock_status === 'outofstock') {
            return '<span class="stock-status out-of-stock">არ არის მარაგში</span>';
        }
        return '';
    }

    public function render() {
        global $product;
        
        if (!$product) {
            echo '<p>' . esc_html__('No related products found', 'bricks') . '</p>';
            return;
        }

        $element_id = 'carousel-' . uniqid();
        $products_count = isset($this->settings['products_count']) ? $this->settings['products_count'] : 4;
        $cards_per_row = isset($this->settings['cards_per_row']) ? $this->settings['cards_per_row'] : 3;

        $related_ids = wc_get_related_products($product->get_id(), $products_count);

        if (empty($related_ids)) {
            echo '<p>' . esc_html__('No related products found', 'bricks') . '</p>';
            return;
        }

        $args = [
            'post_type' => 'product',
            'posts_per_page' => $products_count,
            'post__in' => $related_ids,
            'orderby' => 'post__in'
        ];
        $related_products = new WP_Query($args);

        echo '<div id="' . esc_attr($element_id) . '" class="custom-product-carousel" style="overflow: hidden; position: relative;">'; 
        echo '<div class="carousel-gradient-overlay"></div>';
        ?>
        <button class="my-prev rounded-xl p-8 text-center" style="display: none; background-color: #f2f2f2; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 20;">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="my-next rounded-xl p-8 text-center" style="background-color: #f2f2f2; position: absolute; right: 80px; top: 50%; transform: translateY(-50%); z-index: 20;">
            <i class="fas fa-chevron-right"></i>
        </button>

        <?php if ($related_products->have_posts()): ?>
            <div class="carousel-track" style="display: flex; transition: transform 0.3s ease; align-items: stretch;">
                <?php while ($related_products->have_posts()): $related_products->the_post();
                    global $product; ?>
                    <div class="carousel-item" style="flex: 0 0 calc(100% / <?php echo esc_attr($cards_per_row); ?>); box-sizing: border-box;">
                        <div class="custom-product-item p-8 bg-white rounded-2xl relative" style="display: flex; flex-direction: column; height: 100%;">
                            <div class="product-thumbnail-wrapper relative" style="flex-grow: 1;">
                                <?php
                                // Add sale badge
                                if ($product->is_on_sale()) {
                                    $regular_price = $product->get_regular_price();
                                    $sale_price = $product->get_sale_price();
                                    if ($regular_price > 0) {
                                        $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                                        echo '<div class="sale-badge">-' . $percentage . '%</div>';
                                    }
                                }
                                ?>
                                <a href="<?php the_permalink(); ?>" class="product-link">
                                    <div class="product-thumbnail">
                                        <?php woocommerce_template_loop_product_thumbnail(); ?>
                                    </div>
                                </a>
                                <!-- Desktop Buttons -->
                                <div class="product-icons-desktop hidden lg:block">
                                    <div class="product-icons-overlay"></div>
                                    <div class="product-icons-buttons">
                                        <div class="custom-add-to-cart-button">
                                            <?php woocommerce_template_loop_add_to_cart(); ?>
                                        </div>
                                        <div class="product-wishlist-icon-desktop">
                                            <?php echo do_shortcode('[sw_wishlist_icon]'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="product-details mt-4">
                                <a href="<?php the_permalink(); ?>" class="product-title text-base text-black font-semibold">
                                    <h2 class="text-xl sm:text-2xl lg:text-xl font-normal" style="color: #343434;"><?php the_title(); ?></h2>
                                </a>
                                <div class="price-stock-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                                    <div class="product-price font-semibold custom-price-display">
                                        <?php
                                        $regular_price = $product->get_regular_price();
                                        $sale_price = $product->get_sale_price();
                                        
                                        if ($sale_price) {
                                            echo '<span class="sale-price">' . wc_price($sale_price) . '</span>';
                                            echo '<span class="regular-price">' . wc_price($regular_price) . '</span>';
                                        } else {
                                            echo '<span class="regular-price">' . wc_price($regular_price) . '</span>';
                                        }
                                        ?>
                                    </div>
                                    <div class="product-stock-status">
                                        <?php echo $this->get_stock_status_html($product); ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Mobile Buttons -->
                            <div class="lg:hidden flex justify-start gap-6 mobile-icons mt-6">
                                <div class="product-wishlist-icon">
                                    <?php echo do_shortcode('[sw_wishlist_icon]'); ?>
                                </div>
                                <div class="custom-add-to-cart-button">
                                    <?php woocommerce_template_loop_add_to_cart(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No related products found</p>
        <?php endif; ?>
        </div>

        <!-- JavaScript for carousel -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let currentIndex = 0;
                const track = document.querySelector("#<?php echo esc_js($element_id); ?> .carousel-track");
                const items = document.querySelectorAll("#<?php echo esc_js($element_id); ?> .carousel-item");
                const itemWidth = items[0].offsetWidth;
                const totalItems = items.length;
                const visibleItems = <?php echo esc_js($cards_per_row); ?>;
                const prevBtn = document.querySelector("#<?php echo esc_js($element_id); ?> .my-prev");
                const nextBtn = document.querySelector("#<?php echo esc_js($element_id); ?> .my-next");

                let startX, moveX, isDragging = false;
                let animationId = null;
                let clickStartTime;
                const clickThreshold = 200;

                function updateCarousel(instant = false) {
                    const targetX = -(itemWidth * currentIndex);
                    if (instant) {
                        track.style.transition = "none";
                        track.style.transform = `translateX(${targetX}px)`;
                        track.offsetHeight;
                        track.style.transition = "transform 0.3s ease";
                    } else {
                        cancelAnimationFrame(animationId);
                        function animate() {
                            const currentX = parseFloat(track.style.transform.replace("translateX(", "").replace("px)", "") || 0);
                            const diff = targetX - currentX;
                            if (Math.abs(diff) > 0.5) {
                                track.style.transform = `translateX(${currentX + diff * 0.2}px)`;
                                animationId = requestAnimationFrame(animate);
                            } else {
                                track.style.transform = `translateX(${targetX}px)`;
                            }
                        }
                        animate();
                    }

                    prevBtn.style.display = currentIndex === 0 ? "none" : "block";
                    nextBtn.style.display = currentIndex >= totalItems - visibleItems ? "none" : "block";
                }

                function handleGesture() {
                    if (moveX - startX > 50 && currentIndex > 0) {
                        currentIndex--;
                    } else if (startX - moveX > 50 && currentIndex < totalItems - visibleItems + 0.5) {
                        currentIndex++;
                    }
                    updateCarousel();
                }

                track.addEventListener("mousedown", e => {
                    startX = e.pageX - track.offsetLeft;
                    isDragging = true;
                    clickStartTime = new Date().getTime();
                    track.style.cursor = "grabbing";
                });

                document.addEventListener("mousemove", e => {
                    if (!isDragging) return;
                    e.preventDefault();
                    moveX = e.pageX - track.offsetLeft;
                    const walk = moveX - startX;
                    track.style.transform = `translateX(${-(itemWidth * currentIndex) + walk}px)`;
                });

                document.addEventListener("mouseup", e => {
                    if (isDragging) {
                        isDragging = false;
                        track.style.cursor = "grab";
                        const clickEndTime = new Date().getTime();
                        if (clickEndTime - clickStartTime < clickThreshold) {
                            const link = e.target.closest("a");
                            if (link) link.click();
                        } else {
                            handleGesture();
                        }
                    }
                });

                track.addEventListener("touchstart", e => {
                    startX = e.touches[0].pageX - track.offsetLeft;
                    clickStartTime = new Date().getTime();
                });

                track.addEventListener("touchmove", e => {
                    moveX = e.touches[0].pageX - track.offsetLeft;
                    const walk = moveX - startX;
                    track.style.transform = `translateX(${-(itemWidth * currentIndex) + walk}px)`;
                });

                track.addEventListener("touchend", e => {
                    const clickEndTime = new Date().getTime();
                    if (clickEndTime - clickStartTime < clickThreshold) {
                        const link = e.target.closest("a");
                        if (link) link.click();
                    } else {
                        handleGesture();
                    }
                });

                prevBtn.addEventListener("click", () => {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateCarousel();
                    }
                });

                nextBtn.addEventListener("click", () => {
                    if (currentIndex < totalItems - visibleItems + 0.5) {
                        currentIndex++;
                        updateCarousel();
                    }
                });

                updateCarousel(true);
            });
        </script>

        <!-- Styles -->
        <style>
        
        /* Sale Badge Styles */
            .sale-badge {
                position: absolute;
                top: 0px;
                right: 0px;
                background-color: #E31E24;
                color: white;
                padding: 4px 8px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                z-index: 5;
                min-width: 45px;
                text-align: center;
            }

            /* Ensure badge is visible on hover */
            .product-thumbnail-wrapper:hover .sale-badge {
                z-index: 10;
            }

            /* Custom Price Display Styles */
            .custom-price-display {
                display: flex;
                align-items: center;
                gap: 8px;
                font-family: inherit;
            }

            .custom-price-display .sale-price {
                color: #FF0000;
                font-size: 18px;
                font-weight: bold;
                order: 1;
            }

            .custom-price-display .regular-price {
                color: #00905a;
                font-size: 16px;
                text-decoration: line-through;
                order: 2;
            }

            /* When there's no sale price */
            .custom-price-display .regular-price:only-child {
                color: #00905a;
                text-decoration: none;
                font-size: 18px;
                font-weight: bold;
            }

            /* Currency symbol styling */
            .custom-price-display .woocommerce-Price-currencySymbol {
                font-size: 0.8em;
                position: relative;
                top: -0.1em;
            }

            @media (max-width: 768px) {
                .sale-badge {
                    font-size: 12px;
                    padding: 3px 6px;
                }
            }
        
            .custom-product-item {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.custom-product-item:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

            #<?php echo esc_attr($element_id); ?> {
    position: relative;
    overflow: hidden;
}

.product-thumbnail-wrapper {
    position: relative;
    overflow: hidden;
}

/* Desktop Buttons */
.product-icons-desktop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.product-thumbnail-wrapper:hover .product-icons-desktop {
    opacity: 1;
}

.product-icons-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    pointer-events: none;
    z-index: 1;
}

.product-icons-buttons {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    pointer-events: all;
    z-index: 2;
}

.product-icons-buttons .button,
.product-icons-buttons .sw-wishlist-icon {
    margin: 0 !important;
    min-width: auto !important;
    padding: 8px 8px !important;
    border-radius: 4px !important;
    background: none!important;
}


/* Mobile Styles */
@media (max-width: 1024px) {
    .product-icons-desktop {
        display: none !important;
    }
    
    .mobile-icons {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        z-index: 5;
    }
    
    .mobile-icons .custom-add-to-cart-button,
    .mobile-icons .product-wishlist-icon {
        position: relative;
        z-index: 6;
    }

    .mobile-icons .button,
    .mobile-icons .sw-wishlist-icon {
        margin: 0 !important;
        min-width: auto !important;
        padding: 8px 8px !important;
        border-radius: 50% !important;
        background-color: rgb(242, 242, 242) !important; /* დავამატეთ ბექგრაუნდი */
        border: none !important; /* წავშალეთ ბორდერი */
    }

    .custom-add-to-cart-button {
        background-color: rgb(242, 242, 242) !important;
    }
    
    .my-next {
        right: 10px !important;
    }
    
    .quantity {
        display: none !important;
    }

    /* Ensure all buttons are clickable */
    .mobile-icons * {
        pointer-events: all !important;
    }

    /* მინი კალათის z-index-ის გაზრდა */
    .xoo-wsc-container {
        z-index: 999999 !important;
    }

    .xoo-wsc-modal {
        z-index: 999998 !important;
    }

    /* ოვერლეის z-index-ის გაზრდა */
    .xoo-wsc-overlay {
        z-index: 999997 !important;
    }

    .mobile-icons {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        z-index: 5;
    }
    
    .mobile-icons .custom-add-to-cart-button,
    .mobile-icons .product-wishlist-icon {
        position: relative;
        z-index: 6;
    }
}

/* Desktop Specific */
@media (min-width: 1025px) {
    #<?php echo esc_attr($element_id); ?> {
        padding-right: calc(100% / <?php echo esc_attr($cards_per_row); ?> / 2);
    }
    
    #<?php echo esc_attr($element_id); ?> .carousel-gradient-overlay {
        display: block;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: calc(100% / <?php echo esc_attr($cards_per_row); ?> / 2 + 1px);
        background: linear-gradient(to right, 
                    rgba(255,255,255,0) 0%,
                    rgba(255,255,255,0.7) 50%, 
                    rgba(255,255,255,1) 100%);
        z-index: 10;
        pointer-events: none;
    }
    
    #<?php echo esc_attr($element_id); ?> .carousel-track {
        margin-right: calc(-100% / <?php echo esc_attr($cards_per_row); ?> / 2);
    }
    
    #<?php echo esc_attr($element_id); ?> .my-next {
        right: calc(100% / <?php echo esc_attr($cards_per_row); ?> / 4);
    }
    
    .category-header {
        padding-right: calc(100% / <?php echo esc_attr($cards_per_row); ?> / 2) !important;
    }
}

/* General Styles */
#<?php echo esc_attr($element_id); ?> .carousel-track {
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

#<?php echo esc_attr($element_id); ?> .carousel-track:active {
    cursor: grabbing;
}

.carousel-item {
    padding: 0 10px;
}

.product-thumbnail {
    transition: transform 0.3s ease;
}

.stock-status {
    font-size: 12px;
    font-weight: normal;
}

.out-of-stock {
    color: red;
}

.price-stock-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Mobile and Tablet Versions */
@media (max-width: 767px) {
    #<?php echo esc_attr($element_id); ?> .carousel-item {
        flex: 0 0 calc(100% / 1.5) !important;
    }
    
    .category-header {
        padding-right: 10px !important;
    }
    
    #<?php echo esc_attr($element_id); ?> {
        padding-right: 0;
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    #<?php echo esc_attr($element_id); ?> .carousel-item {
        flex: 0 0 calc(100% / 2.5) !important;
    }
    
    .category-header {
        padding-right: 10px !important;
    }
    
    #<?php echo esc_attr($element_id); ?> {
        padding-right: 0;
    }
}

/* Navigation Buttons */
.my-prev,
.my-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 20;
    background-color: #f2f2f2;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
        </style>
        <?php
        wp_reset_postdata();
    }
}