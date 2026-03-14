<?php

namespace RH\AdminUtils\Hardening;

/**
 * Hide the WordPress version in the frontend
 */
final class ObfuscateVersion
{
    public static function init()
    {
        remove_action('wp_head', 'wp_generator');
        add_filter('script_loader_src', self::obfuscateVersionQueryParam(...), 9999);
        add_filter('style_loader_src', self::obfuscateVersionQueryParam(...), 9999);
    }

    /**
     * Replace any `ver=` query param with a hashed version in script/style loader tags
     */
    private static function obfuscateVersionQueryParam(string $src): string
    {
        parse_str(wp_parse_url($src, PHP_URL_QUERY) ?? '', $params);

        if (empty($params['ver'])) {
            return $src;
        }

        $hash = substr(md5($params['ver'] . wp_salt('rh-admin-utils')), 0, 8);
        $src = remove_query_arg('ver', $src);
        $src = add_query_arg('v', $hash, $src);

        return $src;
    }
}
