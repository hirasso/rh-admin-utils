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
}
