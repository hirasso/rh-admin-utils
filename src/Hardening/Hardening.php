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
     * Allways allow file mods for the automatic updater,
     * even if DISALLOW_FILE_MODS is true.
     */
    private static function file_mod_allowed(bool $allowed, string $context): bool
    {
        if ($context === 'automatic_updater') {
            return true;
        }
        return $allowed;
    }
}
