<?php 

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Misc extends Singleton {

  public function __construct() {
    add_filter('acf/prepare_field/type=image', [$this, 'prepare_image_field']);
  }

  /**
   * Add general instructions to image fields
   *
   * @param Array $field
   * @return Array $field
   */
  public function prepare_image_field( $field ) {
    if( !is_admin() || !$field || empty($field['label']) ) return $field;
    $field['label'] .= "<span title='JPG for photos or drawings, PNG for transparency or simple graphics (larger file size).' class='dashicons dashicons-info acf-js-tooltip rhau-icon--info'></span>";
    return $field;
  }
  
}