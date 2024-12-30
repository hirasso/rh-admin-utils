<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 *
 * Optimizes the WP_Query based on recommendations found here;
 * @see https://wpvip.com/2023/04/28/wp-query-performance/
 *
 */

namespace RH\AdminUtils;

class QueryOptimizer
{
    public static function init()
    {
        add_action('pre_get_posts', [__CLASS__, 'optimize_query']);
    }

    /**
     * Optimize query if __rhau_optimize_query is set
     */
    public static function optimize_query(\WP_Query $query): void
    {
        if (!$query->get('__rhau_optimize_query')) {
            return;
        }

        $query->set('no_found_rows', true);
        $query->set('ignore_sticky_posts', true);
        $query->set('update_meta_cache', false);
        $query->set('__rhau_optimized', true);
        $query->set('__rhau_optimize_query', false);
    }

    /**
     * Directly optimize query args for queries that use 'suppress_filters'
     */
    public static function optimize_query_args(array $args): array
    {
        return array_merge($args, [
            // only setting this to signal that the query was optimized by Admin Utils
            '__rhau_optimized' => true,
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_meta_cache' => true,
        ]);
    }
}
