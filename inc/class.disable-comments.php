<?php 

namespace RH\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DisableComments extends Singleton {

  public function __construct() {
    // disable xmlrpc forever
    add_filter( 'xmlrpc_enabled', '__return_false' );
    add_action('after_setup_theme', [$this, 'init']);
  }

  /**
   * Inits the class
   *
   * @return void
   * @author Rasso Hilber <mail@rassohilber.com>
   */
  public function init(): void {
    // allow re-activating comments
    if( !apply_filters('rhau/disable_comments', true ) ) return;

    $this->overwrite_discussion_options();
    // hide comments UI
    add_action('admin_menu', [$this, 'admin_menu'], 999);
    add_action('admin_bar_menu', [$this, 'admin_bar_menu'], 999);
    // other hooks
    add_action('admin_init', [$this, 'admin_init']);
  }

  public function admin_init(): void {
    global $pagenow;
    if( $pagenow === 'options-discussion.php' ) {
      au()->add_admin_notice('comments-disabled', __( '[RH Admin Utils] Comments are disabled. Some settings on this page are being ignored and/or overwritten.' ));
    }
    
  }

  /**
   * Automatically sets the settings for disabling comments
   *
   * @return void
   * @author Rasso Hilber <mail@rassohilber.com>
   */
  private function overwrite_discussion_options(): void {
    // don't allow comments on new posts
    add_filter('option_default_comment_status', '__return_empty_string');
    // don't allow pingbacks and trackbacks
    add_filter('pre_option_default_ping_status', '__return_empty_string');
    // automatically close comment status on old posts
    add_filter('pre_option_close_comments_days_old', '__return_zero');
    add_filter('pre_option_close_comments_for_old_posts', '__return_true');
    // additional measure: Only allow logged-in users to comment
    add_filter('pre_option_comment_registration', '__return_true');
    // additional measure: All comments must be approved
    add_filter('pre_option_comment_moderation', '__return_true');
  }

  /**
   * Removes the comments node from the admin bar
   *
   * @param \WP_Admin_Bar $admin_bar
   * @return void
   * @author Rasso Hilber <mail@rassohilber.com>
   */
  public function admin_bar_menu(\WP_Admin_Bar $admin_bar): void {
    $admin_bar->remove_node('comments');
  }

  /**
   * Removes the comments admin menu item
   *
   * @return void
   * @author Rasso Hilber <mail@rassohilber.com>
   */
  public function admin_menu(): void {
    remove_menu_page('edit-comments.php');
  }
  
}