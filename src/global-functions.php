<?php

/**
 * Re-serve `dump()`/`dd()` globally, since Strauss prefixes var-dumper's own.
 * Guarded: a site that already loaded an unprefixed var-dumper keeps its copy.
 */

if (!function_exists('dump')) {
    function dump(mixed ...$vars): mixed
    {
        return \RH\AdminUtils\dump(...$vars);
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        \RH\AdminUtils\dd(...$vars);
    }
}
