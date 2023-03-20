<?php
/*
* Copyright (c) 2023 Rasso Hilber
* https://rassohilber.com
*/

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
    }

    /**
     * Allow searching for "id:12345" on upload.php list view
     *
     * @param \WP_Query $query
     * @return void
     */
    public static function pre_get_posts(\WP_Query $query): void
    {
        global $pagenow;
        if (!$query->is_main_query()) return;
        if (!is_admin()) return;
        if ($pagenow !== 'upload.php') return;

        if (!$post = self::get_post_from_searchstring($query->get('s') ?: '')) return;

        $query->is_search = false;
        $query->set('s', '');
        $query->set('post__in', [$post->ID]);
    }

    /**
     * Allow searching for "id:12345" on upload.php grid view
     *
     * @param array $args
     * @return array
     */
    public static function ajax_query_attachments_args(array $args): array
    {
        $post = self::get_post_from_searchstring($args['s'] ?? null);

        if (!$post) return $args;

        // Convert $args to disable the search and instead return the matched post
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
        if (!$search_string) return null;

        if (!preg_match('/(?<=^id:)(?P<id>\d.+?)(?=\D|$)/i', $search_string, $matches)) return null;

        // Activate to debug
        // wp_send_json_success(['matches' => $matches]);

        if (!$post = get_post(intval($matches['id']))) return null;

        return $post;
    }
}
