<?php 

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EditorCanUpdate extends Singleton {

  public $role_name = 'editor_can_update';

  public function __construct() {
    add_action('admin_init', [$this, 'add_role']);
  }

  /**
   * Add the role if it doesn't exist already
   *
   * @return void
   */
  public function add_role(): void {
    $wp_roles = wp_roles();
    if( $wp_roles->is_role($this->role_name) ) return;
    $editor_role = get_role('editor');
    $caps = wp_parse_args([
      'update_core' => true,
      'update_plugins' => true,
    ], $editor_role->capabilities);
    add_role($this->role_name, sprintf("%s (%s)", translate_user_role('Editor'),  __("+ Run Updates")), $caps);
  }
  
}