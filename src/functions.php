<?php

declare(strict_types=1);

namespace RH\AdminUtils;

/**
 * Debug helpers, namespaced to this plugin.
 *
 * Strauss prefixes symfony/var-dumper's global `dump()`/`dd()` to `rhau_vendor_*`, so
 * nothing global is declared by the dependency itself and no other plugin's copy can
 * collide with ours. These always route to the copy this plugin ships, whatever else
 * the site has loaded.
 *
 * Code inside this namespace resolves to these unqualified. Other namespaces import
 * them via `use function RH\AdminUtils\dd;`. For the global variants, which are what
 * themes and other plugins get, see global-functions.php.
 */

/**
 * Dump the given variables
 */
function dump(mixed ...$vars): mixed
{
    return rhau_vendor_dump(...$vars);
}

/**
 * Dump the given variables and stop execution
 */
function dd(mixed ...$vars): never
{
    rhau_vendor_dd(...$vars);
}
