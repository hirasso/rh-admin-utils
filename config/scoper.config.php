<?php

declare(strict_types=1);

/**
 * php-scoper config for creating a scoped release asset for GitHub Releases
 * This release asset serves as the source of truth for non-composer plugin updates
 * via yahnis-elsts/plugin-update-checker
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 * @see https://github.com/YahnisElsts/plugin-update-checker?tab=readme-ov-file#how-to-release-an-update-1
 */

$packageNamespace = 'RH\AdminUtils';

/** @var Symfony\Component\Finder\Finder $finder */
$finder = Isolated\Symfony\Component\Finder\Finder::class;

/** The project root dir, where the composer.json file is */
$rootDir = dirname(__DIR__);

/**
 * Read the project's composer.json
 * @var array $composerJSON
 */
$composerJSON = json_decode(file_get_contents("$rootDir/composer.json"), true);
$devDependencies = array_keys($composerJSON['require-dev'] ?? []);
preg_match('/\d+\.\d+/', $composerJSON['require']['php'], $matches);
$phpVersion = $matches[0];

/** exclude global WordPress symbols */
[$wpClasses, $wpFunctions, $wpConstants] = getWpExcludes();

/** Extra files that should make it into the scoped release */
$extraFiles = array_filter([
    'composer.json',
    'composer.lock',
    'README.md',
    'CHANGELOG.md',
    'LICENSE',
    'LICSENSE.md',
], 'file_exists');

/**
 * Return the config for php-scoper
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 */
return [
    'prefix' => "$packageNamespace\\Scoped",
    'exclude-namespaces' => [
        $packageNamespace,
        /** Exclude PluginUpdateChecker as it breaks when scoped */
        'YahnisElsts\PluginUpdateChecker',
    ],
    'php-version' => $phpVersion,

    'exclude-classes' => [...$wpClasses, WP_CLI::class],
    'exclude-functions' => [...$wpFunctions],
    'exclude-constants' => [...$wpConstants, WP_CLI::class, 'true', 'false'],

    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,

    'finders' => [
        $finder::create()->files()->in('src'),
        $finder::create()->files()->in('vendor')->ignoreVCS(true)
            ->notName('/.*\\.sh|composer\\.(json|lock)/')
            ->exclude($devDependencies)
            ->exclude('bin/'),
        $finder::create()->append(glob('*.php')),
        $finder::create()->append(glob('assets/*')),
        $finder::create()->append($extraFiles),
    ],
];

/**
 * Read WordPress excludes from sniccowp/php-scoper-wordpress-excludes
 * @see https://github.com/humbug/php-scoper/blob/main/docs/further-reading.md#wordpress-support
 */
function getWpExcludes(): array
{
    $baseDir = dirname(__DIR__) . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated';

    $excludes = [];

    foreach (['classes', 'functions', 'constants'] as $type) {
        $excludes[] = json_decode(
            file_get_contents("$baseDir/exclude-wordpress-$type.json"),
            true,
        );
    }

    return $excludes;
}
