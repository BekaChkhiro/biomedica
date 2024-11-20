<?php
if ( ! defined( 'ABSPATH' ) ) exit; // პირდაპირი წვდომისგან დაცვა

class Custom_Products_Archive extends \Bricks\Element {

    public $name = 'products-archive';
    public $icon = 'ti-shopping-cart'; // შეგიძლიათ შეცვალოთ თქვენი სურვილისამებრ
    public $category = 'woocommerce';
    
    // ელემენტის ლეიბელი
    public function get_label() {
        return esc_html__( 'Products Archive', 'bricks' );
    }

    // ელემენტის კონტროლები
    public function set_controls() {
        $this->controls['order_by'] = [
            'label' => esc_html__( 'Order By', 'bricks' ),
            'type' => 'select',
            'options' => [
                'popularity' => esc_html__( 'Popularity', 'bricks' ),
                'rating' => esc_html__( 'Rating', 'bricks' ),
                'date' => esc_html__( 'Newest', 'bricks' ),
                'price' => esc_html__( 'Price: Low to High', 'bricks' ),
                'price_desc' => esc_html__( 'Price: High to Low', 'bricks' ),
            ],
            'default' => 'date',
        ];

        $this->controls['posts_per_page'] = [
            'label' => esc_html__( 'Products Per Page', 'bricks' ),
            'type' => 'number',
            'default' => 9,
        ];
    }

    // პროდუქტების query
    public function render() {
        $order_by = $this->settings['order_by'] ?? 'date';
        $posts_per_page = $this->settings['posts_per_page'] ?? 9;
        
        // WP_Query პარამეტრები პროდუქტების გამოსატანად
        $query_args = [
            'post_type' => 'product',
            'posts_per_page' => $posts_per_page,
            'orderby' => $order_by,
            'order' => 'ASC',
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        ];

        // Query-ის წამოწყება
        $query = new WP_Query($query_args);

        // პროდუქტების გამოტანა
        if ($query->have_posts()) : 
            echo '<div class="products-archive-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">'; // ან <div class="grid grid-cols-3 gap-8"> Tailwind-ით
            
            while ($query->have_posts()) : $query->the_post(); 
                ?>
                <div class="custom-product-item p-8 bg-white rounded-2xl relative" style="display: flex; flex-direction: column; height: 100%;"> <!-- Flexbox სტილი -->
                    <a href="<?php the_permalink(); ?>" class="product-link" style="flex-grow: 1;">
                        <div class="product-thumbnail" style="flex-grow: 1;">
                            <?php woocommerce_template_loop_product_thumbnail(); ?>
                            <div class="product-icons relative inset-0 flex justify-center items-center opacity-0 transition-opacity duration-300" style="margin-top: -50px; margin-bottom: 10px;">
                                <div class="flex space-x-4 w-full flex justify-center items-center product-icons-div-a">
                                    <div class="custom-add-to-cart-button">
                                        <?php woocommerce_template_loop_add_to_cart(); ?>
                                    </div>
                                    <?php echo do_shortcode('[sw_wishlist_icon]'); ?>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="product-details">
                        <a href="<?php the_permalink(); ?>" class="product-title text-base text-black font-semibold" style="margin-top: 20px;">
                            <?php woocommerce_template_loop_product_title(); ?>
                        </a>
                        <div class="product-price font-semibold" style="color: #00905a; font-size: 16px; margin-top: 20px;">
                            <?php woocommerce_template_loop_price(); ?>
                        </div>
                    </div>
                </div>
                <?php
            endwhile;
            echo '</div>';

            // Pagination გამოტანა
            echo paginate_links([
                'total' => $query->max_num_pages,
            ]);

        else :
            echo esc_html__( 'No products found', 'bricks' );
        endif;

        wp_reset_postdata();
    }
}
