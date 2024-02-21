<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

/**
 * This class helps with caching ACF oEmbed fields
 * @see https://support.advancedcustomfields.com/forums/topic/oembed-cache/
 */

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ACFOembedCache
{
    /** Init */
    public static function init()
    {
        add_action('acf/init', [__CLASS__, 'disable_default_oembed_format_value'], 1);
        add_filter('acf/format_value/type=oembed', [__CLASS__, 'format_value_oembed'], 10, 3);
        add_filter('acf/update_value/type=oembed', [__CLASS__, 'cache_value_oembed'], 10, 3);
    }

    /** Disables the built-in formatting for oembed fields */
    public static function disable_default_oembed_format_value(): void
    {
        $field_type = acf_get_field_type('oembed');
        remove_filter('acf/format_value/type=oembed', [ $field_type, 'format_value' ]);
    }

    /** Fetch the cached oEmbed HTML; Replaces the original method */
    public static function format_value_oembed($value, $post_id, $field)
    {
        if (empty($value)) return $value;

        return self::acf_oembed_get($value, $post_id, $field);
    }

    /** Cache the oEmbed HTML */
    public static function cache_value_oembed($value, $post_id, $field)
    {
        if (empty($value)) return $value;

        // Warm the cache
        self::acf_oembed_get($value, $post_id, $field);

        return $value;
    }

    /**
     * Attempts to fetch the embed HTML for a provided URL using oEmbed.
     *
     * Checks for a cached result (stored as custom post or in the post meta).
     *
     * @see  \WP_Embed::shortcode()
     *
     * @param  mixed   $value   The URL to cache.
     * @param  integer $post_id The post ID to save against.
     * @param  array   $field   The field structure.
     * @return string|null The embed HTML on success, otherwise the original URL.
     */
    private static function acf_oembed_get(mixed $value, string|int $post_id, array $field): string|false|null
    {
        if (empty($value)) return $value;

        /** @var \WP_Embed $wp_embed */
        global $wp_embed;

        $attr = [
            'width'  => $field['width'],
            'height' => $field['height'],
        ];

        remove_filter('embed_oembed_html', 'Roots\\Soil\\CleanUp\\embed_wrap');

        /**
         * Overwrite $wp_embed->post_ID with the field's $post_id (if it's an integer)
         */
        $__wp_embed_post_id = $wp_embed->post_ID;
        if (is_int($post_id)) $wp_embed->post_ID = $post_id;

        $html = $wp_embed->shortcode($attr, $value);

        /** Reset $wp_embed->post_ID to it's previous value */
        $wp_embed->post_ID = $__wp_embed_post_id;

        add_filter('embed_oembed_html', 'Roots\\Soil\\CleanUp\\embed_wrap');

        return $html ?: $value;
    }
}
