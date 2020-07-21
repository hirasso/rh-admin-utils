<?php 

namespace R\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class RemoveAds extends Singleton {

  public function __construct() {
    add_action('admin_init', [$this, 'admin_init'], 11);
    add_action('admin_notices', [$this, 'remove_yoast_ads'], 9);
  }

  /**
   * Fire on admin_init
   *
   * @return void
   */
  public function admin_init() {
    $this->hide_duplicate_post_update_notice();
  }

  /**
   * Hides Duplicate Posts's update message
   *
   * @return void
   */
  private function hide_duplicate_post_update_notice() {
    remove_action( 'network_admin_notices', 'duplicate_post_show_update_notice' );
    remove_action( 'admin_notices', 'duplicate_post_show_update_notice' );
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