<?php

/**
 * Plugin Name:     ACF oEmbed Cache
 * Description:     Cache ALL ACF oEmbed responses, no matter in what context
 * Version:         1.0.0
 * Requires PHP:    8.0
 * Author:          Rasso Hilber
 * Author URI:      https://rassohilber.com/
 */

/**
 * This plugin helps with caching ACF oEmbed fields.
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

class ACFOembedCache
{
    private static string $cache_post_type = 'rhau-oembed-cache';
    /** Init */
    public static function init()
    {
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
            'show_ui' => current_user_can('administrator'),
            'labels' => [
                'name' => 'oEmbed Cache',
            ],
            'supports' => ['nothing'],
            'menu_position' => 1000,
            'show_in_menu' => "edit.php?post_type=acf-field-group",
            'show_in_rest' => false,
        ]);
        add_action('add_meta_boxes', [__CLASS__, 'meta_boxes']);
        add_action('deleted_post', [__CLASS__, 'deleted_cache_post'], 10, 2);
        add_filter("get_user_option_screen_layout_{$post_type}", fn ($columns) => 1);
        add_action('current_screen', [__CLASS__, 'redirect_cache_post_edit_php']);
        add_filter('acf/get_post_types', [__CLASS__, 'acf_get_post_types'], 10, 2);
    }

    /**
     * Don't allow ACF custom fields to be attached to the cache post type
     */
    public static function acf_get_post_types(array $post_types, array $args): array
    {
        $post_types = array_filter(
            $post_types,
            fn ($post_type) => $post_type !== self::$cache_post_type
        );
        return $post_types;
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
        remove_filter('acf/format_value/type=oembed', [$oembed_field, 'format_value']);
    }

    /** Fetch the cached oEmbed HTML; Replaces the original method */
    public static function format_value_oembed($value, $post_id, $field)
    {
        if (empty($value)) {
            return $value;
        }

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
        if (empty($value)) {
            return $value;
        }

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
        if (empty($value)) {
            return $value;
        }

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
        if (is_int($post_id) && get_post($post_id)) {
            return $post_id;
        }

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
            'fields' => 'ids',
        ]);

        $post_id = $query->posts[0] ?? wp_insert_post([
            'post_title' => 'oEmbed cache',
            'post_type' => self::$cache_post_type,
            'post_status' => 'publish',
        ]);
        if ($post_id instanceof \WP_Error) {
            $error_message = $post_id->get_error_message();
            throw new \Error("[acf-oembed-cache] Couldn't create the global acf oembed post. $error_message");
        }
        return $post_id;
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
            fn ($item) => esc_html($item[0]),
            $cache_entries
        );

        ?>
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
            .post-type-rhau-oembed-cache #screen-meta-links,
            .post-type-rhau-oembed-cache .wp-heading-inline {
                display: none !important;
            }
        </style>

        <?php if (empty($cache_entries)) : ?>
            <p>
                <?php _e('There global ACF oEmbed cache is currently empty.', RHAU_TEXT_DOMAIN) ?>
            </p>
        <?php else : ?>
            <p>
                <?php printf(
                    __('There are %d oEmbed responses cached globally.', RHAU_TEXT_DOMAIN),
                    count($cache_entries)
                ) ?>
            </p>
            <pre id="rhau-oembed-cache-output"><?= var_dump($cache_entries) ?></pre>

            <a
                class="button button-primary button-large"
                href="<?= get_delete_post_link(get_post(), null, true) ?>">
                Flush oEmbed Cache
            </a>

<?php endif;
    }

    /**
     * Re-create the cache post if it's deleted (flushed)
     */
    public static function deleted_cache_post(int $post_id, \WP_Post $post): void
    {
        if ($post->post_type !== self::$cache_post_type) {
            return;
        }
        /** recreates the cache post if none exists */
        self::get_oembed_cache_post_id();
    }

    /**
     * Redirects edit.php for the cache post type directly to the cache post
     */
    public static function redirect_cache_post_edit_php(): void
    {
        if (rhau()->getCurrentScreen()?->id !== 'edit-rhau-oembed-cache') {
            return;
        }
        $post_id = self::get_oembed_cache_post_id();
        $edit_link = get_edit_post_link($post_id, 'raw');
        \wp_safe_redirect($edit_link);
        exit;
    }
}
