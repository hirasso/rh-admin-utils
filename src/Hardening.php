<?php

namespace RH\AdminUtils;

class Hardening
{
    public static function init()
    {
        add_filter('xmlrpc_enabled', '__return_false');

        /** Hide the WordPress version */
        remove_action('wp_head', 'wp_generator');
        add_filter('script_loader_src', self::obfuscateWPVersionQueryParam(...), 9999);
        add_filter('style_loader_src', self::obfuscateWPVersionQueryParam(...), 9999);
    }

    /**
     * Replace `ver={{$wp_version}}` with a hashed version in script loader tags
     */
    private static function obfuscateWPVersionQueryParam(string $src): string
    {
        $wp_version = get_bloginfo('version');

        if (str_contains($src, "ver=$wp_version")) {
            $src = remove_query_arg('ver', $src);
            $hash = substr(md5($wp_version . wp_salt('rh-admin-utils')), 0, 8);
            $src = add_query_arg('v', $hash, $src);
        }

        return $src;
    }


}
