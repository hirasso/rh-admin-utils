<?php

declare(strict_types=1);

namespace RH\AdminUtils;

/**
 * Debug helpers routing to the prefixed var-dumper.
 * Import from other namespaces via `use function RH\AdminUtils\dd;`
 */
function dump(mixed ...$vars): mixed
{
    return rhau_vendor_dump(...$vars);
}

function dd(mixed ...$vars): never
{
    rhau_vendor_dd(...$vars);
}
