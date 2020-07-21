<?php 

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AdminBarPublishButton extends Singleton {

  public function __construct() {
    add_action('wp_before_admin_bar_render', [$this, 'add_buttons'], 10000001, 1);
  }

  /**
   * Adds buttons to WP Admin Bar
   *
   * @return void
   */
  public function add_buttons() {
    global $wp_admin_bar;
    if( !$this->current_screen_has_publish_button() ) return;
    ob_start() ?>
    <style>
      #wp-admin-bar-rh-publish > .ab-item.is-disabled,
      #wp-admin-bar-rh-save > .ab-item.is-disabled {
        pointer-events: none;
        opacity: 0.5;
      }
    </style>
    <?php $style = ob_get_clean();
    $wp_admin_bar->add_node([
      'id' => 'rh-publish',
      'parent' => 'top-secondary',
      'href' => '##',
      'meta' => [
        'html' => $style
      ]
    ]);
    $wp_admin_bar->add_node([
      'id' => 'rh-save',
      'parent' => 'top-secondary',
      'href' => '##',
      'meta' => [
        'html' => $style
      ]
    ]);
  }

  /**
   * Check if we are on an admin bar edit screen
   *
   * @return boolean
   */
  private function current_screen_has_publish_button() {
    global $pagenow;
    if( in_array($pagenow, ['post.php', 'post-new.php'] ) ) return true;
    if( au()->is_admin_acf_options_page() ) return true;
    return false;
  }
  
}