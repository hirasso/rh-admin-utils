<?php

/**
 * Plugin Name: RH Admin Utils Setup Plugin
 * Description: Helps with development and e2e tests
 * Version: 10000.0.0
 */

namespace RH\AdminUtils\Tests\SetupPlugin;

/** Exit if accessed directly */
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * This fixture is its own wp-env plugin, so it has to load the dev autoloader itself:
 * it needs the classes next to it and extended-acf, neither of which the main plugin
 * autoloads. wp-env mounts the repo as a sibling plugin directory.
 */
require_once \dirname(__DIR__) . '/rh-admin-utils/vendor/autoload.php';

/**
 * Check what env we are currently in
 * @return null|"development"|"tests"
 */
function getCurrentEnv(): ?string
{
    $env = (\defined('RHAU_WP_ENV'))
        ? RHAU_WP_ENV
        : null;

    return \in_array($env, ['development', 'tests'], true)
        ? $env
        : null;
}


\add_action('after_setup_theme', function () {

    getCurrentEnv() === 'tests'
        ? new TestsSetup()
        : new DevSetup();
});
