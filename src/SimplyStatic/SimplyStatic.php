<?php

namespace RH\AdminUtils\SimplyStatic;

use function RH\AdminUtils\rhau;

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
        if (rhau()->is_wp_cli()) {
            \WP_CLI::add_command('rhau simply-static', __NAMESPACE__ . '\\CLI');
        }
    }
}
