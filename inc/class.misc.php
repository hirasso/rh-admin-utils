<?php 

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Misc extends Singleton {

  public function __construct() {
    add_filter('acf/prepare_field/type=image', [$this, 'prepare_image_field']);
    add_filter('admin_init', [$this, 'activate_acf_pro_license']);
    add_filter('admin_init', [$this, 'overwrite_qtranslate_defaults']);
    add_action('admin_init', [$this, 'redirect_edit_php']);
    add_action('plugins_loaded', [$this, 'limit_revisions']);
    add_filter('map_meta_cap', [$this, 'disable_capabilities'], 10, 4);
  }

  /**
   * Limit revisions
   *
   * @return void
   */
  public function limit_revisions() {
    if( defined('WP_POST_REVISIONS') ) return;
    $revisions = intval( apply_filters('rhau/settings/post_revisions', 3) );
    define('WP_POST_REVISIONS', $revisions );
  }

  /**
   * Add general instructions to image fields
   *
   * @param Array $field
   * @return Array $field
   */
  public function prepare_image_field( $field ) {
    if( !is_admin() || !$field || empty($field['label']) ) return $field;
    $field['label'] .= " <span title='JPG for photos or drawings, PNG for transparency or simple graphics (larger file size).' class='dashicons dashicons-info acf-js-tooltip rhau-icon--info'></span>";
    return $field;
  }

  /**
   * Automatically inject ACF pro license key
   *
   * @param [string] $pre
   * @return string
   */
  public function activate_acf_pro_license() {
    if ( 
      ! function_exists('acf_pro_get_license_key') 
      || ! defined( 'ACF_PRO_LICENSE' ) 
      || empty( ACF_PRO_LICENSE ) 
      || acf_pro_get_license_key() ) {
      return;
    }
    $_POST['acf_pro_licence'] = ACF_PRO_LICENSE;
    if( $acf_admin_updates = acf_get_instance('ACF_Admin_Updates') ) {
      $acf_admin_updates->activate_pro_licence();
    }
  }

  /**
   * Overwrites some qtranslate defaults
   *
   * @return void
   */
  public function overwrite_qtranslate_defaults() {
    global $q_config;
    if( !isset($q_config) ) return;
    // disable qtranslate styles on the admin LSBs
    $q_config['lsb_style'] = 'custom';
    // do not highlight translatable fields
    $q_config['highlight_mode'] = 0;
    // hide the 'copy from' button
    $q_config['hide_lsb_copy_content'] = true;
  }

  /**
   * Redirects the default edit.php screen
   *
   * @return void
   */
  public function redirect_edit_php() {
    global $pagenow, $typenow;
    if( $pagenow !== 'edit.php') return;
    if( $typenow ) return;
    $redirect_url = admin_url('/edit.php?post_type=page');
    $redirect_url = apply_filters('rhau/edit_php_redirect_url', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
  }

  /**
   * Disable some caps for all users
   *
   * @param array $caps
   * @param string $cap
   * @param int $user_id
   * @param array $args
   * @return array
   */
  function disable_capabilities( $caps, $cap, $user_id, $args ) {
    $disabled_capabilities = apply_filters('rhau/disabled_capabilities', ['customize']);
    if( !in_array($cap, $disabled_capabilities) ) return $caps;
    $caps[] = 'do_not_allow';
    return $caps;
  }
  
}