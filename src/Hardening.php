<?php

namespace RH\AdminUtils;

final class Hardening
{
    private static $hardenedOptionName = 'rhau-hardened-v1';

    public static function init()
    {
        add_filter('xmlrpc_enabled', '__return_false');

        /** Hide the WordPress version */
        remove_action('wp_head', 'wp_generator');
        add_filter('script_loader_src', self::obfuscateVersionQueryParam(...), 9999);
        add_filter('style_loader_src', self::obfuscateVersionQueryParam(...), 9999);

        add_action('admin_init', self::maybeHardenSite(...));
        add_action('admin_notices', self::printHardeningNotice(...));
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

    /**
     * Check if the site is already hardened
     */
    private static function isHardened(): bool
    {
        return (bool) get_option(self::$hardenedOptionName);
    }

    /**
     * Print a notice to administrators to harden the website via .htaccess directives
     */
    private static function printHardeningNotice()
    {
        if (self::isHardened()) {
            return;
        }

        $message = sprintf(__('This site is not yet hardened.', 'rh-admin-utils'));
        $buttonLabel = sprintf(__('Harden now', 'rh-admin-utils'));
        $url = wp_nonce_url(add_query_arg('action', 'rhau-harden-site'), 'rhau-harden-site');

        echo <<<HTML
        <div class="notice notice-success is-dismissible">
            <p>$message <a class="button-primary" href="$url">$buttonLabel</a></p>
        </div>
        HTML;
    }

    /**
     * Harden the site via .htaccess
     */
    private static function maybeHardenSite(): void
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'rhau-harden-site' && check_admin_referer('rhau-harden-site')) {
            self::hardenSiteViaHtaccess();
        }
    }

    /**
     * Check if the .htaccess file is writable
     */
    private static function isHtaccessWritable(): bool
    {
        $homePath = get_home_path();
        if (
            is_writable($homePath)
            && !file_exists($homePath . '.htaccess')
        ) {
            return true;
        }
        return is_writable($homePath . '.htaccess');
    }

    /**
     * Harden the site via .htaccess
     */
    private static function hardenSiteViaHtaccess(): void
    {
        require_once(ABSPATH . 'wp-admin/includes/misc.php');

        if (!self::isHtaccessWritable()) {
            return;
        }

        $htaccessFile = untrailingslashit(get_home_path()) . '/.htaccess';

        /**
         * Directory hardening. Uses nowdoc to bypass any parsing
         * https://stackoverflow.com/a/36525712/586823
         */
        $content = <<<'EOF'
            <FilesMatch "\.(?i:sql|ini|log|sh|sql\.gz|env)$">
                <IfModule !mod_authz_core.c>
                    Order allow,deny
                    Deny from all
                </IfModule>
                <IfModule mod_authz_core.c>
                    Require all denied
                </IfModule>
            </FilesMatch>
            <IfModule mod_headers.c>
                Header always set X-Content-Type-Options "nosniff"
                Header always set X-Frame-Options "SAMEORIGIN"
                Header always set Referrer-Policy "strict-origin-when-cross-origin"
            </IfModule>
            EOF;

        $directives = explode("\n", trim($content));

        add_filter('insert_with_markers_inline_instructions', '__return_empty_array');
        insert_with_markers($htaccessFile, self::class, $directives);
        remove_filter('insert_with_markers_inline_instructions', '__return_empty_array');
    }

}
