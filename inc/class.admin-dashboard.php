<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AdminDashboard
{

  public static function init()
  {
    add_action("wp_dashboard_setup", [__CLASS__, "wp_dashboard_setup"], 20);
  }

  public static function wp_dashboard_setup(): void
  {
    self::disable_widgets();
  }

  /**
   * Disable some dashboard widgets
   *
   * @return void
   */
  private static function disable_widgets(): void
  {
    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    // unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
  }

}