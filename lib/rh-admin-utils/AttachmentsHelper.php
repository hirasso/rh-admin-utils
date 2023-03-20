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
    }

    /**
     * Hook into `ajax_query_attachments_args`
     *
     * @param array $args
     * @return array
     */
    public static function ajax_query_attachments_args(array $args): array
    {
        $args = self::support_searching_for_attachment_id($args);
        return $args;
    }

    /**
     * Allows to search for an attachment ID
     *
     * @param array $args
     * @return array
     */
    private static function support_searching_for_attachment_id(array $args): array
    {
        $search_string = $args['s'] ?? null;

        if (!$search_string) return $args;

        /**
         * Match search strings starting in the shape of
         *
         * - "id:12345"
         * - "Id:12345"
         * - "ID:12345"
         *
         */
        if (!preg_match('/(?<=^id:)(?P<id>\d.+?)(?=\D|$)/i', $search_string, $matches)) return $args;

        // Activate to debug
        // wp_send_json_success(['matches' => $matches]);

        if (!$post = get_post(intval($matches['id']))) return $args;

        if ($post->post_type !== 'attachment') return $args;

        // Convert $args to disable the search and instead return the matched post
        $args['post__in'] = [$post->ID];

        unset($args['s']);

        return $args;
    }
}
