<?php

declare(strict_types=1);

namespace RH\AdminUtils;

use Exception;
use SplFileInfo;
use ZipArchive;

/**
 * php-scoper config for creating a scoped release asset for GitHub Releases
 * This release asset serves as the source of truth for non-composer plugin updates
 * via yahnis-elsts/plugin-update-checker
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 * @see https://github.com/YahnisElsts/plugin-update-checker?tab=readme-ov-file#how-to-release-an-update-1
 */

/** @var Symfony\Component\Finder\Finder $finder */
$finder = \Isolated\Symfony\Component\Finder\Finder::class;

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
$extraFiles = [...getGitArchiveables()];

/**
 * Exclude yahnis-elsts/plugin-update-checker
 * Note to self: This somehow didn't work – the prefix was still added.
 * Resorted to using a custom patcher for now.
 */
// $excludeFiles = array_map(
//     static fn (SplFileInfo $fileInfo) => $fileInfo->getPathName(),
//     array_values([...$finder::create()->files()->in('vendor/yahnis-elsts/plugin-update-checker')])
// );
$excludeFiles = [];

/**
 * Return the config for php-scoper
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 */
return [
    'prefix' => __NAMESPACE__ . '\Vendor',
    'exclude-namespaces' => [__NAMESPACE__],
    'php-version' => $phpVersion,
    'exclude-files' => $excludeFiles,

    'exclude-classes' => [...$wpClasses, 'WP_CLI'],
    'exclude-functions' => [...$wpFunctions],
    'exclude-constants' => [...$wpConstants, 'WP_CLI', 'true', 'false'],

    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,

    'finders' => [
        $finder::create()->files()->in('src'),
        $finder::create()->files()->in('vendor')->ignoreVCS(true)
            ->notName('/.*\\.sh|composer\\.(json|lock)/')
            ->exclude([
                ...$devDependencies,
                'sniccowp/php-scoper-wordpress-excludes',
                'bin/',
            ]),
        $finder::create()->append(glob('*.php')),
        $finder::create()->append(glob('assets/*')),
        $finder::create()->append($extraFiles),
    ],
    'patchers' => [
        /**
         * Remove the prefix from strings in plugin-update-checker/load-v5p5.php
         * @see https://github.com/YahnisElsts/plugin-update-checker/issues/586#issuecomment-2567753162
         */
        static function (string $filePath, string $prefix, string $content): string {
            if (preg_match('/plugin-update-checker\/load-v\d+p\d\.php/', $filePath) === false) {
                return $content;
            }
            return preg_replace('/(["\'])' . preg_quote($prefix) . '\\/', '$1', $content);
        },
    ]
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

/**
 * Get all top-level <git archive>-able files and folders.
 */
function getGitArchiveables(bool $includeDirs = false): array
{
    $entries = [];

    $zipFile = '/tmp/dist.zip';

    exec("git archive --format=zip --output=$zipFile HEAD");

    $zip = new ZipArchive();

    if ($zip->open($zipFile) !== true) {
        throw new Exception("Failed to open ZIP archive: $zipFile");
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);

        $isToplevelFile = !str_contains($entry, '/');
        $isToplevelDir = str_ends_with($entry, '/');

        if ($isToplevelDir && !$includeDirs) {
            continue;
        }
        if (!$isToplevelFile) {
            continue;
        }

        $entries[] = $entry;
    }

    $zip->close();

    exec("rm -rf $zipFile");

    return $entries;
}
