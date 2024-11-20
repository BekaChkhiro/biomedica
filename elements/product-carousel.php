<?php
if (!defined('ABSPATH')) exit;

class Custom_Product_Carousel extends \Bricks\Element {
    public $category = 'woocommerce';
    public $name = 'Product-Carousel';
    public $icon = 'ti-shopping-cart';

    public function set_controls() {
        $this->controls['category'] = [
            'label' => esc_html__('Product Category', 'bricks'),
            'type' => 'select',
            'options' => $this->get_product_categories(),
            'default' => '',
            'placeholder' => esc_html__('Select a category', 'bricks'),
        ];

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
    }

    public function get_product_categories() {
        $categories = get_terms('product_cat');
        $options = [];
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                $options[$category->slug] = $category->name;
            }
        }
        return $options;
    }

    private function get_stock_status_html($product) {
        $stock_status = $product->get_stock_status();
        if ($stock_status === 'outofstock') {
            return '<span class="stock-status out-of-stock">არ არის მარაგში</span>';
        }
        return '';
    }

    public function render() {
        $element_id = 'carousel-' . uniqid();
        $category_slug = isset($this->settings['category']) ? $this->settings['category'] : '';
        $products_count = isset($this->settings['products_count']) ? $this->settings['products_count'] : 8;
        $cards_per_row = isset($this->settings['cards_per_row']) ? $this->settings['cards_per_row'] : 3;

        $view_all_text = isset($this->settings['view_all_text']) ? $this->settings['view_all_text'] : 'იხილეთ სრულად';
        $view_all_font_size = isset($this->settings['view_all_font_size']) ? $this->settings['view_all_font_size'] : 16;
        $view_all_text_color = isset($this->settings['view_all_text_color']) ? $this->settings['view_all_text_color'] : '#000000';
        $view_all_font_family = isset($this->settings['view_all_font_family']) ? $this->settings['view_all_font_family'] : 'inherit';

        if (empty($category_slug)) {
            echo '<p>' . esc_html__('Please select a category.', 'bricks') . '</p>';
            return;
        }

        $category = get_term_by('slug', $category_slug, 'product_cat');

        // Query for featured products first
        $featured_args = [
            'post_type' => 'product',
            'posts_per_page' => $products_count,
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $category_slug
                ],
                [
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => 'featured'
                ]
            ],
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        $featured_query = new WP_Query($featured_args);
        $featured_product_ids = wp_list_pluck($featured_query->posts, 'ID');
        
        // Query for regular products if needed
        $remaining_count = $products_count - count($featured_product_ids);
        $all_products = $featured_query->posts;
        
        if ($remaining_count > 0) {
            $regular_args = [
                'post_type' => 'product',
                'posts_per_page' => $remaining_count,
                'tax_query' => [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => $category_slug
                    ]
                ],
                'post__not_in' => $featured_product_ids,
                'orderby' => 'date',
                'order' => 'DESC'
            ];
            
            $regular_query = new WP_Query($regular_args);
            $all_products = array_merge($all_products, $regular_query->posts);
        }

        echo '<div class="category-header w-full" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-right: 80px;">';
        echo '<a href="' . esc_url(get_term_link($category)) . '"><h2 class="category-name" style="font-size: 24px;">' . esc_html($category->name) . '</h2></a>';
        echo '<a href="' . esc_url(get_term_link($category)) . '" class="view-all-button" style="text-decoration: none; font-size: ' . esc_attr($view_all_font_size) . 'px; color: ' . esc_attr($view_all_text_color) . '; font-family: ' . esc_attr($view_all_font_family) . ';">' . esc_html($view_all_text) . '</a>';
        echo '</div>';

        echo '<div id="' . esc_attr($element_id) . '" class="custom-product-carousel" style="overflow: hidden; position: relative;">'; 
        echo '<div class="carousel-gradient-overlay"></div>';
        ?>
        <button class="my-prev rounded-xl p-8 text-center" style="display: none; background-color: #f2f2f2; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 20;">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="my-next rounded-xl p-8 text-center" style="background-color: #f2f2f2; position: absolute; right: 80px; top: 50%; transform: translateY(-50%); z-index: 20;">
            <i class="fas fa-chevron-right"></i>
        </button>

        <?php if (!empty($all_products)): ?>
            <div class="carousel-track" style="display: flex; transition: transform 0.3s ease; align-items: stretch;">
                <?php 
                foreach ($all_products as $post):
                    setup_postdata($GLOBALS['post'] =& $post);
                    global $product;
                    ?>
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
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No products found</p>
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

            @media (max-width: 768px) {
                .sale-badge {
                    font-size: 12px;
                    padding: 3px 6px;
                }
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

            /* Optional: Add currency symbol styling */
            .custom-price-display .woocommerce-Price-currencySymbol {
                font-size: 0.8em;
                position: relative;
                top: -0.1em;
            }
            .custom-product-item {
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .custom-product-item:hover {
                box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
                transform: translateY(-4px);
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
                background: rgba(255, 255, 255, 0.5);
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
                    background-color: rgb(242, 242, 242) !important;
                    border: none !important;
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

                .mobile-icons * {
                    pointer-events: all !important;
                }

                .xoo-wsc-container {
                    z-index: 999999 !important;
                }

                .xoo-wsc-modal {
                    z-index: 999998 !important;
                }

                .xoo-wsc-overlay {
                    z-index: 999997 !important;
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