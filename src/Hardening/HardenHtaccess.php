<?php

namespace RH\AdminUtils\Hardening;

use Exception;
use WP_CLI;

final class HardenHtaccess
{
    /** Bump the version suffix to re-trigger hardening on already-hardened sites */
    private static $htaccessHardenedOption = 'rhau-hardened-v1';

    public static function init()
    {
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
        /** @phpstan-ignore booleanAnd.rightAlwaysTrue */
        return defined('WP_CLI') && WP_CLI;
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
        return !(bool) get_network_option(null, self::$htaccessHardenedOption);
    }

    /**
     * Print a notice to administrators to harden the website via .htaccess directives
     */
    private static function showHtaccessHardeningNotice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!self::isApache() || !self::needsHtaccessHardening()) {
            return;
        }

        $url = wp_nonce_url(add_query_arg('rhau-action', 'harden-htaccess'), 'rhau-harden-htaccess');

        ob_start(); ?>
        <div class="notice notice-info">
            <p>
                <?php _e('This site should be hardened using <code>.htaccess</code> directives.', 'rh-admin-utils') ?>

                <a
                    class="button-primary"
                    href="<?= $url ?>">
                    <?= __('Apply directives now', 'rh-admin-utils') ?>
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
     * Get a message from a store, reusable in both HTML and WP CLI
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
    private static function showManualHardeningNotice(string $reason = ''): void
    {
        ob_start(); ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php echo esc_html(self::getMessage('could_not_update_htaccess')) ?>
                <?php if ($reason !== '') : ?>
                    <br><em><?php echo wp_kses_post($reason) ?></em>
                <?php endif; ?>
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
        if (!self::isHardenHtaccessActionUrl()) {
            return;
        }

        if (!current_user_can('edit_others_posts')) {
            wp_die(
                __('Sorry, you are not allowed to harden this site.', 'rh-admin-utils'),
                '',
                ['response' => 403]
            );
        }

        check_admin_referer('rhau-harden-htaccess');

        $directives = self::getHardeningDirectives();

        try {
            self::writeToHtaccess($directives);
            add_action('admin_notices', self::showHardenSuccessNotice(...));
        } catch (Exception $e) {
            add_action('admin_notices', fn () => self::showManualHardeningNotice($e->getMessage()));
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
        if (!self::isWpCli()) {
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
        // Ensure get_home_path() is declared
        require_once ABSPATH . 'wp-admin/includes/file.php';

        if (self::isWpCli()) {
            $_SERVER['SCRIPT_FILENAME'] = ABSPATH;
        }

        $filePath = get_home_path() . '.htaccess';

        return $filePath;
    }

    /**
     * Harden the site via .htaccess, then make sure the site still responds.
     *
     * Apache exposes no API for reading `AllowOverride`, so the only way to find out
     * whether these directives are permitted is to apply them and request the site:
     * a directive the vhost disallows makes Apache return a 500 for the whole
     * directory. The previous file is restored verbatim if that happens.
     *
     * @throws Exception
     */
    private static function writeToHtaccess(string $directives): void
    {
        // Ensure insert_with_markers() is declared
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        $htaccessFile = self::getHtaccessFilePath();

        if (!self::isWritable($htaccessFile)) {
            throw new Exception(sprintf("The <code>.htaccess</code> is not writable"));
        }

        /** Establish that the site responds before touching anything */
        $respondedBefore = self::siteResponds();

        $backup = file_exists($htaccessFile) ? file_get_contents($htaccessFile) : null;

        if (!insert_with_markers($htaccessFile, 'AdminUtils\Hardening', explode("\n", trim($directives)))) {
            throw new Exception(sprintf("Could not update the <code>.htaccess</code> file"));
        }

        /**
         * Only trust the check if the site was reachable to begin with. Loopback
         * requests are blocked on plenty of hosts, and treating that as a failure
         * would mean those sites could never be hardened at all.
         */
        if ($respondedBefore && !self::siteResponds()) {
            self::restoreHtaccess($htaccessFile, $backup);
            throw new Exception(
                'The directives made the site return a server error, so they were rolled back. '
                . 'The vhost is most likely not allowing them via <code>AllowOverride</code>.'
            );
        }

        update_network_option(null, self::$htaccessHardenedOption, true);
    }

    /**
     * Request the home URL and check that the site isn't erroring
     */
    private static function siteResponds(): bool
    {
        $response = wp_remote_get(home_url('/'), [
            'timeout' => 10,
            'redirection' => 0,
            'sslverify' => false,
            'headers' => ['Cache-Control' => 'no-cache'],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        return wp_remote_retrieve_response_code($response) < 500;
    }

    /**
     * Put the .htaccess file back the way it was before we wrote to it
     */
    private static function restoreHtaccess(string $file, ?string $backup): void
    {
        if ($backup === null) {
            @unlink($file);
            return;
        }

        file_put_contents($file, $backup);
    }

    /**
     * Hardening directives. Uses nowdoc to bypass any parsing
     * @see https://stackoverflow.com/a/36525712/586823
     */
    private static function getHardeningDirectives(): string
    {
        return <<<'EOF'
            # Disable directory listing
            Options -Indexes

            # Block direct access to sensitive file types
            <FilesMatch "\.(?i:sql|ini|log|sh|sql\.gz|env)$">
                Require all denied
            </FilesMatch>

            # Block access to version-revealing files
            <FilesMatch "^(readme\.html|license\.txt)$">
                Require all denied
            </FilesMatch>

            # Block access to dotfiles (e.g. .git, .env, .htpasswd)
            <FilesMatch "^\.">
                Require all denied
            </FilesMatch>

            # Security headers
            <IfModule mod_headers.c>
                # Prevent MIME type sniffing
                Header always set X-Content-Type-Options "nosniff"
                # Disallow embedding in iframes from other origins
                Header always set X-Frame-Options "SAMEORIGIN"
                # Control referrer information sent with requests
                Header always set Referrer-Policy "strict-origin-when-cross-origin"
            </IfModule>
            EOF;
    }

}
