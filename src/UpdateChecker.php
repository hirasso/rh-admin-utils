<?php

namespace RH\AdminUtils;

use RH\AdminUtils\Scoped\YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Check for Updates
 */
class UpdateChecker
{
    public static function init(string $entryPoint)
    {
        /**
         * Plugin Update Checker
         */
        $pluginSlug = basename(dirname($entryPoint));
        $checker = PucFactory::buildUpdateChecker(
            "https://github.com/hirasso/$pluginSlug-dist/",
            $entryPoint,
            $pluginSlug,
        );
        $checker->addFilter('vcs_update_detection_strategies', [static::class, 'update_strategies'], 999);
    }

    /**
     * Only keep the "latest_tag" strategy
     */
    public static function update_strategies(array $strategies): array
    {
        unset($strategies['latest_release']);
        unset($strategies['stable_tag']);
        unset($strategies['branch']);
        return $strategies;
    }
}
