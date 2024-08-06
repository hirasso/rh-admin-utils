<?php

namespace RH\AdminUtils;

use WP_Admin_Bar;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AdminBarPublishButton extends Singleton
{
    public function __construct()
    {
        /** just before `wp_admin_bar_my_account_item` */
        add_action('admin_bar_menu', [$this, 'add_buttons'], 9990, 1);
    }

    /**
     * Adds buttons to WP Admin Bar
     *
     * @return void
     */
    public function add_buttons()
    {
        /** @var WP_Admin_Bar $wp_admin_bar */
        global $wp_admin_bar;

        if ($this->current_screen_has_publish_button()) {
            $wp_admin_bar->add_node([
                'id' => 'rh-publish',
                'parent' => 'top-secondary',
                'href' => '##',
            ]);
        }

        $wp_admin_bar->add_node([
            'id' => 'rh-save',
            'parent' => 'top-secondary',
            'href' => '##',
        ]);

    }

    /**
     * Check if we are on an admin bar edit screen
     *
     * @return boolean
     */
    private function current_screen_has_publish_button()
    {
        global $pagenow;
        if (in_array($pagenow, ['post.php', 'post-new.php'])) return true;
        if (rhau()->is_admin_acf_options_page()) return true;
        return false;
    }
}
