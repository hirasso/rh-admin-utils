<?php
/**
 * Plugin Name: RH Admin Utilities
 * Version: 1.0.8
 * Author: Rasso Hilber
 * Description: Admin Utilities for WordPress
 * Author URI: https://rassohilber.com
**/

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once(__DIR__ . '/inc/class.singleton.php');

class AdminUtils extends Singleton {

  private $prefix = 'rhau';

  public function __construct() {
    
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    add_action('wp_before_admin_bar_render', [$this, 'admin_bar_buttons'], 10000001, 1);
    add_action('admin_notices', [$this, 'remove_yoast_ads'], 9);
  }

  /**
   * Enqueues Admin Assets
   *
   * @return void
   */
  public function enqueue_admin_assets() {
    wp_enqueue_style( "$this->prefix-admin", $this->asset_uri("assets/$this->prefix-admin.css"), [], null );
    wp_enqueue_script( "$this->prefix-admin", $this->asset_uri("assets/$this->prefix-admin.js"), ['jquery'], null, true );
  }

  /**
   * Helper function to get versioned asset urls
   *
   * @param [type] $path
   * @return void
   */
  private function asset_uri( $path ) {
    $uri = plugins_url( $path, __FILE__ );
    $file = $this->get_plugin_path( $path );
    if( file_exists( $file ) ) {
      $version = filemtime( $file );
      $uri .= "?v=$version";
    }
    return $uri;
  }

  /**
   * Helper function to get a file path inside this plugin's folder
   *
   * @return void
   */
  function get_plugin_path( $path ) {
    $path = ltrim( $path, '/' );
    $file = plugin_dir_path( __FILE__ ) . $path;
    return $file;
  }

  /**
   * Helper function to transform an array to an object
   *
   * @param array $array
   * @return stdClass
   */
  private function to_object( $array ) {
    return json_decode(json_encode($array));
  }

  /**
   * Helper function to detect a development environment
   */
  private function is_dev() {
    return defined('WP_ENV') && WP_ENV === 'development';
  }

  /**
   * Get a template
   *
   * @param string $template_name
   * @param mixed $value
   * @return string
   */
  public function get_template($template_name, $value = null) {
    $value = $this->to_object($value);
    $path = $this->get_plugin_path("templates/$template_name.php");
    $path = apply_filters("$this->prefix/template/$template_name", $path);
    if( !file_exists($path) ) return "<p>$template_name: Template doesn't exist</p>";
    ob_start();
    if( $this->is_dev() ) echo "<!-- Template Path: $path -->";
    include( $path );
    return ob_get_clean();
  }

  /**
   * Check if we are on an admin bar edit screen
   *
   * @return boolean
   */
  private function is_admin_edit_screen() {
    global $pagenow;
    if( in_array($pagenow, ['post.php', 'post-new.php'] ) ) return true;
    if( $this->is_admin_acf_options_page() ) return true;
    return false;
  }

  /**
   * Check if on acf options page
   *
   * @return boolean
   */
  private function is_admin_acf_options_page() {
    if( !function_exists('acf_get_options_page') ) return false;
    if( !$slug = $_GET['page'] ?? null ) return false;
    if( !$options_page = acf_get_options_page($slug) ) return false;
    $prepare_slug = preg_replace( "/[\?|\&]page=$slug/", "", basename( $_SERVER['REQUEST_URI'] ) );
    if( !empty($options_page['parent_slug']) && $options_page['parent_slug'] !== $prepare_slug ) return false;
    return true;
  }

  /**
   * Adds buttons to WP Admin Bar
   *
   * @return void
   */
  public function admin_bar_buttons() {
    global $wp_admin_bar;
    if( !$this->is_admin_edit_screen() ) return;
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
   * Removes YOAST SEO ads from WordPress Admin
   * Tested with Yoast SEO Version 14.4.1
   * 
   * @date 2020/06/25
   * @return void
   */
  public function remove_yoast_ads() {
    // check if class Yoast_Notification_Center exists
    if( !class_exists('\Yoast_Notification_Center') ) return;
    $notification_center = \Yoast_Notification_Center::get();
    // get all notifications
    $notifications = $notification_center->get_sorted_notifications();
    // loop through all YOAST notifications
    foreach( $notifications as $notification ) {
      // transform the notification to an array, so that we can access the message
      $notification_array = $notification->to_array();
      // get message from array
      $notification_message = $notification_array['message'] ?? null;
      // continue to next notification if no message in array
      if( !$notification_message ) continue;
      // Remove the notification if it contains a string. 
      // You could also check for $notification_array['options']['yoast_branding'] === true
      if( stripos($notification_message, 'Get Yoast SEO Premium') !== false ) {
        $notification_center->remove_notification($notification);
      }
    }
  }

}

AdminUtils::getInstance();