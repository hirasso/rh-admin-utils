<?php
/**
 * Plugin Name: RH Admin Utilities
 * Version: 1.0.3
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
    
    add_action('plugins_loaded', [$this, 'connect_to_rh_updater']);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    add_action('wp_before_admin_bar_render', [$this, 'admin_bar_buttons'], 10000001, 1);
    
  }

  /**
   * Connects the plugin to RH Updater
   *
   * @return void
   */
  public function connect_to_rh_updater() {
    if( class_exists('\RH_Bitbucket_Updater') ) {
      new \RH_Bitbucket_Updater( __FILE__ );
    } else {
      add_action('admin_notices', [$this, 'show_notice_missing_rh_updater']);
    }
  }

  /**
   * Shows the missing updater notice
   *
   * @return void
   */
  private function show_notice_missing_rh_updater() {
    global $rh_updater_notice_shown;
    if( !$rh_updater_notice_shown && current_user_can('activate_plugins') ) {
      $rh_updater_notice_shown = true;
      echo "<div class='notice notice-warning'><p>RH Updater is not installed. Custom plugins won't be updated.</p></div>";
    }
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

}

AdminUtils::getInstance();