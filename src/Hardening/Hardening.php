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

        UserEnumeration::init();
        HardenHtaccess::init();
        ObfuscateVersion::init();
    }
}
