<?php

namespace RH\AdminUtils\Tests\SetupPlugin;

/** Exit if accessed directly */
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Setup context for development
 */
final class DevSetup
{
    public function __construct()
    {
        $this->register_cpt();
    }

    private function register_cpt(): void
    {
        register_post_type('dev_entry', [
            'public' => true,
            'supports' => ['title', 'editor', 'revisions', 'page-attributes'],
            'labels' => [
                'name' => 'Entries',
                'singular_name' => 'Entry',
                'menu_name' => 'Dev Entries',
            ],
        ]);
    }

}
