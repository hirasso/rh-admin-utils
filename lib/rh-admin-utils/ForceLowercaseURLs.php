<?php

/**
 * Plugin Name:     Force Lowercase URLs
 * Description:     Redirect uppercase URLs to their lowercase counterpart.
 * Version:         1.0.0
 * Requires PHP:    8.0
 * Author:          Rasso Hilber
 * Author URI:      http://rassohilber.com/
 */

/**
 * This code is heavily based on this plugin by Josh Buchea:
 * https://github.com/joshbuchea/wp-force-lowercase-urls/
 *
 * There are a few notable changes to the original plugin
 *
 * - the redirect will only ever be executed on `template_redirect`. This will prohibit many
 *   pitfalls the original plugin had since it hooked into the `init` hook
 * - as an additional measure, the code will gracefully an empty REQUEST_URI if not in
 *   a browser context (WP_CLI)
 * - general code cleanup, use of a namespace
 *
 * Initialize the plugin by requiring the file and then calling this in your functions.php or wherever:
 *
 * ```php
 * require_once('/path/to/ForceLowercaseURLs.php');
 * \RH\AdminUtils\ForceLowercaseURLs::init()
 * ```
 */

declare(strict_types=1);

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


class ForceLowercaseURLs
{

    /**
     * Initialize this plugin
     */
    public static function init()
    {
        add_action('template_redirect', [__CLASS__, 'server_side_redirect']);
        add_action('wp_head', [__CLASS__, 'client_side_redirect'], 1);
    }

    /**
     * Changes the requested URL to lowercase and redirects if necessary
     */
    public static function server_side_redirect()
    {
        // Allow to opt-out of this functionality
        if (!apply_filters('rhau/force_lowercase_urls', true)) return;
        if (!apply_filters('rhau/force_lowercase_urls/server', true)) return;

        // Grab URL information for the current request
        $parsed = wp_parse_url($_SERVER['REQUEST_URI'] ?? '/');

        // Grab the relevant parts from the parsed URL
        $path = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? null;

        // Create a lowercase copy of the path
        $lowercase_path = strtolower($path);

        // Bail early if the path already is lowercase
        if ($path === $lowercase_path) return;

        // Construct the redirect url
        $redirect_uri = empty($query) ? $lowercase_path : "$lowercase_path?$query";

        // Perform a permanent redirect
        wp_safe_redirect($redirect_uri, 301);
        exit;
    }

    /**
     * Static Cache plugins like WP Super Cache check for matching HTML files for the current request.
     * This check is case-insensitive, since that's how file systems work.
     * The only way to force lowercase URLs here is using JavaScript to change the URL
     */
    public static function client_side_redirect()
    {
        // Allow to opt-out of this functionality
        if (!apply_filters('rhau/force_lowercase_urls', true)) return;
        if (!apply_filters('rhau/force_lowercase_urls/client', true)) return;

        ob_start() ?>
        <!-- Injected by ForceLowercaseURLs -->
        <script>
            function forceLowercaseURL() {
                // Grab relevant info from the current URL
                const { pathname, search, hash } = window.location;
                // Construct a lowercase version of the URL
                const lowerCaseURL = new URL(pathname.toLowerCase() + search + hash, document.baseURI).toString();
                // Bail early if the URL already is lowercase
                if (lowerCaseURL === window.location.href) return;
                // Replace the current URL without a hard reload
                const state = window.history.state || {};
                window.history.replaceState(state, '', lowerCaseURL);
            }
            forceLowercaseURL();
        </script>
        <?php echo ob_get_clean();
    }
}
