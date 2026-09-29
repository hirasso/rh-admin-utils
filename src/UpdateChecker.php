<?php

namespace RH\AdminUtils;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use YahnisElsts\PluginUpdateChecker\v5p7\PucFactory as VersionedPucFactory;

/**
 * Check for Updates using Plugin Update Checker
 * @see https://github.com/YahnisElsts/plugin-update-checker
 */
class UpdateChecker
{
    public static function init(string $entryPoint)
    {
        self::loadLibrary();

        /** Prevent duplication of updates in rh-updater */
        add_filter("rh-updater/allow/slug=rh-admin-utils", "__return_false");

        /** get owner and name from the composer.json */
        $composerJSON = json_decode(file_get_contents(baseDir() . "/composer.json"));
        [$owner, $name] = explode("/", $composerJSON->name);

        /** build the update checker */
        $checker = PucFactory::buildUpdateChecker(
            "https://github.com/$owner/$name/",
            $entryPoint,
            $name,
        );

        $checker->setBranch('main');

        if ($token = static::getGitHubToken()) {
            $checker->setAuthentication($token);
        }

        /**
         * Expect a "$name.zip" attached to every release
         * @var \YahnisElsts\PluginUpdateChecker\v5p7\Vcs\GitHubApi $api
         */
        $api = $checker->getVcsApi();
        $api->enableReleaseAssets("/$name\.zip/i", $api::REQUIRE_RELEASE_ASSETS);

        $checker->addFilter('vcs_update_detection_strategies', [static::class, 'update_strategies'], 999);
    }

    /**
     * Load the bundled copy of plugin-update-checker.
     * Vendored into lib/ so composer installs don't pull it into the consuming project.
     * A second copy is safe to load; skipping it just avoids a redundant autoloader.
     */
    private static function loadLibrary(): void
    {
        if (class_exists(VersionedPucFactory::class, false)) {
            return;
        }

        require_once baseDir() . '/lib/plugin-update-checker/plugin-update-checker.php';
    }

    /**
     * Get the RHAU_GITHUB_TOKEN for authenticated GitHub requests
     */
    public static function getGitHubToken(): ?string
    {
        if (
            defined('RHAU_GITHUB_TOKEN')
            && is_string(RHAU_GITHUB_TOKEN)
            && !empty(trim(RHAU_GITHUB_TOKEN))
        ) {
            return RHAU_GITHUB_TOKEN;
        }
        return null;
    }

    /**
     * Only keep the "latest_release" strategy
     */
    public static function update_strategies(array $strategies): array
    {
        return ['latest_release' => $strategies['latest_release']];
    }
}
