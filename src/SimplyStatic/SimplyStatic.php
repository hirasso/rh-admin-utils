<?php

namespace RH\AdminUtils\SimplyStatic;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Helpers for SimplyStatic
 */
class SimplyStatic
{
    public static function init()
    {
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('rhau simply-static', __NAMESPACE__ . '\\CLI');
        }
    }
}
