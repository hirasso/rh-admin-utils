<?php

namespace RH\AdminUtils\Hardening;

/**
 * Hardens WordPress
 */
final class Hardening
{
    public static function init()
    {
        add_filter('xmlrpc_enabled', '__return_false');

        add_filter('file_mod_allowed', self::file_mod_allowed(...), 10, 2);
        add_filter('auto_update_plugin', self::maybe_disallow_auto_update(...));
        add_filter('auto_update_theme', self::maybe_disallow_auto_update(...));

        UserEnumeration::init();
        HardenHtaccess::init();
        ObfuscateVersion::init();
        TrustedAdmins::init();
    }

    /**
     * Revert DISALLOW_FILE_MODS=true in certain contexts:
     *
     *  - the automatic updater is currently running
     *  - /wp-admin/update-core.php is accessed directly
     */
    private static function file_mod_allowed(bool $allowed, string $context): bool
    {
        global $pagenow;

        if ($context === 'automatic_updater' || $pagenow === 'update-core.php') {
            return true;
        }

        return $allowed;
    }

    /**
     * Keep plugin and theme auto-updates off if file mods are disallowed.
     *
     * Allowing the `automatic_updater` context above re-enables the whole updater,
     * not just core, so it is asked with our own context here. Null is passed
     * through so WordPress can still tell that nothing forced a decision.
     */
    private static function maybe_disallow_auto_update(?bool $update): ?bool
    {
        if (!wp_is_file_mod_allowed('rhau_auto_update')) {
            return false;
        }

        return $update;
    }
}
