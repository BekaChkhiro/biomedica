<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Prefix_Element_Product_Carousel extends \Bricks\Element {
  public function get_name() {
    return 'product-carousel';
  }

  public function get_label() {
    return esc_html__('Product Carousel', 'bricks');
  }

  public function set_control_groups() {
    $this->control_groups['general'] = [
      'title' => esc_html__('General', 'bricks'),
      'tab' => 'content',
    ];
  }

  public function set_controls() {
    $this->controls['category'] = [
      'tab' => 'content',
      'group' => 'general',
      'label' => esc_html__('Category', 'bricks'),
      'type' => 'select',
      'options' => $this->get_product_categories(),
    ];

    $this->controls['products_per_row'] = [
      'tab' => 'content',
      'group' => 'general',
      'label' => esc_html__('Products per row', 'bricks'),
      'type' => 'number',
      'default' => 4,
    ];

    $this->controls['total_products'] = [
      'tab' => 'content',
      'group' => 'general',
      'label' => esc_html__('Total products', 'bricks'),
      'type' => 'number',
      'default' => 8,
    ];
  }

  private function get_product_categories() {
    $categories = get_terms('product_cat');
    $options = [];
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ){
      foreach ( $categories as $category ) {
        $options[$category->term_id] = $category->name;
      }
    }
    return $options;
  }

  public function render() {
    $category = $this->settings['category'];
    $products_per_row = $this->settings['products_per_row'];
    $total_products = $this->settings['total_products'];

    $query_args = [
      'post_type' => 'product',
      'posts_per_page' => $total_products,
      'tax_query' => [
        [
          'taxonomy' => 'product_cat',
          'field' => 'term_id',
          'terms' => $category,
        ],
      ],
    ];

    $products_query = new WP_Query($query_args);
    if ($products_query->have_posts()) :
      echo '<div class="product-carousel">';
      while ($products_query->have_posts()) : $products_query->the_post();
        echo '<div class="product-item">';
        the_post_thumbnail('medium');
        the_title('<h3>', '</h3>');
        echo '<span>' . wc_price(get_post_meta(get_the_ID(), '_price', true)) . '</span>';
        echo '</div>';
      endwhile;
      echo '</div>';
      wp_reset_postdata();
    else :
      esc_html_e('No products matched your criteria.', 'bricks');
    endif;
  }
}
