<?php

namespace RH\AdminUtils;

use Exception;
use WP_CLI;

final class Hardening
{
    /** Bump the version suffix to re-trigger hardening on already-hardened sites */
    private static $htaccessHardenedOption = 'rhau-hardened-v1';

    public static function init()
    {
        add_filter('xmlrpc_enabled', '__return_false');

        /** Hide the WordPress version */
        remove_action('wp_head', 'wp_generator');
        add_filter('script_loader_src', self::obfuscateVersionQueryParam(...), 9999);
        add_filter('style_loader_src', self::obfuscateVersionQueryParam(...), 9999);

        /** Hardening via .htaccess */
        add_action('admin_init', self::maybeApplyHtaccessHardening(...));
        add_action('admin_notices', self::showHtaccessHardeningNotice(...));

        if (self::isWpCli()) {
            WP_CLI::add_command('rhau harden:htaccess', self::wpCliApplyHtaccessHardening(...));
        }
    }

    /**
     * Currently within WP CLI?
     */
    private static function isWpCli()
    {
        return defined('WP_CLI') && WP_CLI;
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
     * Check if currently running on Apache
     */
    private static function isApache(): bool
    {
        if (function_exists('apache_get_modules')) {
            return true;
        }
        $software = $_SERVER['SERVER_SOFTWARE'] ?? '';
        return stripos($software, 'apache') !== false;
    }

    /**
     * Check if the site is already hardened
     */
    private static function needsHtaccessHardening(): bool
    {
        return !(bool) get_option(self::$htaccessHardenedOption);
    }

    /**
     * Print a notice to administrators to harden the website via .htaccess directives
     */
    private static function showHtaccessHardeningNotice()
    {
        if (!self::isApache() || !self::needsHtaccessHardening()) {
            return;
        }

        $url = wp_nonce_url(add_query_arg('rhau-action', 'harden-htaccess'), 'rhau-harden-htaccess');

        ob_start(); ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('This site can be hardened using <code>.htaccess</code> directives:', 'rh-admin-utils') ?>
                <?php echo self::renderCodeBlock(self::getHardeningDirectives()) ?>

                <a
                    class="button-primary"
                    href="<?= $url ?>">
                    <?= __('Apply directives automatically', 'rh-admin-utils') ?>
                </a>
            </p>
        </div>
        <?php echo ob_get_clean();
    }

    /**
     * Render source code in a block
     */
    private static function renderCodeBlock(string $code): string
    {
        ob_start() ?>
        <pre style="
            border-radius: 3px;
            background: rgb(0 0 0 / 0.1);
            padding: 1rem;
            border: 1px solid rgb(0 0 0 / 0.1);"><?php echo esc_html($code) ?></pre>
        <?php return ob_get_clean();
    }

    /**
     * Show a notice when the website was successfully hardened
     */
    private static function showHardenSuccessNotice(): void
    {
        ob_start(); ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php echo self::getMessage('successfully_updated_htaccess') ?>
            </p>
        </div>
        <?php echo ob_get_clean();
    }

    /**
     * Get a message from a store, reusable
     */
    private static function getMessage(string $key): string
    {
        $map = [
            'successfully_updated_htaccess' => __('Successfully updated the .htaccess file', 'rh-admin-utils'),
            'could_not_update_htaccess' => __('Could not update the .htaccess file. Copy and paste manually:', 'rh-admin-utils')
        ];

        return $map[$key] ?? '';
    }

    /**
     * Show a notice for manually applying the .htaccess hardening code
     */
    private static function showManualHardeningNotice(): void
    {
        ob_start(); ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php self::getMessage('could_not_update_htaccess') ?>
                <?php echo self::renderCodeBlock(self::getHardeningDirectives()) ?>
            </p>
        </div>
        <?php echo ob_get_clean();
    }

    /**
     * Check if we are currently on ?action=rhau-harden-site
     */
    private static function isHardenHtaccessActionUrl(): bool
    {
        $action = $_GET['rhau-action'] ?? null;
        return is_admin() && $action === 'harden-htaccess';
    }

    /**
     * Write hardening directives to the .htaccess file
     */
    private static function maybeApplyHtaccessHardening(): void
    {
        delete_option(self::$htaccessHardenedOption);

        if (!self::isHardenHtaccessActionUrl()) {
            return;
        }

        check_admin_referer('rhau-harden-htaccess');

        $directives = self::getHardeningDirectives();

        try {
            self::writeToHtaccess($directives);
            add_action('admin_notices', self::showHardenSuccessNotice(...));
        } catch (Exception $e) {
            add_action('admin_notices', self::showManualHardeningNotice(...));
        }
    }

    /**
     * Write hardening directives to the .htaccess file.
     *
     * ## OPTIONS
     *
     * [--force]
     * : Apply even if the site is already marked as hardened.
     *
     * ## EXAMPLES
     *
     *     wp rhau harden:htaccess
     *     wp rhau harden:htaccess --force
     */
    private static function wpCliApplyHtaccessHardening($args, array $options): void
    {
        if (!rhau()->is_wp_cli()) {
            throw new Exception('%s can only be invoked via WP CLI', __METHOD__);
        }

        $force = $options['force'] ?? false;

        if (!self::needsHtaccessHardening() && !$force) {
            WP_CLI::success('.htaccess already hardened');
            return;
        }

        $directives = self::getHardeningDirectives();

        try {
            self::writeToHtaccess($directives);
            WP_CLI::success(self::getMessage('successfully_updated_htaccess'));
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage(), false);
            WP_CLI::line(self::getMessage('could_not_update_htaccess'));
            WP_CLI::error_multi_line(explode("\n", $directives));
        }

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
     * Get the path to the .htaccess file.
     * Augments $_SERVER['SCRIPT_FILENAME'] in wp cli to make get_home_path work
     *
     * @see https://github.com/wp-cli/rewrite-command/blob/e3b67911c92aca8e07692b515594af33301efcc9/src/Rewrite_Command.php#L329-L330
     */
    private static function getHtaccessFilePath(): string
    {
        if (self::isWpCli()) {
            $_SERVER['SCRIPT_FILENAME'] = ABSPATH;
        }

        $filePath = get_home_path() . '.htaccess';

        return $filePath;
    }

    /**
     * Harden the site via .htaccess
     *
     * @throws Exception
     */
    private static function writeToHtaccess(string $directives): void
    {
        require_once(ABSPATH . 'wp-admin/includes/misc.php');

        $htaccessFile = self::getHtaccessFilePath();

        if (!self::isWritable($htaccessFile)) {
            throw new Exception(sprintf("The <code>.htaccess</code> is not writable."));
        }

        // add_filter('insert_with_markers_inline_instructions', '__return_empty_array');
        insert_with_markers($htaccessFile, self::class, explode("\n", trim($directives)));
        // remove_filter('insert_with_markers_inline_instructions', '__return_empty_array');

        update_option(self::$htaccessHardenedOption, true);
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
