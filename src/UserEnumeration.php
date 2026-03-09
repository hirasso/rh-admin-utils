<?php

declare(strict_types=1);

namespace RH\AdminUtils;

class UserEnumeration
{
    public static function init(): void
    {
        add_action('pre_get_posts', self::disableAuthorArchives(...));
        add_filter('rest_endpoints', self::blockRestUsersEndpoint(...));
    }

    public static function disableAuthorArchives(\WP_Query $query): void
    {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        if ($query->is_author() || isset($_GET['author'])) {
            $query->set_404();
            status_header(404);
        }
    }

    public static function blockRestUsersEndpoint(array $endpoints): array
    {
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
