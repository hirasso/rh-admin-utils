<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LimitLoginAttemptsReloadedHelper
{

    public static function init()
    {
        add_action('admin_init', [__CLASS__, 'bypass_dashboard_tab']);
        add_action("wp_dashboard_setup", [__CLASS__, "disable_dashboard_widget"], 9999);
    }

    /**
     * Completely bypasses the dashboard page of Limit Login Attempts Reloaded
     *  – it's a pile of crap.
     *
     * @return void
     */
    public static function bypass_dashboard_tab(): void
    {
        $page = $_GET['page'] ?? null;
        if ($page !== 'limit-login-attempts') return;

        // An empty "tab" is also being redirected to "dashboard"
        $tab = $_GET['tab'] ?? 'dashboard';
        if ($tab !== 'dashboard') return;

        $new_url = add_query_arg([
            'page' => 'limit-login-attempts',
            'tab' => 'settings'
        ], admin_url('options-general.php'));

        wp_safe_redirect($new_url, 301);
        exit;
    }

    public static function disable_dashboard_widget(): void
    {
        global $wp_meta_boxes;
        unset($wp_meta_boxes['dashboard']['normal']['high']['llar_stats_widget']);
    }
}
