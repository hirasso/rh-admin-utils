<?php
/*
* Copyright (c) 2022 Rasso Hilber
* https://rassohilber.com
*/

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * A helper to modify default WP Super Cache behavior
 */
class WpscHtaccessHelper
{

    /**
     * Static init function instead of a constructor, has to be initialized
     * manually from a theme or plugin, like this:
     *
     * WpCacheHtaccess::init();
     */
    public static function init()
    {
        add_action('wp_cache_cleared', [__CLASS__, 'modify_cache_dir_htaccess']);
    }

    /**
     * Adds custom directives to the wp-content/cache/.htaccess file
     *
     * @return void
     */
    public static function modify_cache_dir_htaccess(): void
    {
        // Check if the wp-content/cache/.htaccess exists
        $htaccess_path = WP_CONTENT_DIR . '/cache/.htaccess';
        if (!file_exists($htaccess_path)) return;

        // Make sure `insert_with_markers` exists
        if (!function_exists('insert_with_markers')) {
            require_once(ABSPATH . 'wp-admin/includes/misc.php');
        }

        // Our custom directives
        $custom_directives = [
            "<IfModule mod_headers.c>",
            "Header set Accept-Ranges 'bytes'",
            "</IfModule>"
        ];

        // Update the .htaccess file
        insert_with_markers(
            $htaccess_path,
            'rh-admin-utils',
            $custom_directives
        );
    }

}
