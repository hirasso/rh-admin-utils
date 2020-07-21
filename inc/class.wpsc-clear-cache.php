<?php 

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WpscClearCache extends Singleton {

  public function __construct() {
    add_action('admin_init', [$this, 'wp_super_cache_init']);
  }

  /**
   * WP Super Cache Init
   *
   * @return void
   */
  public function wp_super_cache_init() {
    if( is_plugin_active('wp-super-cache/wp-cache.php') ) {
      add_action('admin_bar_menu', [$this, 'replace_wp_super_cache_admin_bar_button'], 999 );
    }
    if( intval($_GET['rh_clear_cache'] ?? null) === 1 ) $this->clear_cache();
  }

  /**
   * Deletes the cache
   *
   * @return void
   */
  private function clear_cache() {
    global $cache_path;
    check_admin_referer( 'rh_clear_cache' );
    prune_super_cache( $cache_path . 'supercache/', true );
    prune_super_cache( $cache_path, true );

    do_action('rh/wpsc-cc/clear_cache');

    $redirect_url = remove_query_arg('_wpnonce');
    $redirect_url = remove_query_arg('rh_clear_cache', $redirect_url);

    $notice = apply_filters('rh/wpsc-cc/cache_deleted_notice', __( 'Cache deleted.' ));
    au()->add_admin_notice('cache-cleared', $notice, 'success');

    wp_safe_redirect( $redirect_url );
    exit;

  }

  /**
   * Adds the Item to the admin bar menu. Replaces WPSC's item
   *
   * @param [Class] $wp_admin_bar
   * @return void
   */
  public function replace_wp_super_cache_admin_bar_button( $wp_admin_bar ) {
    global $super_cache_enabled, $cache_enabled;

    $wp_admin_bar->remove_menu('delete-cache');
    if( !current_user_can('edit_others_posts') || !($super_cache_enabled && $cache_enabled) ) {
      return;
    }

    $url = $_SERVER['REQUEST_URI'];
    $url = add_query_arg( 'rh_clear_cache', '1', $url );
    $text = __( 'Delete Cache', 'wp-super-cache' );
    $text = apply_filters('rh/wpsc-cc/menu_item_text', $text);

    $args = [
      'parent' => '',
      'id' => 'delete-cache',
      'title' => '<span class="ab-icon"></span>' . $text,
      'meta' => array( 'title' => __( 'Delete Super Cache cached files', 'wp-super-cache' ), 'target' => '_self' ),
      'href' => wp_nonce_url( $url, 'rh_clear_cache' )
    ];

    $wp_admin_bar->add_menu($args);

  }
}