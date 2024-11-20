<?php 
/**
 * Register/enqueue custom scripts and styles
 */
add_action( 'wp_enqueue_scripts', function() {
    // Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
    if ( ! bricks_is_builder_main() ) {
        wp_enqueue_style( 'bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime( get_stylesheet_directory() . '/style.css' ) );
    }
} );

/* tailwind */
/* მხოლოდ Tailwind */
function enqueue_tailwind() {
    wp_enqueue_style('tailwind-css', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', array(), null);
}
add_action('wp_enqueue_scripts', 'enqueue_tailwind');

function load_font_awesome() {
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' );
}
add_action( 'wp_enqueue_scripts', 'load_font_awesome' );

// AJAX Add to Cart functionality
function bricks_ajax_add_to_cart() {
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    $product_status = get_post_status($product_id);

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity) && 'publish' === $product_status) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
            wc_add_to_cart_message(array($product_id => $quantity), true);
        }

        WC_AJAX :: get_refreshed_fragments();
    } else {
        $data = array(
            'error' => true,
            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
        );

        wp_send_json($data);
    }

    wp_die();
}

add_action('wp_ajax_bricks_ajax_add_to_cart', 'bricks_ajax_add_to_cart');
add_action('wp_ajax_nopriv_bricks_ajax_add_to_cart', 'bricks_ajax_add_to_cart');

// Enqueue the custom script
function enqueue_bricks_ajax_add_to_cart_script() {
    wp_enqueue_script('bricks-ajax-add-to-cart', get_stylesheet_directory_uri() . '/js/ajax-add-to-cart.js', array('jquery'), '1.0', true);
    wp_localize_script('bricks-ajax-add-to-cart', 'bricks_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_bricks_ajax_add_to_cart_script');

// Modify Add to Cart button
add_filter('woocommerce_loop_add_to_cart_link', 'custom_loop_add_to_cart_link', 10, 2);
function custom_loop_add_to_cart_link($button, $product) {
    $desktop_svg = '<svg class="desktop-svg" width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g id="Group">
    <path id="Vector" d="M3.5 3.25H16L14.414 8.8C14.2945 9.21779 14.0422 9.58529 13.6952 9.84691C13.3483 10.1085 12.9255 10.25 12.491 10.25H5.791C5.29846 10.2503 4.82314 10.0688 4.45612 9.74035C4.0891 9.41189 3.85618 8.95955 3.802 8.47L3 1.25H1" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
    <g id="Group_2">
    <path id="Vector_2" d="M5.5 13.75C6.05228 13.75 6.5 13.3023 6.5 12.75C6.5 12.1977 6.05228 11.75 5.5 11.75C4.94772 11.75 4.5 12.1977 4.5 12.75C4.5 13.3023 4.94772 13.75 5.5 13.75Z" fill="white"/>
    <path id="Vector_3" d="M13.5 13.75C14.0523 13.75 14.5 13.3023 14.5 12.75C14.5 12.1977 14.0523 11.75 13.5 11.75C12.9477 11.75 12.5 12.1977 12.5 12.75C12.5 13.3023 12.9477 13.75 13.5 13.75Z" fill="white"/>
    </g>
    </g>
    </svg>';

    $mobile_svg = '<svg class="mobile-svg" xmlns="http://www.w3.org/2000/svg" width="17" height="14" viewBox="0 0 17 14" fill="none">
    <path d="M3.25 3.25H15.75L14.164 8.8C14.0445 9.21779 13.7922 9.58529 13.4452 9.84691C13.0983 10.1085 12.6755 10.25 12.241 10.25H5.541C5.04846 10.2503 4.57314 10.0688 4.20612 9.74035C3.8391 9.41189 3.60618 8.95955 3.552 8.47L2.75 1.25H0.75" stroke="#343434" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.25 13.75C5.80228 13.75 6.25 13.3023 6.25 12.75C6.25 12.1977 5.80228 11.75 5.25 11.75C4.69772 11.75 4.25 12.1977 4.25 12.75C4.25 13.3023 4.69772 13.75 5.25 13.75Z" fill="#343434"/>
    <path d="M13.25 13.75C13.8023 13.75 14.25 13.3023 14.25 12.75C14.25 12.1977 13.8023 11.75 13.25 11.75C12.6977 11.75 12.25 12.1977 12.25 12.75C12.25 13.3023 12.6977 13.75 13.25 13.75Z" fill="#343434"/>
    </svg>';

    return sprintf('<a href="%s" data-quantity="1" class="%s" %s data-product_id="%d" rel="nofollow">%s %s</a>',
        esc_url($product->add_to_cart_url()),
        esc_attr(implode(' ', array('button', 'product_type_' . $product->get_type(), $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '', 'ajax-add-to-cart'))),
        $product->get_type() == 'simple' ? 'aria-label="' . esc_attr__('Add to cart', 'woocommerce') . '"' : 'aria-label="' . esc_attr__('Select options', 'woocommerce') . '"',
        esc_attr($product->get_id()),
        $desktop_svg,
        $mobile_svg
    );
}

/* woocommerce custom js */
function enqueue_custom_js() {
    wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/woocommerce/js/script.js', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_js' );

/* custom mini cart */
function custom_update_cart_quantity() {
    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
    $quantity = intval( $_POST['quantity'] );

    if ( $quantity > 0 && WC()->cart->set_quantity( $cart_item_key, $quantity ) ) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action( 'wp_ajax_update_cart_quantity', 'custom_update_cart_quantity' );
add_action( 'wp_ajax_nopriv_update_cart_quantity', 'custom_update_cart_quantity' );

function refresh_mini_cart() {
    ob_start();
    wc_get_template( 'cart/mini-cart.php' );
    $mini_cart = ob_get_clean();
    wp_send_json_success( $mini_cart );
}
add_action( 'wp_ajax_refresh_mini_cart', 'refresh_mini_cart' );
add_action( 'wp_ajax_nopriv_refresh_mini_cart', 'refresh_mini_cart' );

function custom_enqueue_styles_and_scripts() {
    if ( !is_admin() ) {
        wp_enqueue_style( 'custom-mini-cart-style', get_stylesheet_directory_uri() . '/css/custom-style.css' );
        wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/js/custom-script.js', array( 'jquery' ), '', true );
        wp_localize_script( 'custom-script', 'wc_add_to_cart_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_styles_and_scripts' );

/* custom cart page */
// Register custom cart page template
function custom_cart_page_template( $template ) {
    if ( is_cart() ) {
        $custom_template = locate_template( 'woocommerce/cart/custom-cart.php' );
        if ( $custom_template ) {
            $template = $custom_template;
        }
    }
    return $template;
}
add_filter( 'woocommerce_locate_template', 'custom_cart_page_template', 10, 3 );

add_action( 'after_setup_theme', 'mytheme_setup' );
function mytheme_setup() {
    // ჩართული უნდა იყოს WooCommerce-ის მხარდაჭერა, თუ ის გამორთულია.
    add_theme_support('woocommerce');
}

/* payment move to billing form */
// გადახდის სექციის მოშორება Checkout-ის სტანდარტული ადგილიდან
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

add_filter( 'woocommerce_checkout_fields', 'custom_override_checkout_fields' );

function custom_override_checkout_fields( $fields ) {
    // მისამართის ველის სავალდებულოს გამორთვა
    $fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_address_2']['required'] = false;
    $fields['billing']['billing_city']['required'] = false;
    $fields['billing']['billing_postcode']['required'] = false;
    $fields['billing']['billing_state']['required'] = false;
    $fields['billing']['billing_country']['required'] = false;

    // ასევე გადახდის მისამართისთვის იგივე
    $fields['shipping']['shipping_address_1']['required'] = false;
    $fields['shipping']['shipping_address_2']['required'] = false;
    $fields['shipping']['shipping_city']['required'] = false;
    $fields['shipping']['shipping_postcode']['required'] = false;
    $fields['shipping']['shipping_state']['required'] = false;
    $fields['shipping']['shipping_country']['required'] = false;

    return $fields;
}

/** 
 * რეგისტრაცია ძიების ელემენტის
 */
add_action( 'init', function() {
    $element_files = [
      __DIR__ . '/elements/element-product-search.php',
    ];
  
    foreach ( $element_files as $file ) {
      \Bricks\Elements::register_element( $file );
    }
  }, 11 );
  
add_action( 'wp_ajax_product_search', 'ajax_product_search' );
add_action( 'wp_ajax_nopriv_product_search', 'ajax_product_search' );
  
function ajax_product_search() {
    // შემოწმება, რომ "query" პარამეტრი არ იყოს ცარიელი
    if ( ! isset( $_POST['query'] ) ) {
      wp_die();
    }
  
    $search_query = sanitize_text_field( $_POST['query'] );
  
    // WooCommerce პროდუქტების Query
    $args = [
      'post_type' => 'product',
      'posts_per_page' => 5,
      's' => $search_query
    ];
  
    $search_results = new WP_Query( $args );
  
    if ( $search_results->have_posts() ) {
      echo '<ul class="product-search-results">';
      while ( $search_results->have_posts() ) {
        $search_results->the_post();
        global $product;?>

        <li style="border-bottom: 1px solid #ddd;">
          <a href="<?php echo get_permalink(); ?>" class="flex justify-start items-center gap-4">
            <div class="cart-product-item-image-bg">
            <?php echo woocommerce_get_product_thumbnail(); ?>
            </div> 
            <span><?php echo get_the_title(); ?></span>
          </a>
        </li>

      <?php }
      echo '</ul>';
    } else {
      echo '<p>' . esc_html__( 'No products found', 'bricks' ) . '</p>';
    }
  
    wp_reset_postdata();
    wp_die();
}
  
/* product Carousel with category select and count */

// რეგისტრაცია ახალი ელემენტის functions.php-ში
add_action('init', function() {
  // რეგისტრირება custom-product-carousel ელემენტის
  $element_files = [
      __DIR__ . '/elements/product-carousel.php', // ელემენტის ფაილი
  ];

  foreach ($element_files as $file) {
      \Bricks\Elements::register_element($file); // ელემენტის რეგისტრაცია Bricks-ში
  }
}, 11);

// რეგისტრაცია ახალი ელემენტის functions.php-ში
add_action('init', function() {
  // რეგისტრირება custom-product-carousel ელემენტის
  $element_files = [
      __DIR__ . '/elements/related-product-carousel.php', // ელემენტის ფაილი
  ];

  foreach ($element_files as $file) {
      \Bricks\Elements::register_element($file); // ელემენტის რეგისტრაცია Bricks-ში
  }
}, 11);

// რეგისტრაცია ახალი ელემენტის functions.php-ში
add_action('init', function() {
  // რეგისტრირება custom-product-carousel ელემენტის
  $element_files = [
      __DIR__ . '/elements/element-products-archive.php', // ელემენტის ფაილი
  ];

  foreach ($element_files as $file) {
      \Bricks\Elements::register_element($file); // ელემენტის რეგისტრაცია Bricks-ში
  }
}, 11);

function enqueue_font_awesome() {
  wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');

function my_custom_woocommerce_locate_template($template, $template_name, $template_path) {
  $child_theme_path = get_stylesheet_directory() . '/woocommerce/' . $template_name;
  
  if (file_exists($child_theme_path)) {
      return $child_theme_path;
  }
  
  return $template;
}
add_filter('woocommerce_locate_template', 'my_custom_woocommerce_locate_template', 10, 3);

// custom shop page

// დაამატე pre_get_posts ჰუკი, რომ ჩართო პროდუქტის კატეგორიის ფილტრი
add_action( 'pre_get_posts', 'custom_woocommerce_product_filter' );

function custom_woocommerce_product_filter( $query ) {
    // ვამოწმებთ, მთავარ query-ში ვართ თუ არა და ესაა შოპის გვერდი
    if ( ! is_admin() && $query->is_main_query() && is_shop() ) {
        // ვამოწმებთ, არსებობს თუ არა 'b_product_cat' პარამეტრი GET მოთხოვნაში
        if ( isset( $_GET['b_product_cat'] ) && ! empty( $_GET['b_product_cat'] ) ) {
            $product_cat_ids = array_map( 'intval', $_GET['b_product_cat'] ); // აქ კატეგორიები აიღება GET მოთხოვნიდან
            
            // Tax Query-ის შექმნა კატეგორიების მიხედვით
            $tax_query = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $product_cat_ids, // GET-დან მიღებული კატეგორიების IDs
                    'operator' => 'IN',
                ),
            );
            
            // ვამატებთ tax_query-ს მთავარ query-ში
            $query->set( 'tax_query', $tax_query );
        }
    }
}

// Prevent updating button text after AJAX add to cart
add_filter('woocommerce_add_to_cart_fragments', 'remove_add_to_cart_button_text_update', 10, 1);
function remove_add_to_cart_button_text_update($fragments) {
    if (isset($fragments['a.added_to_cart'])) {
        unset($fragments['a.added_to_cart']);
    }
    return $fragments;
}

function remove_item_from_cart() {
    $cart_item_key = $_POST['cart_item_key'];

    if($cart_item_key) {
        WC()->cart->remove_cart_item($cart_item_key);
        
        $cart_count = WC()->cart->get_cart_contents_count();
        
        $data = array(
            'cart_count' => $cart_count,
            'cart_total' => WC()->cart->get_cart_subtotal(),
            'empty_cart_message' => __('თქვენ კალათში პროდუქტები არ არის.', 'woocommerce'),
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
        );

        if ($cart_count == 0) {
            $data['cart_content'] = '<p class="text-center text-gray-600">' . $data['empty_cart_message'] . '</p>';
        }

        wp_send_json_success($data);
    } else {
        wp_send_json_error();
    }
    
    wp_die();
}
add_action('wp_ajax_remove_item_from_cart', 'remove_item_from_cart');
add_action('wp_ajax_nopriv_remove_item_from_cart', 'remove_item_from_cart');


/* ajax filtre for shop page */

// Add theme support
function add_custom_scripts() {
    wp_enqueue_script('jquery');
    
    // Check if we're on shop or category page
    if (is_shop() || is_product_category()) {
        // Enqueue custom CSS
        wp_enqueue_style('custom-shop-styles', get_stylesheet_directory_uri() . '/assets/css/shop-styles.css', array(), '1.0.0');
        
        // Enqueue custom JS
        wp_enqueue_script('custom-shop-scripts', get_stylesheet_directory_uri() . '/assets/js/shop-scripts.js', array('jquery'), '1.0.0', true);
        
        // Localize script
        wp_localize_script('custom-shop-scripts', 'custom_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('filter_products_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'add_custom_scripts');

// Products per page
function set_posts_per_page_for_products($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category())) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'set_posts_per_page_for_products');

// AJAX handler for filtering
function filter_products_callback() {
    check_ajax_referer('filter_products_nonce', 'security');

    $categories = isset($_POST['categories']) ? array_map('absint', $_POST['categories']) : array();
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';
    $paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 12,
        'paged' => $paged,
        'post_status' => 'publish'
    );

    // Category filter
    if (!empty($categories)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $categories,
                'operator' => 'IN',
            )
        );
    }

    // Ordering filter
    switch ($orderby) {
        case 'popularity':
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            break;
        case 'rating':
            $args['meta_key'] = '_wc_average_rating';
            $args['orderby'] = 'meta_value_num';
            break;
        case 'date':
            $args['orderby'] = 'date';
            break;
        case 'price':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'menu_order';
    }

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            ?>
            <div class="custom-product-item p-8 bg-white rounded-2xl relative cursor-pointer" onclick="handleProductClick(event, '<?php the_permalink(); ?>')">
                <div class="product-thumbnail-wrapper relative">
                    <a href="<?php the_permalink(); ?>" class="product-link">
                        <div class="product-thumbnail">
                            <?php woocommerce_template_loop_product_thumbnail(); ?>
                        </div>
                    </a>
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
                    <a href="<?php the_permalink(); ?>" class="product-title">
                        <h2 class="text-xl sm:text-2xl lg:text-xl"><?php the_title(); ?></h2>
                    </a>
                    <div class="price-stock-wrapper flex justify-between items-center mt-4">
                        <div class="product-price text-[#00905a] font-semibold">
                            <?php woocommerce_template_loop_price(); ?>
                        </div>
                        <div class="product-stock-status">
                            <?php 
                            if (!$product->is_in_stock()) {
                                echo '<span class="stock-status out-of-stock text-red-600">არ არის მარაგში</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="lg:hidden flex justify-start gap-6 mobile-icons mt-6">
                    <div class="product-wishlist-icon">
                        <?php echo do_shortcode('[sw_wishlist_icon]'); ?>
                    </div>
                    <div class="custom-add-to-cart-button">
                        <?php woocommerce_template_loop_add_to_cart(); ?>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p class="text-center w-full">პროდუქტები არ მოიძებნა</p>';
    }

    $products = ob_get_clean();

    // Pagination
    ob_start();
    ?>
    <div class="w-full custom-pagination-container bg-white rounded-lg flex justify-between items-center">
        <div class="results-count">
            <?php
            $total = $query->found_posts;
            $per_page = 12;
            $current_page = $paged;
            
            // გამოვთვალოთ დიაპაზონი
            $start_range = (($current_page - 1) * $per_page) + 1;
            $end_range = min($current_page * $per_page, $total);
            
            // ტექსტი დიაპაზონისთვის
            echo "ნაჩვენებია {$start_range}-{$end_range} პროდუქტი {$total}-დან";
            ?>
        </div>
        
        <?php if ($query->max_num_pages > 1) : ?>
        <nav class="woocommerce-pagination">
            <ul class="page-numbers">
                <?php
                // წინა გვერდზე გადასვლა
                if ($current_page > 1) : ?>
                    <li><a class="prev page-numbers" href="?paged=<?php echo ($current_page - 1); ?>">←</a></li>
                <?php endif; ?>
                
                <?php
                $total_pages = $query->max_num_pages;
                
                // პირველი გვერდი
                if ($current_page > 2) : ?>
                    <li><a class="page-numbers" href="?paged=1">1</a></li>
                <?php endif; ?>
                
                <?php if ($current_page > 3) : ?>
                    <li><span class="page-numbers dots">...</span></li>
                <?php endif; ?>
                
                <?php
                // გამოვაჩინოთ გვერდები მიმდინარის გარშემო
                for ($i = max(1, $current_page - 1); $i <= min($total_pages, $current_page + 1); $i++) :
                    if ($i == $current_page) : ?>
                        <li><span class="page-numbers current"><?php echo $i; ?></span></li>
                    <?php else : ?>
                        <li><a class="page-numbers" href="?paged=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php endif;
                endfor; ?>
                
                <?php if ($current_page < $total_pages - 2) : ?>
                    <li><span class="page-numbers dots">...</span></li>
                    <li><a class="page-numbers" href="?paged=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a></li>
                <?php elseif ($current_page < $total_pages - 1) : ?>
                    <li><a class="page-numbers" href="?paged=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a></li>
                <?php endif; ?>
                
                <?php if ($current_page < $total_pages) : ?>
                    <li><a class="next page-numbers" href="?paged=<?php echo ($current_page + 1); ?>">→</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <?php
    $pagination = ob_get_clean();

    wp_reset_postdata();

    wp_send_json_success(array(
        'products' => $products,
        'pagination' => $pagination
    ));
}

add_action('wp_ajax_filter_products', 'filter_products_callback');
add_action('wp_ajax_nopriv_filter_products', 'filter_products_callback');

// Add this to your theme's JavaScript file or footer
add_action('wp_footer', function() {
    ?>
    <script>
    function handleProductClick(event, productUrl) {
        // Check if click was on buttons or links
        if (
            event.target.closest('.custom-add-to-cart-button') ||
            event.target.closest('.product-wishlist-icon') ||
            event.target.closest('.product-wishlist-icon-desktop') ||
            event.target.closest('a.product-link') ||
            event.target.closest('a.product-title')
        ) {
            // If clicked on button or link, let the default action happen
            return;
        }
        
        // If clicked elsewhere, navigate to product page
        window.location.href = productUrl;
    }
    </script>
    <?php
}, 99);

// Add shop classes to category pages
function add_shop_classes_to_category($classes) {
    if (is_product_category()) {
        $classes[] = 'woocommerce-shop';
    }
    return $classes;
}
add_filter('body_class', 'add_shop_classes_to_category');

// თემფლეითის პრიორიტეტის კონტროლი
function custom_template_include($template) {
    if (is_product_category()) {
        $new_template = locate_template(array('taxonomy-product_cat.php'));
        if (!empty($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'custom_template_include', 99);

// კატეგორიის ქუერის მოდიფიკაცია
function modify_product_category_query($query) {
    if (!is_admin() && is_product_category() && $query->is_main_query()) {
        $query->set('posts_per_page', 12);
        $query->set('orderby', isset($_GET['orderby']) ? $_GET['orderby'] : 'menu_order');
    }
}
add_action('pre_get_posts', 'modify_product_category_query');

// კატეგორიის გვერდზე პროდუქტების რაოდენობის კონტროლი
function set_product_category_posts_per_page($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_product_category() || is_shop()) {
            $query->set('posts_per_page', 12);
        }
    }
}
add_action('pre_get_posts', 'set_product_category_posts_per_page');

// კატეგორიის გვერდზე shop-ის კლასების დამატება
function add_shop_body_class($classes) {
    if (is_product_category()) {
        $classes[] = 'woocommerce';
        $classes[] = 'woocommerce-page';
        $classes[] = 'woocommerce-shop';
    }
    return $classes;
}
add_filter('body_class', 'add_shop_body_class');

// კატეგორიის გვერდზე სტილების და სკრიპტების ჩართვა
function enqueue_category_scripts() {
    if (is_product_category() || is_shop()) {
        wp_enqueue_style('custom-shop-styles', get_stylesheet_directory_uri() . '/assets/css/shop-styles.css', array(), '1.0.1');
        wp_enqueue_script('custom-shop-scripts', get_stylesheet_directory_uri() . '/assets/js/shop-scripts.js', array('jquery'), '1.0.1', true);
        
        wp_localize_script('custom-shop-scripts', 'custom_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('filter_products_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_category_scripts');

/* checkout */

function custom_cod_gateway_title($title) {
    if ($title === 'Cash on delivery') {
        return 'ადგილზე გადახდა';
    }
    return $title;
}
add_filter('woocommerce_gateway_title', 'custom_cod_gateway_title', 10, 1);

// Change payment description
function custom_cod_description($description) {
    if (strpos($description, 'Pay with cash upon delivery') !== false) {
        return 'გადაიხადეთ ნაღდი ფულით შეკვეთის მიღებისას';
    }
    return $description;
}
add_filter('woocommerce_gateway_description', 'custom_cod_description', 10, 1);

// Change privacy policy text
function custom_privacy_message() {
    $privacy_page_id = wc_privacy_policy_page_id();
    $privacy_link = $privacy_page_id ? '<a href="' . esc_url( get_permalink( $privacy_page_id ) ) . '" class="woocommerce-privacy-policy-link" target="_blank">კონფიდენციალურობის პოლიტიკა</a>' : '';
    
    $message = 'თქვენი პერსონალური მონაცემები გამოყენებული იქნება თქვენი შეკვეთის დასამუშავებლად, ამ ვებსაიტზე თქვენი გამოცდილების გასაუმჯობესებლად და სხვა მიზნებისთვის, რომლებიც აღწერილია ჩვენს ' . $privacy_link;
    
    return $message;
}
add_filter('woocommerce_get_privacy_policy_text', 'custom_privacy_message', 10, 1);

// Alternative filter if the above doesn't work
add_filter('woocommerce_checkout_privacy_policy_text', 'custom_privacy_message', 10, 1);

// Another alternative using direct text replacement
function custom_privacy_text_replace($translated_text, $text, $domain) {
    if ($domain === 'woocommerce') {
        $search_text = 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our';
        if ($text === $search_text) {
            $privacy_page_id = wc_privacy_policy_page_id();
            $privacy_link = $privacy_page_id ? '<a href="' . esc_url( get_permalink( $privacy_page_id ) ) . '" class="woocommerce-privacy-policy-link" target="_blank">კონფიდენციალურობის პოლიტიკა</a>' : '';
            
            return 'თქვენი პერსონალური მონაცემები გამოყენებული იქნება თქვენი შეკვეთის დასამუშავებლად, ამ ვებსაიტზე თქვენი გამოცდილების გასაუმჯობესებლად და სხვა მიზნებისთვის, რომლებიც აღწერილია ჩვენს ' . $privacy_link;
        }
    }
    return $translated_text;
}
add_filter('gettext', 'custom_privacy_text_replace', 20, 3);


/* shipping methods */


/* code for description */

function combine_product_descriptions($post_id) {
    // შევამოწმოთ არის თუ არა პროდუქტი
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // ავტომატური შენახვის დროს არ გაეშვას
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // შევამოწმოთ არის თუ არა POST მოთხოვნა რედაქტორიდან
    if (!isset($_POST['content']) && !isset($_POST['excerpt'])) {
        return;
    }

    $post = get_post($post_id);
    
    // მივიღოთ ახალი მნიშვნელობები POST-დან
    $short_description = isset($_POST['excerpt']) ? wp_kses_post($_POST['excerpt']) : $post->post_excerpt;
    $full_description = isset($_POST['content']) ? wp_kses_post($_POST['content']) : $post->post_content;
    
    // თუ ორივე ცარიელია, გამოვიდეთ ფუნქციიდან
    if (empty(trim($short_description)) && empty(trim($full_description))) {
        return;
    }
    
    // განვაახლოთ პოსტი remove_action-ის გარეშე უსასრულო ციკლის თავიდან ასაცილებლად
    remove_action('save_post_product', 'combine_product_descriptions', 20);
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_excerpt' => $short_description,
        'post_content' => $full_description
    ));
    
    // დავაბრუნოთ action
    add_action('save_post_product', 'combine_product_descriptions', 20);
}

// დავამატოთ ქმედება პროდუქტის შენახვისას
add_action('save_post_product', 'combine_product_descriptions', 20);


/* checkout form and shipping methods */

// ფუნქცია მიწოდების საფასურის დასამატებლად
add_action('woocommerce_cart_calculate_fees', function() {
    if (!is_checkout()) return;
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (did_action('woocommerce_cart_calculate_fees') >= 2) return;

    // მივიღოთ არჩეული მეთოდი და ქალაქი
    $delivery_method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'delivery';
    $chosen_city = isset($_POST['billing_city_select']) ? sanitize_text_field($_POST['billing_city_select']) : '';

    // თუ აფთიაქიდან გატანაა, საფასური არ ემატება
    if ($delivery_method === 'pickup') {
        return;
    }

    // მივიღოთ კალათის ჯამური თანხა
    $cart_total = WC()->cart->subtotal;

    // თბილისის შემთხვევა
    if ($chosen_city === 'Tbilisi') {
        if ($cart_total >= 100) {
            WC()->cart->add_fee('მიწოდება თბილისში', 0, true);
        } else {
            WC()->cart->add_fee('მიწოდება თბილისში', 5, true);
        }
    } 
    // სხვა ქალაქების შემთხვევა
    elseif (!empty($chosen_city) && $chosen_city !== 'Tbilisi') {
        WC()->cart->add_fee('მიწოდება რეგიონში', 10, true);
    }
}, 10);


// AJAX handler მიწოდების მეთოდის განახლებისთვის
add_action('wp_ajax_update_shipping_method', 'handle_shipping_method_update');
add_action('wp_ajax_nopriv_update_shipping_method', 'handle_shipping_method_update');
function handle_shipping_method_update() {
    if (isset($_POST['delivery_method']) && isset($_POST['city'])) {
        WC()->session->set('chosen_delivery_method', sanitize_text_field($_POST['delivery_method']));
        WC()->session->set('chosen_city', sanitize_text_field($_POST['city']));
        WC()->cart->calculate_totals();
        
        wp_send_json_success([
            'total' => WC()->cart->get_total(),
            'fragments' => apply_filters('woocommerce_update_order_review_fragments', array())
        ]);
    }
    wp_die();
}

// მიწოდების საფასურის დამატება
add_action('woocommerce_cart_calculate_fees', function() {
    if (!is_checkout()) return;
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (did_action('woocommerce_cart_calculate_fees') >= 2) return;

    $delivery_method = isset($_POST['delivery_method']) ? $_POST['delivery_method'] : 'delivery';
    $chosen_city = isset($_POST['billing_city_select']) ? $_POST['billing_city_select'] : '';
    
    // აფთიაქიდან გატანის შემთხვევაში საფასური არ ემატება
    if ($delivery_method === 'pickup') {
        return;
    }

    // კალათის ჯამური თანხა
    $cart_total = WC()->cart->subtotal;

    if (!empty($chosen_city)) {
        if ($chosen_city === 'Tbilisi') {
            if ($cart_total >= 100) {
                WC()->cart->add_fee('მიწოდება თბილისში', 0);
            } else {
                WC()->cart->add_fee('მიწოდება თბილისში', 5);
            }
        } else {
            WC()->cart->add_fee('მიწოდება რეგიონში', 10);
        }
    }
});

/* shipping amount for checkout page */

// Register custom shipping method
add_action('woocommerce_shipping_init', 'custom_shipping_method_init');

function custom_shipping_method_init() {
    class Custom_Shipping_Method extends WC_Shipping_Method {
        public function __construct() {
            $this->id                 = 'custom_shipping';
            $this->method_title       = 'Custom Shipping';
            $this->method_description = 'Custom shipping method for city-based delivery';
            $this->enabled           = 'yes';
            $this->title             = 'მიწოდების საფასური';
            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        public function calculate_shipping($package = array()) {
            $delivery_method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'delivery';
            $chosen_city = isset($_POST['billing_city_select']) ? sanitize_text_field($_POST['billing_city_select']) : '';
            $cart_total = WC()->cart->subtotal;

            $cost = 0;

            if ($delivery_method === 'delivery') {
                if ($chosen_city === 'Tbilisi') {
                    if ($cart_total < 100) {
                        $cost = 5;
                    }
                } elseif (!empty($chosen_city)) {
                    $cost = 10;
                }
            }

            if ($cost > 0) {
                $rate = array(
                    'id'      => $this->id,
                    'label'   => $this->title,
                    'cost'    => $cost,
                    'package' => $package,
                );
                $this->add_rate($rate);
            }
        }
    }
}

// Add shipping method to WooCommerce
add_filter('woocommerce_shipping_methods', 'add_custom_shipping_method');

function add_custom_shipping_method($methods) {
    $methods['custom_shipping'] = 'Custom_Shipping_Method';
    return $methods;
}

// Modify cart total to include shipping
add_filter('woocommerce_calculated_total', 'add_shipping_to_total', 10, 2);

function add_shipping_to_total($total, $cart) {
    $delivery_method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'delivery';
    $chosen_city = isset($_POST['billing_city_select']) ? sanitize_text_field($_POST['billing_city_select']) : '';
    
    $shipping_cost = 0;
    if ($delivery_method === 'delivery') {
        if ($chosen_city === 'Tbilisi' && $cart->subtotal < 100) {
            $shipping_cost = 5;
        } elseif (!empty($chosen_city) && $chosen_city !== 'Tbilisi') {
            $shipping_cost = 10;
        }
    }
    
    return $total + $shipping_cost;
}

// Save delivery details to session
add_action('wp_ajax_save_delivery_details', 'save_delivery_details_to_session');
add_action('wp_ajax_nopriv_save_delivery_details', 'save_delivery_details_to_session');

function save_delivery_details_to_session() {
    if (isset($_POST['delivery_method'])) {
        WC()->session->set('chosen_delivery_method', sanitize_text_field($_POST['delivery_method']));
    }
    if (isset($_POST['city'])) {
        WC()->session->set('chosen_city', sanitize_text_field($_POST['city']));
    }
    
    // Recalculate shipping and totals
    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();
    
    wp_die();
}

// Update order review on checkout
add_action('woocommerce_checkout_update_order_review', 'update_shipping_on_order_review');

function update_shipping_on_order_review($post_data) {
    parse_str($post_data, $data);
    
    $delivery_method = isset($data['delivery_method']) ? $data['delivery_method'] : 'delivery';
    $chosen_city = isset($data['billing_city_select']) ? $data['billing_city_select'] : '';
    
    WC()->session->set('chosen_delivery_method', $delivery_method);
    WC()->session->set('chosen_city', $chosen_city);
}

// Add JavaScript to handle updates
add_action('wp_footer', 'update_checkout_script');

function update_checkout_script() {
    if (!is_checkout()) return;
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        var updateTimeout;
        
        function updateShipping() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(function() {
                var method = $('input[name="delivery_method"]:checked').val();
                var city = $('#billing_city_select').val();
                
                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.ajax_url,
                    data: {
                        'action': 'save_delivery_details',
                        'delivery_method': method,
                        'city': city
                    },
                    success: function() {
                        $('body').trigger('update_checkout');
                    }
                });
            }, 300);
        }

        $(document).on('change', 'input[name="delivery_method"], #billing_city_select', function() {
            updateShipping();
        });
        
        // Update on page load
        updateShipping();
    });
    </script>
    <?php
}

// Save delivery details to order
add_action('woocommerce_checkout_update_order_meta', 'save_delivery_details_to_order');

function save_delivery_details_to_order($order_id) {
    if (isset($_POST['delivery_method'])) {
        update_post_meta($order_id, '_delivery_method', sanitize_text_field($_POST['delivery_method']));
    }
    if (isset($_POST['billing_city_select'])) {
        update_post_meta($order_id, '_delivery_city', sanitize_text_field($_POST['billing_city_select']));
    }
}

// Add shipping fee to review order totals
add_action('woocommerce_review_order_before_order_total', 'add_shipping_to_review_order');

function add_shipping_to_review_order() {
    $delivery_method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'delivery';
    $chosen_city = isset($_POST['billing_city_select']) ? sanitize_text_field($_POST['billing_city_select']) : '';
    $cart_total = WC()->cart->subtotal;
    
    $shipping_cost = 0;
    if ($delivery_method === 'delivery') {
        if ($chosen_city === 'Tbilisi' && $cart_total < 100) {
            $shipping_cost = 5;
        } elseif (!empty($chosen_city) && $chosen_city !== 'Tbilisi') {
            $shipping_cost = 10;
        }
    }
    
    if ($shipping_cost > 0) {
        ?>
        <tr class="shipping-fee">
            <th>მიწოდების საფასური</th>
            <td colspan="2"><?php echo wc_price($shipping_cost); ?></td>
        </tr>
        <?php
    }
}

function remove_page_titles() {
    if (is_page()) {
        add_filter('the_title', 'hide_page_title');
    }
}
add_action('wp_head', 'remove_page_titles');

function hide_page_title($title) {
    if (is_page() && in_the_loop()) {
        return '';
    }
    return $title;
}

/* bricks singular product template debuug */

/* morbenali striqoni */

// ოფციების გვერდის დამატება ადმინ მენიუში
function running_text_menu() {
    add_menu_page(
        'მორბენალი სტრიქონი',
        'მორბენალი სტრიქონი',
        'manage_options',
        'running-text-settings',
        'running_text_settings_page',
        'dashicons-text'
    );
}
add_action('admin_menu', 'running_text_menu');

// დავამატოთ CSS სტილები ადმინ გვერდისთვის
function running_text_admin_styles() {
    ?>
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #00915a;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .switch-status {
            margin-left: 10px;
            display: inline-block;
            vertical-align: middle;
        }
    </style>
    <?php
}
add_action('admin_head', 'running_text_admin_styles');

// ოფციების რეგისტრაცია
function running_text_settings_init() {
    register_setting('running_text_options', 'running_text_settings');

    add_settings_section(
        'running_text_section',
        'მორბენალი სტრიქონის პარამეტრები',
        'running_text_section_callback',
        'running-text-settings'
    );

    add_settings_field(
        'running_text_field_text',
        'სტრიქონის ტექსტი',
        'running_text_field_text_callback',
        'running-text-settings',
        'running_text_section'
    );

    add_settings_field(
        'running_text_field_speed',
        'სიჩქარე (წამებში)',
        'running_text_field_speed_callback',
        'running-text-settings',
        'running_text_section'
    );

    add_settings_field(
        'running_text_field_spacing',
        'დაშორება (პიქსელებში)',
        'running_text_field_spacing_callback',
        'running-text-settings',
        'running_text_section'
    );

    add_settings_field(
        'running_text_field_enabled',
        'სტატუსი',
        'running_text_field_enabled_callback',
        'running-text-settings',
        'running_text_section'
    );
}
add_action('admin_init', 'running_text_settings_init');

// სექციის კოლბექი
function running_text_section_callback() {
    echo '<p>მორბენალი სტრიქონის პარამეტრების კონფიგურაცია</p>';
}

// ველების კოლბექები
function running_text_field_text_callback() {
    $options = get_option('running_text_settings');
    $value = isset($options['text']) ? $options['text'] : '';
    echo '<input type="text" name="running_text_settings[text]" value="' . esc_attr($value) . '" class="regular-text">';
}

function running_text_field_speed_callback() {
    $options = get_option('running_text_settings');
    $value = isset($options['speed']) ? $options['speed'] : '20';
    echo '<input type="number" name="running_text_settings[speed]" value="' . esc_attr($value) . '" min="1" max="100">';
}

function running_text_field_spacing_callback() {
    $options = get_option('running_text_settings');
    $value = isset($options['spacing']) ? $options['spacing'] : '100';
    echo '<input type="number" name="running_text_settings[spacing]" value="' . esc_attr($value) . '" min="0" max="500">';
}

function running_text_field_enabled_callback() {
    $options = get_option('running_text_settings');
    $checked = isset($options['enabled']) ? $options['enabled'] : '0';
    ?>
    <label class="switch">
        <input type="checkbox" name="running_text_settings[enabled]" value="1" <?php checked(1, $checked, true); ?>>
        <span class="slider"></span>
    </label>
    <span class="switch-status">
        <span class="enabled-text" style="color: #00915a; <?php echo $checked ? '' : 'display: none;'; ?>">ჩართულია</span>
        <span class="disabled-text" style="color: #999; <?php echo $checked ? 'display: none;' : ''; ?>">გამორთულია</span>
    </span>

    <script>
        jQuery(document).ready(function($) {
            var checkbox = $('input[name="running_text_settings[enabled]"]');
            var enabledText = $('.enabled-text');
            var disabledText = $('.disabled-text');

            checkbox.change(function() {
                if (this.checked) {
                    enabledText.show();
                    disabledText.hide();
                } else {
                    enabledText.hide();
                    disabledText.show();
                }
            });
        });
    </script>
    <?php
}

// ოფციების გვერდის HTML
function running_text_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('running_text_options');
            do_settings_sections('running-text-settings');
            submit_button('პარამეტრების შენახვა');
            ?>
        </form>
    </div>
    <?php
}

// მორბენალი სტრიქონის ჩვენების ფუნქცია
function display_running_text() {
    $options = get_option('running_text_settings');
    
    if (!isset($options['enabled']) || $options['enabled'] != '1') {
        return;
    }

    $text = isset($options['text']) ? $options['text'] : 'მორბენალი სტრიქონის ტექსტი';
    $speed = isset($options['speed']) ? intval($options['speed']) : 20;
    $spacing = isset($options['spacing']) ? intval($options['spacing']) : 100;

    echo "<div class='bricks-marquee' style='background-color: #00915a; padding: 10px 0px;'>";
    echo "<div class='bricks-marquee-content' style='color: white;'></div>";
    echo "</div>";
    
    echo "<style>
    .bricks-marquee {
        width: 100%;
        overflow: hidden;
    }
    .bricks-marquee-content {
        display: inline-flex;
        white-space: nowrap;
        animation: bricks-marquee {$speed}s linear infinite;
    }
    .marquee-item {
        display: inline-block;
        padding-right: {$spacing}px;
    }
    @keyframes bricks-marquee {
        0% { transform: translate(0, 0); }
        100% { transform: translate(-100%, 0); }
    }
    </style>";

    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        var marqueeContent = document.querySelector('.bricks-marquee-content');
        var containerWidth = document.querySelector('.bricks-marquee').offsetWidth;
        var itemWidth = {$spacing};
        var repeatCount = Math.ceil(containerWidth / itemWidth) + 1;
        
        for (var i = 0; i < repeatCount; i++) {
            var span = document.createElement('span');
            span.className = 'marquee-item';
            span.textContent = '" . esc_js($text) . "';
            marqueeContent.appendChild(span);
        }
        
        var contentWidth = marqueeContent.offsetWidth;
        var animationDuration = (contentWidth / containerWidth) * {$speed};
        marqueeContent.style.animationDuration = animationDuration + 's';
    });
    </script>";
}

// შორთკოდის დამატება
function running_text_shortcode() {
    ob_start();
    display_running_text();
    return ob_get_clean();
}
add_shortcode('running_text', 'running_text_shortcode');

/* product descriptions styles */

// Apply the content filters to product short description
function apply_content_filters_to_short_description($short_description) {
    // Remove the default filters
    remove_filter('woocommerce_short_description', 'wptexturize');
    remove_filter('woocommerce_short_description', 'wpautop');
    remove_filter('woocommerce_short_description', 'shortcode_unautop');
    remove_filter('woocommerce_short_description', 'do_shortcode');

    // Apply the default WordPress content filters
    $short_description = wptexturize($short_description);
    $short_description = convert_smilies($short_description);
    $short_description = convert_chars($short_description);
    $short_description = wpautop($short_description);
    $short_description = shortcode_unautop($short_description);
    $short_description = do_shortcode($short_description);

    return $short_description;
}
add_filter('woocommerce_short_description', 'apply_content_filters_to_short_description', 99);

// Preserve styles in product description
function preserve_product_description_styles($description) {
    // Add style preservation
    if (!is_admin()) {
        // Remove empty paragraphs
        $description = str_replace('<p></p>', '', $description);
        
        // Ensure proper spacing between paragraphs
        $description = wpautop($description, true);
        
        // Preserve existing HTML classes and styles
        add_filter('safe_style_css', function($styles) {
            return array_merge($styles, array(
                'display',
                'margin',
                'padding',
                'font-size',
                'line-height',
                'text-align',
                'color',
                'background',
                'border',
                'width',
                'height'
            ));
        });
    }
    
    return $description;
}
add_filter('the_content', 'preserve_product_description_styles', 20);
add_filter('woocommerce_short_description', 'preserve_product_description_styles', 20);

// Add custom CSS to ensure proper spacing
function add_product_description_spacing() {
    if (!is_admin()) {
        ?>
        <style>
            .woocommerce-product-details__short-description p,
            .woocommerce-product-details__short-description ul,
            .woocommerce-product-details__short-description ol {
                margin-bottom: 1em;
            }
            .woocommerce-product-details__short-description p:last-child {
                margin-bottom: 0;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'add_product_description_spacing');

