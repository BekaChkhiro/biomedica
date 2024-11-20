<?php
if ( ! defined( 'ABSPATH' ) ) exit; // პირდაპირი წვდომის ბლოკირება

class Prefix_Product_Search extends \Bricks\Element {
  // ელემენტის მახასიათებლები
  public $category   = 'woocommerce';
  public $name       = 'product-search';
  public $icon       = 'ti-search'; // Themify-ის ძიების აიქონი

  // კონტროლების შექმნა
  public function set_controls() {
    $this->controls['placeholder_text'] = [
      'tab'    => 'content',
      'label'  => esc_html__( 'Placeholder Text', 'bricks' ),
      'type'   => 'text',
      'default' => 'Search for products...',
    ];
  }

  // ელემენტის HTML გენერაცია
  public function render() {
    $placeholder = $this->settings['placeholder_text'] ? $this->settings['placeholder_text'] : 'Search for products...';

    echo '<div class="bricks-product-search w-full">';
    echo '<input type="text" id="product-search-input" class="rounded" style="padding: 0px 15px; border-radius: 10px;" placeholder="' . esc_html( $placeholder ) . '" />';
    echo '<div id="product-search-results"></div>'; // აქ გამოვა შედეგები
    echo '</div>';

    // AJAX სკრიპტი
    ?>
    <script>
      jQuery(document).ready(function($) {
        $('#product-search-input').on('keyup', function() {
          var searchText = $(this).val();
          if (searchText.length > 2) {
            $.ajax({
              url: '<?php echo admin_url('admin-ajax.php'); ?>',
              type: 'POST',
              data: {
                action: 'product_search',
                query: searchText
              },
              success: function(data) {
                $('#product-search-results').html(data);
              }
            });
          } else {
            $('#product-search-results').html(''); // ვშლით შედეგებს თუ ველი ცარიელია ან ძალიან მოკლეა
          }
        });
      });
    </script>
    <?php
  }
}
?>
