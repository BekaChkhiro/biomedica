<?php
/**
 * The Template for displaying product archives
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// ლიმიტის დაყენება 12 პროდუქტზე
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && is_shop()) {
        $query->set('posts_per_page', 12);
    }
});

// კატეგორიების იერარქიული ფუნქცია
function get_product_categories_hierarchical($parent = 0, $current_cat_id = null, $selected_cats = array()) {
    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => $parent
    );
    
    $categories = get_terms($args);
    $html = '';
    
    if (!empty($categories)) {
        foreach ($categories as $cat) {
            $subcategories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent' => $cat->term_id
            ));
            
            $has_children = !empty($subcategories);
            $unique_id = 'cat-' . $cat->term_id;
            $is_current = $current_cat_id && $current_cat_id == $cat->term_id;
            $is_selected = in_array($cat->term_id, $selected_cats);
            
            $html .= '<div class="category-item' . ($is_current ? ' current-cat' : '') . '">';
            $html .= '<div class="category-header">';
            
            // Checkbox
            $html .= '<label class="category-checkbox">';
            $html .= '<input type="checkbox" name="product_categories[]" value="' . $cat->term_id . '" ' . 
                    ($is_selected ? 'checked' : '') . ' class="category-filter-checkbox">';
            $html .= '<span class="checkmark"></span>';
            $html .= '</label>';
            
            if ($has_children) {
                $html .= '<button type="button" class="toggle-subcategories' . ($is_current || $is_selected ? ' active' : '') . '" data-target="' . $unique_id . '">';
                $html .= '<svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
                $html .= '<polyline points="9 18 15 12 9 6"></polyline>';
                $html .= '</svg>';
                $html .= '</button>';
            } else {
                $html .= '<div class="spacer-for-arrow"></div>';
            }
            
            $html .= '<span class="category-name">' . $cat->name . '</span>';
            $html .= '<span class="product-count">(' . $cat->count . ')</span>';
            $html .= '</div>';
            
            if ($has_children) {
                $html .= '<div id="' . $unique_id . '" class="subcategories' . ($is_current || $is_selected ? ' active' : '') . '">';
                $html .= get_product_categories_hierarchical($cat->term_id, $current_cat_id, $selected_cats);
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
    }
    
    return $html;
}

?>

<div class="mt-20 lg:mt-0 custom-products-archive-main w-full flex flex-col lg:gap-20">
    <div class="w-full flex flex-col gap-6">
        <h1 class="text-3xl font-bold text-black">
            <?php 
            if (is_shop()) {
                echo 'მედიკამენტები';
            } elseif (is_product_category()) {
                single_cat_title();
            } else {
                the_title();
            }
            ?>
        </h1>
        <div class="w-full flex lg:flex-row flex-col items-center gap-8">
            <img src="https://biomedica.webin.ge/wp-content/uploads/2024/09/ფართე-1.png" alt="heel" class="rounded-md lg:w-1/2">
            <img src="https://biomedica.webin.ge/wp-content/uploads/2024/09/Rectangle-28.png" alt="traumel" class="rounded-md lg:w-1/2">
        </div>
    </div>
    
    <div class="w-full flex flex-col lg:flex-row lg:gap-5 mt-5">
        <!-- Filter Section -->
        <div class="custom-products-archive-filter w-full lg:w-3/12 flex flex-col mt-0.5">
            <span class="text-2xl font-bold text-[#343434]">ფილტრი</span>
            <div class="w-full p-6 bg-white flex flex-col rounded-md gap-6">
                <span class="text-2xl font-bold text-[#343434]">კატეგორიები</span>
                <div class="category-filter">
                    <?php
                    $current_cat_id = null;
                    $selected_cats = isset($_GET['product_categories']) ? array_map('intval', $_GET['product_categories']) : array();
                    
                    if (is_product_category()) {
                        $current_cat = get_queried_object();
                        $current_cat_id = $current_cat->term_id;
                        if (!in_array($current_cat_id, $selected_cats)) {
                            $selected_cats[] = $current_cat_id;
                        }
                    }
                    ?>
                    <form id="product-filter-form" method="GET">
                        <?php wp_nonce_field('filter_products_nonce', 'filter_nonce'); ?>
                        <?php echo get_product_categories_hierarchical(0, $current_cat_id, $selected_cats); ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="w-full mt-6 lg:mt-0 lg:w-9/12 flex flex-col gap-6">
            <!-- Ordering -->
            <div class="custom-ordering w-full rounded-md flex justify-end">
                <form method="get" class="woocommerce-ordering bg-white w-full lg:w-1/4 rounded-2xl">
                    <select name="orderby" class="orderby p-4 rounded-2xl">
                        <option value="menu_order" <?php selected('menu_order', isset($_GET['orderby']) ? $_GET['orderby'] : ''); ?>>დალაგება: ნაგულისხმევი</option>
                        <option value="popularity" <?php selected('popularity', isset($_GET['orderby']) ? $_GET['orderby'] : ''); ?>>დალაგება: პოპულარობით</option>
                        <option value="rating" <?php selected('rating', isset($_GET['orderby']) ? $_GET['orderby'] : ''); ?>>დალაგება: შეფასებით</option>
                        <option value="date" <?php selected('date', isset($_GET['orderby']) ? $_GET['orderby'] : ''); ?>>დალაგება: უახლესი</option>
                        <option value="price" <?php selected('price', isset($_GET['orderby']) ? $_GET['orderby'] : ''); ?>>დალაგება: ფასი (ზრდა)</option>
                        <option value="price-desc" <?php selected('price-desc', isset($_GET['orderby']) ? $_GET['orderby'] : ''); ?>>დალაგება: ფასი (კლება)</option>
                    </select>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="custom-products-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <?php
                if (have_posts()) :
                    while (have_posts()) : the_post();
                        global $product;
                        if (!$product || !$product->is_visible()) continue;
                        ?>
                        <div class="custom-product-item p-8 bg-white rounded-2xl relative">
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
                                <div class="price-stock-wrapper">
                                    <div class="product-price">
                                        <?php woocommerce_template_loop_price(); ?>
                                    </div>
                                    <div class="product-stock-status">
                                        <?php 
                                        $stock_status = $product->get_stock_status();
                                        if ($stock_status === 'outofstock') {
                                            echo '<span class="stock-status out-of-stock">არ არის მარაგში</span>';
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
                    endwhile;
                else :
                    echo '<p>პროდუქტები არ მოიძებნა</p>';
                endif;
                ?>
            </div>

            <!-- Pagination -->
            <div class="text-center mb-24 lg:text-left p-4 custom-pagination-container bg-white rounded-xl flex flex-col lg:flex-row lg:justify-between items-center gap-6">
                <div class="product-count-container lg:w-1/2">
                    <?php
                    $total = wc_get_loop_prop('total');
                    $per_page = wc_get_loop_prop('per_page');
                    $current = wc_get_loop_prop('current_page');
                    
                    $first = ($per_page * $current) - $per_page + 1;
                    $last = min($total, $per_page * $current);
                    
                    if ($total <= 1) {
                        echo "ნაჩვენებია 1 პროდუქტი 1-დან";
                    } elseif ($total <= $per_page || -1 === $per_page) {
                        echo "ნაჩვენებია $total პროდუქტი $total-დან";
                    } else {
                        echo "ნაჩვენებია $first-$last პროდუქტი $total-დან";
                    }
                    ?>
                </div>
                <?php if (wc_get_loop_prop('total_pages') > 1) : ?>
                <nav class="woocommerce-pagination custom-pagination-list lg:w-1/2 lg:flex lg:justify-end">
                    <?php
                    echo paginate_links(array(
                        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => wc_get_loop_prop('total_pages'),
                        'prev_text' => '&larr;',
                        'next_text' => '&rarr;',
                        'type' => 'list',
                        'end_size' => 1,
                        'mid_size' => 1
                    ));
                    ?>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Filter script initialized');
    let isAjaxRunning = false;

    function filterProducts(page = 1) {
        if (isAjaxRunning) {
            console.log('AJAX is already running');
            return;
        }
        
        console.log('Starting filter process');
        isAjaxRunning = true;

        const selectedCategories = [];
        $('.category-filter-checkbox:checked').each(function() {
            selectedCategories.push($(this).val());
        });
        
        const orderby = $('.woocommerce-ordering select').val() || 'menu_order';
        
        console.log('Selected categories:', selectedCategories);
        console.log('Order by:', orderby);
        console.log('Page:', page);

        $('.custom-products-grid').addClass('loading');
        
        $.ajax({
            url: custom_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_products',
                security: custom_ajax_obj.security,
                categories: selectedCategories,
                orderby: orderby,
                paged: page
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                
                if (response.success) {
                    $('.custom-products-grid').html(response.data.products);
                    $('.custom-pagination-container').html(response.data.pagination);
                    
                    // URL განახლება
                    updateUrlParameters(selectedCategories, orderby, page);

                    // სქროლი პროდუქტებთან
                    $('html, body').animate({
                        scrollTop: $('.custom-products-grid').offset().top - 100
                    }, 500);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
            },
            complete: function() {
                isAjaxRunning = false;
                $('.custom-products-grid').removeClass('loading');
            }
        });
    }

    function updateUrlParameters(categories, orderby, page) {
        const params = new URLSearchParams(window.location.search);
        
        // კატეგორიების პარამეტრები
        params.delete('product_categories[]');
        if (categories.length > 0) {
            categories.forEach(cat => {
                params.append('product_categories[]', cat);
            });
        }
        
        // დალაგების პარამეტრი
        if (orderby && orderby !== 'menu_order') {
            params.set('orderby', orderby);
        } else {
            params.delete('orderby');
        }
        
        // გვერდის პარამეტრი
        if (page > 1) {
            params.set('paged', page);
        } else {
            params.delete('paged');
        }
        
        const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
        window.history.pushState({}, '', newUrl);
    }

    // Event Listeners
    $(document).on('change', '.category-filter-checkbox', function() {
        console.log('Category checkbox changed');
        filterProducts(1);
    });

    $(document).on('change', '.woocommerce-ordering select', function() {
        console.log('Order changed');
        filterProducts(1);
    });

    // Pagination კლიკები
    $(document).on('click', '.woocommerce-pagination a.page-numbers', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            console.log('Pagination clicked:', page);
            filterProducts(page);
        }
    });

    // ქვეკატეგორიების ტოგლი
    $('.toggle-subcategories').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const targetId = $(this).data('target');
        const targetElement = $('#' + targetId);
        
        $(this).toggleClass('active');
        targetElement.toggleClass('active').slideToggle(300);
    });

    // მშობელი კატეგორიების გახსნა
    function openParentCategories() {
        $('.current-cat').parents('.subcategories').each(function() {
            $(this).addClass('active').show();
            $(this).siblings('.category-header').find('.toggle-subcategories').addClass('active');
        });
    }
    
    openParentCategories();
});
</script>

<style>
/* ძირითადი სტილები */
.custom-product-item {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.product-thumbnail-wrapper {
    position: relative;
    overflow: hidden;
}

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
    pointer-events: all;
}

.product-icons-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to right, 
                    rgba(255,255,255,0) 0%,
                    rgba(255,255,255,0.7) 50%, 
                    rgba(255,255,255,1) 100%);
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
    background: none !important;
}

/* კატეგორიების ფილტრის სტილები */
.category-filter {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.category-item {
    margin-bottom: 8px;
}

.category-header {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ჩექბოქსის სტილი */
.category-checkbox {
    display: flex;
    align-items: center;
    position: relative;
    padding-left: 25px;
    cursor: pointer;
    user-select: none;
}

.category-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    left: 0;
    height: 18px;
    width: 18px;
    background-color: #fff;
    border: 2px solid #ccc;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.category-checkbox:hover input ~ .checkmark {
    border-color: #00905a;
}

.category-checkbox input:checked ~ .checkmark {
    background-color: #00905a;
    border-color: #00905a;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.category-checkbox input:checked ~ .checkmark:after {
    display: block;
}

.category-checkbox .checkmark:after {
    left: 5px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* ფასის და სტატუსის სტილები */
.price-stock-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.product-price, 
.product-price span {
    color: #00905a !important;
    font-size: 16px;
    font-weight: 600;
}

.stock-status.out-of-stock {
    font-size: 12px;
    color: #dc2626 !important;
}

/* ქვეკატეგორიების სტილი */
.subcategories {
    margin-left: 28px;
    margin-top: 8px;
    display: none;
    transition: all 0.3s ease;
}

.subcategories.active {
    display: block;
}

.toggle-subcategories {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
    min-width: 24px;
    min-height: 24px;
    border-radius: 4px;
}

.toggle-subcategories:hover {
    background-color: rgba(0, 144, 90, 0.1);
}

.toggle-subcategories.active .arrow-icon {
    transform: rotate(90deg);
}

/* Loading ინდიკატორი */
.custom-products-grid.loading {
    position: relative;
    min-height: 200px;
}

.custom-products-grid.loading:after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1000;
}

.custom-products-grid.loading:before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #00905a;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 1001;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Pagination სტილები */
.woocommerce-pagination {
    margin-top: 2rem;
}

.woocommerce-pagination ul {
    display: flex !important;
    justify-content: center !important;
    gap: 0.5rem !important;
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.woocommerce-pagination ul li {
    margin: 0 !important;
}

.woocommerce-pagination ul li span.current {
    background: #00905a !important;
    color: white !important;
}

.woocommerce-pagination ul li a:hover {
    background: #e5e7eb !important;
}

/* მობილური სტილები */
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
    
    .category-name {
        font-size: 16px;
    }
    
    .custom-pagination-container {
        flex-direction: column;
        text-align: center;
    }
    
    .product-count-container,
    .woocommerce-pagination {
        width: 100% !important;
        justify-content: center !important;
    }
}
</style>

<?php get_footer(); ?>