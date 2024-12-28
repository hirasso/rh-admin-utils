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
$pluginSlug = basename($rootDir);

/** Read the project's composer.json */
$composerJSON = json_decode(file_get_contents("$rootDir/composer.json"), true);

[$wpClasses, $wpFunctions, $wpConstants] = getWpExcludes();

return [
    'prefix' => 'RH\AdminUtils\Scoped',
    /** prevent double scoping */
    'exclude-namespaces' => ['RH\AdminUtils'],
    'php-version' => '8.2',

    // 'exclude-files' => [...$excludeFiles],

    'exclude-classes' => [...$wpClasses, 'WP_CLI'],
    'exclude-functions' => [...$wpFunctions],
    'exclude-constants' => [...$wpConstants, 'true', 'false'],

    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,

    'finders' => [
        $finder::create()->files()->in('src'),
        $finder::create()
            ->files()
            ->in('vendor')
            ->ignoreVCS(true)
            ->notName('/.*\\.sh/')
            ->exclude(array_keys($composerJSON['require-dev'] ?? [])),
        $finder::create()->append([
            "$pluginSlug.php",
            'README.md',
            'CHANGELOG.md',
            ...glob('assets/*')
        ])
    ]
];

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
