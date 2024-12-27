<?php

declare(strict_types=1);

/**
 * php-scoper config
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 */

/** @var Symfony\Component\Finder\Finder $finder */
$finder = Isolated\Symfony\Component\Finder\Finder::class;

/** The project root dir, where the composer.json file is */
$rootDir = dirname(__DIR__);

/** Read the project's composer.json */
$composerJSON = json_decode(file_get_contents("$rootDir/composer.json"), true);

$devDependencies = array_filter(
    array_map(
        fn(string $packageName) => "$rootDir/vendor/$packageName",
        array_keys($composerJSON['require-dev'] ?? [])
    ),
    fn(string $dir) => is_dir($dir)
);

/** Do not prefix dev dependencies */
$excludeFiles = empty($devDependencies) ? [] : array_map(
    static fn(SplFileInfo $fileInfo) => $fileInfo->getPathName(),
    iterator_to_array(
        $finder::create()->files()->in($devDependencies),
        false,
    ),
);

/**
 * Read WordPress excludes from sniccowp/php-scoper-wordpress-excludes
 * @see https://github.com/humbug/php-scoper/blob/main/docs/further-reading.md#wordpress-support
 */
function getWpExcludes(): array
{
    $baseDir = dirname(__DIR__) . '/php-scoper-wordpress-excludes';

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
    'prefix' => 'RH\AdminUtils\Scoped',
    /** prevent double scoping */
    'exclude-namespaces' => ['RH\AdminUtils'],

    'exclude-files' => [...$excludeFiles],

    'exclude-classes' => [...$wpClasses, 'WP_CLI'],
    'exclude-functions' => [...$wpFunctions],
    'exclude-constants' => [...$wpConstants, 'true', 'false'],

    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,
];
