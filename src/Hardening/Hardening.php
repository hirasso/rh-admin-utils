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
        add_filter('auto_update_plugin', self::auto_update_plugin(...));

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
     * Keep plugin auto-updates off if file mods are disallowed.
     *
     * Allowing the `automatic_updater` context above re-enables the whole updater,
     * not just core, so it is asked with our own context here. Null is passed
     * through so WordPress can still tell that nothing forced a decision.
     */
    private static function auto_update_plugin(?bool $update): ?bool
    {
        if (!wp_is_file_mod_allowed('rhau_auto_update_plugin')) {
            return false;
        }

        return $update;
    }
}
