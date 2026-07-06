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
