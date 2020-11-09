<?php 

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Misc extends Singleton {

  public function __construct() {
    add_filter('acf/prepare_field/type=image', [$this, 'prepare_image_field']);
    add_filter('admin_init', [$this, 'activate_acf_pro_license']);
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
     if ( ! defined( 'ACF_PRO_LICENSE' ) || empty( ACF_PRO_LICENSE ) || acf_pro_get_license_key() ) {
      return;
    }
    $_POST['acf_pro_licence'] = ACF_PRO_LICENSE;
    if( $acf_admin_updates = acf_get_instance('ACF_Admin_Updates') ) {
      $acf_admin_updates->activate_pro_licence();
    }
  }
  
}