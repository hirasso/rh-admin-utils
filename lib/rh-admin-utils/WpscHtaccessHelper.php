<?php
/*
* Copyright (c) 2022 Rasso Hilber
* https://rassohilber.com
*/

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * A helper to modify the WP Super Cache cache/.htaccess
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
        add_action('wp_cache_cleared', [__CLASS__, 'add_custom_directives_to_htaccess']);
        add_action('wp_cache_cleared', [__CLASS__, 'modify_supercache_directives']);
        add_action(
            'admin_init',
            [__CLASS__, 'modify_htaccess_on_super_cache_options_update'],
            11 // execute just after wp_cache_manager_updates has completed its thing
        );
    }

    /**
     * Modifies the .htaccess every time the Super Cache
     * Settings > "Update Status" button is clicked
     *
     * @return void
     */
    public static function modify_htaccess_on_super_cache_options_update() {
        $action = $_POST['action'] ?? null;
        $is_super_cache_update = $action === 'scupdates';
        if (!$is_super_cache_update) return;
        self::add_custom_directives_to_htaccess();
        self::modify_supercache_directives();
    }

    /**
     * Get the .htaccess path
     *
     * @return string
     */
    private static function get_htaccess_path(): string {
        return WP_CONTENT_DIR . '/cache/.htaccess';
    }

    /**
     * Checks if the wp-content/cache/.htaccess exists
     */
    private static function htaccess_exists(): bool
    {
        return file_exists(self::get_htaccess_path());
    }

    /**
     * Make sure the required api functions are available
     */
    private static function require_wp_api_functions(): void
    {
        if (function_exists('insert_with_markers')) return;
        require_once(ABSPATH . 'wp-admin/includes/misc.php');
    }

    /**
     * Adds custom directives to the wp-content/cache/.htaccess file
     *
     * @return void
     */
    public static function add_custom_directives_to_htaccess(): void
    {
        if (!self::htaccess_exists()) return;
        self::require_wp_api_functions();

        // Our custom directives
        $custom_directives = [
            "<IfModule mod_headers.c>",
            "Header set Accept-Ranges 'bytes'",
            "</IfModule>"
        ];

        // Update the .htaccess file
        insert_with_markers(
            self::get_htaccess_path(),
            'rh-admin-utils',
            $custom_directives
        );
    }

    /**
     * Modify the supercache directives
     *
     * @return void
     */
    public static function modify_supercache_directives(): void
    {

        if (!self::htaccess_exists()) return;
        self::require_wp_api_functions();

        // Extract the supercache directives
        $directives = extract_from_markers(self::get_htaccess_path(), 'supercache');
        // Trim each directive, to make it easier to handle them
        $directives = array_map('trim', $directives);

        $new_directives = [];

        // Make the two dirctives I want to customize filterable
        $cache_control_header = apply_filters(
            "rhau/wpsc/cache-control-header",
            "max-age=3, private"
        );
        $expires_by_type = apply_filters(
            "rhau/wpsc/expires-by-type",
            "access plus 3 seconds"
        );

        foreach ($directives as $directive) {
            // Modifiy the Cache-Control header directive
            if (str_starts_with($directive, "Header set Cache-Control")) {
                $new_directives[] = "Header set Cache-Control '$cache_control_header'";
                continue;
            }
            // Modify the ExpiresByType directive
            if (str_starts_with($directive, "ExpiresByType text/html")) {
                $new_directives[] = "ExpiresByType text/html '$expires_by_type'";
                continue;
            }
            // Add all other directives without modification
            $new_directives[] = $directive;
        }

        // Update the .htaccess file
        insert_with_markers(
            self::get_htaccess_path(),
            'supercache',
            $new_directives
        );
    }
}
