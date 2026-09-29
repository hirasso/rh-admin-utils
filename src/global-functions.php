<?php

/**
 * Global debug helpers, re-served from this plugin.
 *
 * Strauss prefixes symfony/var-dumper's own globals to `rhau_vendor_*`, which keeps the
 * shipped copy conflict-free but also takes `dump()` and `dd()` away from everywhere
 * else on the site. This puts them back, delegating to the prefixed copy.
 *
 * Declared conditionally on purpose. On a site whose root composer project already
 * loaded an unprefixed symfony/var-dumper — which happens before plugins load, via
 * wp-config.php — that copy wins and this is skipped. Either way nothing is redeclared
 * and no other plugin's copy can collide with ours.
 *
 * Define `RHAU_GLOBAL_DEBUG_FUNCTIONS` as `false` in wp-config.php to opt out entirely.
 *
 * Note this file is loaded when the plugin loads, so code running earlier (wp-config.php,
 * mu-plugins) cannot use these.
 */

if (!function_exists('dump')) {
    /**
     * Dump the given variables
     */
    function dump(mixed ...$vars): mixed
    {
        return \RH\AdminUtils\dump(...$vars);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the given variables and stop execution
     */
    function dd(mixed ...$vars): never
    {
        \RH\AdminUtils\dd(...$vars);
    }
}
