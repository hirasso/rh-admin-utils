<?php

declare(strict_types=1);

/** @var Symfony\Component\Finder\Finder $finder */
$finder = Isolated\Symfony\Component\Finder\Finder::class;

/**
 * Read WordPress excludes from sniccowp/php-scoper-wordpress-excludes
 * @see https://github.com/humbug/php-scoper/blob/main/docs/further-reading.md#wordpress-support
 */
function getWpExcludes(): array
{
    $baseDir = dirname(__DIR__) . '/scopeme/php-scoper-wordpress-excludes';

    $excludes = [];

    foreach (['classes', 'functions', 'constants'] as $type) {
        $excludes[] = json_decode(
            file_get_contents("$baseDir/generated/exclude-wordpress-$type.json"),
            true,
        );
    }

    return $excludes;
}

[$wpClasses, $wpFunctions, $wpConstants] = getWpExcludes();

return [
    'prefix' => 'RH\AdminUtils',

    'exclude-classes' => [...$wpClasses, 'WP_CLI'],
    'exclude-functions' => [...$wpFunctions],
    'exclude-constants' => [...$wpConstants, 'true', 'false'],

    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,
];
