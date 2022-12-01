<?php

namespace RH\AdminUtils;

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

    $wp_admin_bar->add_node([
      'id' => 'rh-publish',
      'parent' => 'top-secondary',
      'href' => '##',
    ]);
    $wp_admin_bar->add_node([
      'id' => 'rh-save',
      'parent' => 'top-secondary',
      'href' => '##',
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
    if( rhau()->is_admin_acf_options_page() ) return true;
    return false;
  }

}
