<?php

declare(strict_types=1);

namespace RH\AdminUtils;

class BlockUserEnumeration
{
    public static function init(): void
    {
        add_action('pre_get_posts', self::disableAuthorArchives(...));
        add_filter('rest_endpoints', self::blockRestUsersEndpoint(...));
        add_filter('wp_sitemaps_add_provider', self::blockSitemapUsersProvider(...), 10, 2);
    }

    private static function isEnabled(): bool
    {
        return (bool) apply_filters('rhau/block_user_enumeration', true);
    }

    /**
     * Disable author archives without giving away that they exist (404 vs. 301/2)
     *
     * The following command should return a 404:
     * `curl -IL https://schulz-und-schulz.test/?author=1`
     */
    private static function disableAuthorArchives(\WP_Query $query): void
    {
        if (!self::isEnabled()) {
            return;
        }

        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->is_author() || isset($_GET['author'])) {
            $query->set_404();
            status_header(404);
        }
    }

    /**
     * Remove users from the wp-sitemap.xml
     *
     * This must return \"rest_no_route\": `curl -s https://example.com/wp-sitemap.xml | grep users`
     */
    private static function blockSitemapUsersProvider(\WP_Sitemaps_Provider|false $provider, string $name): \WP_Sitemaps_Provider|false
    {
        if (!self::isEnabled()) {
            return $provider;
        }

        if ($name === 'users') {
            return false;
        }

        return $provider;
    }

    /**
     * Completely remove the WP JSON users endpoints
     *
     * This must return "rest_no_route": `curl -s https://example.com/wp-json/wp/v2/users | jq`
     */
    private static function blockRestUsersEndpoint(array $endpoints): array
    {
        if (!self::isEnabled()) {
            return $endpoints;
        }

        if (current_user_can('list_users')) {
            return $endpoints;
        }

        return array_filter(
            $endpoints,
            fn (string $key) => ! str_starts_with($key, '/wp/v2/users'),
            ARRAY_FILTER_USE_KEY
        );
    }
}
