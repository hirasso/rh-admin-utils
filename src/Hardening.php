<?php

namespace RH\AdminUtils;

use Exception;

final class Hardening
{
    /** Bump the version suffix to re-trigger hardening on already-hardened sites */
    private static $hardenedOptionName = 'rhau-hardened-v1';

    public static function init()
    {
        add_filter('xmlrpc_enabled', '__return_false');

        /** Hide the WordPress version */
        remove_action('wp_head', 'wp_generator');
        add_filter('script_loader_src', self::obfuscateVersionQueryParam(...), 9999);
        add_filter('style_loader_src', self::obfuscateVersionQueryParam(...), 9999);

        add_action('admin_init', self::maybeHardenSite(...));
        add_action('admin_notices', self::printAdminNotices(...));
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
    private static function printAdminNotices()
    {
        if (self::isHardened()) {
            return;
        }

        if (!self::isHardenSiteActionUrl()) {
            $message = sprintf(__('This site is not yet hardened.', 'rh-admin-utils'));
            $buttonLabel = sprintf(__('Harden now', 'rh-admin-utils'));
            $url = wp_nonce_url(add_query_arg('action', 'rhau-harden-site'), 'rhau-harden-site');

            echo <<<HTML
            <div class="notice notice-success is-dismissible">
                <p>$message <a class="button-primary" href="$url">$buttonLabel</a></p>
            </div>
            HTML;
        }

    }

    /**
     * Check if we are currently on ?action=rhau-harden-site
     */
    private static function isHardenSiteActionUrl(): bool
    {
        $action = $_GET['action'] ?? null;
        return is_admin() && $action === 'rhau-harden-site';
    }

    /**
     * Harden the site via .htaccess
     */
    private static function maybeHardenSite(): void
    {
        if (!self::isHardenSiteActionUrl() || !check_admin_referer('rhau-harden-site')) {
            return;
        }

        $directives = self::getHardeningDirectives();

        try {
            self::writeToHtaccess($directives);
            // update_option(self::$hardenedOptionName, true);
        } catch (Exception $e) {
            // rhau()->add_admin_notice(
            //     'rhau-hardening-directives',
            //     <<<HTML
            //     Could not write to the <code>.htaccess</code> file.
            //     Please do it manually: <pre>$directives</pre>
            //     HTML,
            //     'warning',
            // );
        }

        $notice = sprintf(<<<HTML
            Could not write to the <code>.htaccess</code> file.
            Please do it manually: <pre>%s</pre>
        HTML, esc_html($directives));

        rhau()->add_admin_notice('rhau-hardening-directives', $notice, 'warning');

    }

    /**
     * Check if a file can be written or created
     */
    private static function isWritable(string $file): bool
    {
        $dir = dirname($file);
        return is_writable($file) || (is_writable($dir) && !file_exists($file));
    }

    /**
     * Harden the site via .htaccess
     *
     * @throws Exception
     */
    private static function writeToHtaccess(string $directives): void
    {
        require_once(ABSPATH . 'wp-admin/includes/misc.php');

        $htaccessFile = get_home_path() . '.htaccess';

        if (!self::isWritable($htaccessFile)) {
            throw new Exception(sprintf("The <code>.htaccess</code> is not writable."));
        }

        add_filter('insert_with_markers_inline_instructions', '__return_empty_array');
        insert_with_markers($htaccessFile, self::class, explode("\n", trim($directives)));
        remove_filter('insert_with_markers_inline_instructions', '__return_empty_array');
    }

    /**
     * Hardening directives. Uses nowdoc to bypass any parsing
     * @see https://stackoverflow.com/a/36525712/586823
     */
    private static function getHardeningDirectives(): string
    {
        return <<<'EOF'
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
    }

}
