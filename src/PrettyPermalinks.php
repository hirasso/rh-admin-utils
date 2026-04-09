<?php

namespace RH\AdminUtils;

/**
 * Allow pretty permalinks for post stati like draft, future etc.
 */
class PrettyPermalinks
{
    public static function init(): void
    {
        add_filter('post_type_link', self::filter_permalink(...), 10, 3);
        add_filter('page_link', self::filter_permalink(...), 10, 3);
        add_filter('request', self::request(...));
        add_action('after_setup_theme', self::after_setup_theme(...));
    }

    /**
     * Help detecting conflicts
     */
    private static function after_setup_theme(): void
    {
        if (!rhau()->is_dev()) {
            return;
        }
        if (class_exists('\Site\Base\PrettyPermalinks')) {
            throw new \Error('[rh-admin-utils] Please remove the class \Site\Base\PrettyPermalinks. It conflicts with this plugin.');
        }
    }

    /**
     * Ensure pretty permalinks for posts with post stati other than 'publish'
     *
     * @see https://wordpress.stackexchange.com/a/280592/
     * @see https://www.wp-tweaks.com/link-to-a-draft-wordpress-permalinks/
     */
    private static function filter_permalink(
        string $permalink,
        int|\WP_Post $post,
        bool $leavename,
    ): string {
        /** for filter recursion (prevent infinite loops) */
        static $recursing = false;

        if ($recursing) {
            return $permalink;
        }

        if (!$post = get_post($post)) {
            return $permalink;
        }

        /** store the query params for later */
        parse_str(parse_url($permalink, PHP_URL_QUERY) ?? '', $params);

        /** Allow pretty permalinks for these post stati */
        $post_stati = apply_filters(
            'rhau/pretty_permalinks/post_stati',
            ['private', 'draft', 'auto-draft', 'pending'],
            get_post_type($post)
        );

        if (!in_array($post->post_status, $post_stati)) {
            return $permalink;
        }

        /** From now on, we don't want recursion to happen */
        $recursing = true;

        /** Create a clone to bypass the object cache and set it's post status to 'publish' */
        $clone = clone $post;
        $clone->post_status = 'publish';

        /** Make sure we have a post name available */
        if (empty($clone->post_name)) {
            $slug = sanitize_title($clone->post_title, "{$clone->ID}");
            $clone->post_name = wp_unique_post_slug(
                slug: $slug,
                post_id: $clone->ID,
                post_status: 'publish',
                post_type: $clone->post_type,
                post_parent: $clone->post_parent,
            );
        }

        /** Get a fresh permalink for the (now published) post */
        $permalink = get_permalink($clone, $leavename);

        if (!$post->post_name) {
            $permalink = add_query_arg($params, $permalink);
        }

        /** Reset the recusion prevention */
        $recursing = false;

        return $permalink;
    }

    /**
     * Filter the request to ignore the post name if the request
     * is a 'preview' request and contains 'p' or 'page_id'
     */
    private static function request(array $vars): array
    {
        if (is_admin()) {
            return $vars;
        }
        if (!is_user_logged_in()) {
            return $vars;
        }

        /** are we in preview mode? */
        if (($vars['preview'] ?? null) !== 'true') {
            return $vars;
        }

        /** if we have "page_id" or "p", drop anything we don't need */
        if ($vars['p'] ?? $vars['page_id'] ?? null) {
            $vars = array_intersect_key($vars, array_flip(['post_type', 'preview', 'page_id', 'p']));
        }

        return $vars;
    }
}
