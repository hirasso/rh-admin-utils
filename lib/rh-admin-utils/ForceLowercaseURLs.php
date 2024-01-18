<?php

/**
 * Plugin Name:     Force Lowercase URLs
 * Description:     Redirect uppercase URLs to their lowercase counterpart.
 * Version:         1.0.0
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
 * require_once('/path/to/ForceLowercaseURLs');
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
        add_action('template_redirect', array(__CLASS__, 'force_lowercase_urls'));
    }

    /**
     * Changes the requested URL to lowercase and redirects if necessary
     */
    public static function force_lowercase_urls()
    {
        // Allow to opt-out of this functionality
        if (!apply_filters('rhau/force_lowercase_urls', true)) return;

        // Grab URL information for the current request
        $parsed = wp_parse_url($_SERVER['REQUEST_URI'] ?? '/');

        // Grab the relevant parts from the parsed url
        $path = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? null;

        // Create a lowercase copy of the URI
        $lowercase_path = strtolower($path);

        // Bail early if the url already is lowercase
        if ($path === $lowercase_path) return;

        // Construct the redirect url
        $redirect_uri = empty($query) ? $lowercase_path : "$lowercase_path?$query";

        // Perform a permanent redirect
        wp_safe_redirect($redirect_uri, 301);
        exit;
    }
}
