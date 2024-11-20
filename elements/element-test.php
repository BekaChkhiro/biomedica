<?php 
// element-test.php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Prefix_Element_Test extends \Bricks\Element {
  // Element properties
  public $category     = 'general'; // Use predefined element category 'general'
  public $name         = 'prefix-test'; // Make sure to prefix your elements
  public $icon         = 'ti-bolt-alt'; // Themify icon font class
  public $css_selector = '.prefix-test-wrapper'; // Default CSS selector
  public $scripts      = ['prefixElementTest']; // Script(s) run when element is rendered on frontend or updated in builder

  // Return localised element label
  public function get_label() {
    return esc_html__( 'Test element', 'bricks' );
  }

  // Set builder control groups
  public function set_control_groups() {
    $this->control_groups['text'] = [ // Unique group identifier (lowercase, no spaces)
      'title' => esc_html__( 'Text', 'bricks' ), // Localized control group title
      'tab' => 'content', // Set to either "content" or "style"
    ];

    $this->control_groups['settings'] = [
      'title' => esc_html__( 'Settings', 'bricks' ),
      'tab' => 'content',
    ];
  }
 
  // Set builder controls
  public function set_controls() {
    $this->controls['content'] = [ // Unique control identifier (lowercase, no spaces)
      'tab' => 'content', // Control tab: content/style
      'group' => 'text', // Show under control group
      'label' => esc_html__( 'Content', 'bricks' ), // Control label
      'type' => 'text', // Control type 
      'default' => esc_html__( 'Content goes here ..', 'bricks' ), // Default setting
    ];
    
    $this->controls['type'] = [
      'tab' => 'content',
      'group' => 'settings',
      'label' => esc_html__( 'Type', 'bricks' ),
      'type' => 'select',
      'options' => [
        'info' => esc_html__( 'Info', 'bricks' ),
        'success' => esc_html__( 'Success', 'bricks' ),
        'warning' => esc_html__( 'Warning', 'bricks' ),
        'danger' => esc_html__( 'Danger', 'bricks' ),
        'muted' => esc_html__( 'Muted', 'bricks' ),
      ],
      'inline' => true,
      'clearable' => false,
      'pasteStyles' => false,
      'default' => 'info',
    ];
  }

  // Enqueue element styles and scripts
  public function enqueue_scripts() {
    wp_enqueue_script( 'prefix-test-script' );
  }

  // Render element HTML
  public function render() {
    // Set element attributes
    $root_classes[] = 'prefix-test-wrapper';

    if ( ! empty( $this->settings['type'] ) ) {
      $root_classes[] = "color-{$this->settings['type']}";
    }

    // Add 'class' attribute to element root tag
    $this->set_attribute( '_root', 'class', $root_classes );

    // Render element HTML
    // '_root' attribute is required since Bricks 1.4 (contains element ID, class, etc.)
    echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes
      if ( ! empty( $this->settings['content'] ) ) {
        echo "<div>{$this->settings['content']}</div>";
      }
    echo '</div>';
  }
}