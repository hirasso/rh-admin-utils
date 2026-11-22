<?php

namespace RH\AdminUtils\Commands;

use function RH\AdminUtils\rhau;

/**
 * Various handy WP CLI commands
 */
class Commands
{
    /**
     * Static init function instead of a constructor
     */
    public static function init()
    {
        if (!rhau()->is_wp_cli()) {
            return;
        }
        \WP_CLI::add_command('rhau do-action-save-post', DoActionSavePostCommand::class);
        \WP_CLI::add_command('rhau test-imagick', TestImagickCommand::class);
        TestImagickCommand::hook_into_media_regenerate();
    }
}
