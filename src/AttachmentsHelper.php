<?php

namespace RH\AdminUtils;

/**
 * A class to improve working with WP attachments
 */
class AttachmentsHelper
{
    /**
     * Init
     */
    public static function init()
    {
        add_filter('ajax_query_attachments_args', [__CLASS__, 'ajax_query_attachments_args']);
        add_action('pre_get_posts', [__CLASS__, 'pre_get_posts']);
        add_filter('acf/load_value/type=image', self::load_attachment_value(...));
        add_filter('acf/load_value/type=file', self::load_attachment_value(...));
        add_filter('acf/load_value/type=gallery', self::load_gallery_value(...));
    }

    /**
     * Return null for trashed or deleted attachments in ACF image and file fields
     */
    private static function load_attachment_value(mixed $value): mixed
    {
        if (empty($value) || !is_numeric($value)) {
            return $value;
        }

        return self::is_available((int) $value) ? $value : null;
    }

    /**
     * Remove trashed or deleted attachments from ACF gallery fields
     */
    private static function load_gallery_value(mixed $value): mixed
    {
        if (empty($value) || !is_array($value)) {
            return $value;
        }

        _prime_post_caches(array_map('intval', $value), false, false);

        return array_values(array_filter($value, fn ($id) => self::is_available((int) $id)));
    }

    /**
     * Check if an attachment exists and is not trashed
     */
    private static function is_available(int $id): bool
    {
        $status = get_post_status($id);

        return $status !== false && $status !== 'trash';
    }

    /**
     * Allow searching for "id:12345" on upload.php list view
     */
    public static function pre_get_posts(\WP_Query $query): void
    {
        global $pagenow;
        if (!$query->is_main_query()) {
            return;
        }
        if (!is_admin()) {
            return;
        }
        if ($pagenow !== 'upload.php') {
            return;
        }

        if (!$post = self::get_post_from_searchstring($query->get('s') ?: '')) {
            return;
        }

        $query->is_search = false;
        $query->set('s', '');
        $query->set('post__in', [$post->ID]);
    }

    /**
     * Allow searching for "id:12345" on upload.php grid view
     */
    public static function ajax_query_attachments_args(array $args): array
    {
        $post = self::get_post_from_searchstring($args['s'] ?? null);

        if (!$post) {
            return $args;
        }

        /** Convert $args to disable the search and instead return the matched post */
        $args['post__in'] = [$post->ID];

        unset($args['s']);

        return $args;
    }

    /**
     * Try to find a matching post for search strings matching this pattern
     *
     * - "id:12345"
     * - "Id:12345"
     * - "ID:12345"
     */
    private static function get_post_from_searchstring(?string $search_string): ?\WP_Post
    {
        if (!$search_string) {
            return null;
        }

        if (!preg_match('/(?<=^id:)(?P<id>\d.+?)(?=\D|$)/i', $search_string, $matches)) {
            return null;
        }

        /** Activate to debug */
        // wp_send_json_success(['matches' => $matches]);

        if (!$post = get_post(intval($matches['id']))) {
            return null;
        }

        return $post;
    }
}
