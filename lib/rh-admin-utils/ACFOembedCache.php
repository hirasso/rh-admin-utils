<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

/**
 * This class helps with caching ACF oEmbed fields.
 *
 * It makes use of the fact that WordPress stores oEmbed responses in post meta.
 * For global fields (from ACF options pages or similar), this plugin creates
 * a custom post type with exactly ONE post in it. That post will be used as the
 * cache container for the oEmbed responses. You can see and flush the global cache
 * by going to /wp-admin/edit.php?post_type=rhau-oembed-cache
 *
 * I also recommend you install the plugin wp-sweep to flush ALL your oEmbed caches
 * @see https://wordpress.org/plugins/wp-sweep/
 *
 * Inspiration, Discussion:
 * @see https://core.trac.wordpress.org/ticket/14759
 * @see https://support.advancedcustomfields.com/forums/topic/oembed-cache/
 * @see https://salferrarello.com/caching-wordpress-oembed-calls/
 * @see https://support.advancedcustomfields.com/forums/topic/watch-out-for-cache-issues-with-the-oembed-field/
 */

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ACFOembedCache
{
    private static string $cache_post_type = 'rhau-oembed-cache';
    /** Init */
    public static function init()
    {
        // add_filter('pre_delete_post', [__CLASS__, 'prevent_cache_post_deletion'], 10, 2);
        add_action('after_setup_theme', [__CLASS__, 'after_setup_theme']);
    }

    public static function after_setup_theme()
    {
        if (class_exists('\RAH\ThemeBase\OembedHelper')) {
            throw new \Error('[rh-admin-utils] Please remove the class \RAH\ThemeBase\OembedHelper. It conflicts with this plugin.');
        }
        add_action('init', [__CLASS__, 'register_cache_post_type']);
        add_action('acf/init', [__CLASS__, 'disable_default_oembed_format_value'], 1);
        add_filter('acf/format_value/type=oembed', [__CLASS__, 'format_value_oembed'], 20, 3);
        add_filter('acf/update_value/type=oembed', [__CLASS__, 'cache_value_oembed'], 10, 3);
    }

    /**
     * Register a custom post type for caching oembed requests outside the loop
     */
    public static function register_cache_post_type(): void
    {
        $post_type = self::$cache_post_type;
        register_post_type($post_type, [
        'public' => false,
        'show_ui' => true,
        'labels' => [
            'name' => 'oEmbed Cache'
        ],
        'supports' => ['nothing'],
        'menu_position' => 1000,
        'show_in_menu' => "edit.php?post_type=acf-field-group",
        'show_in_rest' => false
        ]);
        add_action('add_meta_boxes', [__CLASS__, 'meta_boxes']);
        add_action('deleted_post', [__CLASS__, 'deleted_cache_post'], 10, 2);
        add_filter("get_user_option_screen_layout_{$post_type}", fn($columns) => 1);
        add_action('current_screen', [__CLASS__, 'redirect_cache_post_edit_php']);
    }

    /**
     * Add a meta box to the cache post
     */
    public static function meta_boxes(): void
    {
        \remove_meta_box('submitdiv', self::$cache_post_type, 'side');

        \add_meta_box(
            id: 'rhau-oembed-cache-metabox',
            title: __('RH Admin Utils: Global oEmbed cache', RHAU_TEXT_DOMAIN),
            callback: [__CLASS__, 'render_custom_meta_box'],
            screen: 'rhau-oembed-cache"',
            context: 'normal',
            priority: 'high',
        );
    }

    /** Disables the built-in formatting for oembed fields */
    public static function disable_default_oembed_format_value(): void
    {
        /** @var \acf_field_oembed $field_type */
        $oembed_field = acf_get_field_type('oembed');
        remove_filter('acf/format_value/type=oembed', [ $oembed_field, 'format_value' ]);
    }

    /** Fetch the cached oEmbed HTML; Replaces the original method */
    public static function format_value_oembed($value, $post_id, $field)
    {
        if (empty($value)) return $value;

        $value = self::acf_oembed_get($value, $post_id, $field);

        if (
            str_contains($value, '<iframe')
            && preg_match('/(vimeo.com|youtube.com)/', $value)
        ) {
            return strip_tags($value, '<iframe>');
        }

        return $value;
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

        if (is_null($__wp_embed_post_id)) {
            $wp_embed->post_ID = self::get_oembed_post_id($post_id);
        }

        $html = $wp_embed->shortcode($attr, $value);

        /** Reset $wp_embed->post_ID to it's previous value */
        $wp_embed->post_ID = $__wp_embed_post_id;

        add_filter('embed_oembed_html', 'Roots\\Soil\\CleanUp\\embed_wrap');

        return $html ?: $value;
    }

    /**
     * Get the post id for the oembed cache. Falls back to a custom hidden
     * utility post type if the $post_id is a string (ACF does this for options pages, for example)
     */
    private static function get_oembed_post_id(mixed $post_id): int
    {
        if (is_int($post_id) && get_post($post_id)) return $post_id;

        $cache_post_id = self::get_oembed_cache_post_id();

        return $cache_post_id;
    }

    /**
     * Get the oEmbed cache post
     */
    private static function get_oembed_cache_post_id(): int
    {
        $query = new \WP_Query([
            'post_type' => self::$cache_post_type,
            'posts_per_page' => 1,
            'suppress_filters' => true,
            'fields' => 'ids'
        ]);

        return $query->posts[0] ?? self::create_cache_post();
    }

    /**
     * Prevents the oembed cache post to be deleted, like, ever
     */
    public static function prevent_cache_post_deletion(mixed $check, \WP_Post $post): mixed
    {
        if ($post->post_type === self::$cache_post_type) return false;
        return $check;
    }

    /**
     * Renders a metabox with some instructions
     */
    public static function render_custom_meta_box(\WP_Post $post): void
    {
        $cache_entries = array_filter(
            get_post_meta($post->ID) ?: [],
            fn ($key) => str_starts_with($key, '_oembed_') && !str_starts_with($key, '_oembed_time_'),
            ARRAY_FILTER_USE_KEY
        );
        $cache_entries = array_map(
            fn($item) => esc_html($item[0]),
            $cache_entries
        );

        ?>
        <p>
            <?php printf(
                __('Currently, there are %d oEmbed responses cached globally (from fields in options pages or similar).', RHAU_TEXT_DOMAIN),
                count($cache_entries)
            ) ?>
        </p>
        <style>
            #rhau-oembed-cache-output {
                width: 100%;
                overflow: auto;
                background: #eee;
                padding: 10px;
                box-sizing: border-box;
                border-radius: 5px;
            }
            .post-type-rhau-oembed-cache #post-body-content,
            .post-type-rhau-oembed-cache .page-title-action,
            .post-type-rhau-oembed-cache .wp-heading-inline {
                display: none !important;
            }
            .post-type-rhau-oembed-cache #poststuff #post-body {
                /* margin-right: 0; */
            }
        </style>
        <pre id="rhau-oembed-cache-output"><?= var_dump($cache_entries) ?></pre>

        <a
            class="button button-primary button-large"
            href="<?= get_delete_post_link(get_post(), null, true) ?>">
            Flush oEmbed Cache
        </a>

        <?php
    }

    /**
     * Re-create the cache post if it's deleted (flushed)
     */
    public static function deleted_cache_post(int $post_id, \WP_Post $post): void
    {
        if ($post->post_type !== self::$cache_post_type) return;
        /** recreates the cache post if none exists */
        self::get_oembed_cache_post_id();
    }

    /**
     * Create the cache post
     */
    private static function create_cache_post(): int
    {
        return wp_insert_post([
            'post_title' => 'oEmbed cache',
            'post_type' => self::$cache_post_type,
            'post_status' => 'publish'
        ]);
    }

    /**
     * Redirects edit.php for the cache post type directly to the cache post
     */
    public static function redirect_cache_post_edit_php(): void
    {
        if (get_current_screen()->id !== 'edit-rhau-oembed-cache') return;
        $post_id = self::get_oembed_cache_post_id();
        $edit_link = get_edit_post_link($post_id, 'raw');
        \wp_safe_redirect($edit_link);
        exit;
    }
}
