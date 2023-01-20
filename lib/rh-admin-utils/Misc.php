<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Misc extends Singleton
{

    public function __construct()
    {
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('acf/prepare_field/type=image', [$this, 'prepare_image_field']);
        add_action('admin_init', [$this, 'activate_acf_pro_license']);
        add_action('admin_init', [$this, 'overwrite_qtranslate_defaults']);
        add_action('admin_init', [$this, 'redirect_edit_php']);
        add_action('plugins_loaded', [$this, 'limit_revisions']);
        add_action('after_setup_theme', [$this, 'after_setup_theme']);
        add_action('admin_bar_menu', [$this, 'admin_bar_menu'], 999);
        add_filter('gu_set_options', [$this, 'github_updater_options']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_filter('debug_bar_enable', [$this, 'debug_bar_enable']);
        add_action('map_meta_cap', [$this, 'map_meta_cap_privacy_options'], 1, 4);
        add_action('admin_init', [$this, 'remove_privacy_policy_notice']);
        add_action('init', [$this, 'edit_screen_columns'], 999);
        add_filter('admin_body_class', [$this, 'admin_body_class']);
    }

    public function after_setup_theme()
    {
        add_filter('map_meta_cap', [$this, 'disable_capabilities'], 10, 4);
    }

    public function remove_privacy_policy_notice()
    {
        remove_action('admin_notices', ['WP_Privacy_Policy_Content', 'notice']);
    }

    /**
     * Limit revisions
     *
     * @return void
     */
    public function limit_revisions()
    {
        if (defined('WP_POST_REVISIONS')) return;
        $revisions = intval(apply_filters('rhau/settings/post_revisions', 3));
        define('WP_POST_REVISIONS', $revisions);
    }

    /**
     * Add general instructions to image fields
     *
     * @param Array $field
     * @return Array $field
     */
    public function prepare_image_field($field)
    {
        if (!is_admin() || !$field || empty($field['label'])) return $field;
        $field['label'] .= " <span title='JPG for photos or drawings, PNG for transparency or simple graphics (larger file size).' class='dashicons dashicons-info acf-js-tooltip rhau-icon--info'></span>";
        return $field;
    }

    private function get_acf_version(): ?string
    {
        if (!function_exists('acf')) return null;
        return acf()->version;
    }

    /**
     * Automatically inject ACF pro license key
     *
     * @param [string] $pre
     * @return string
     */
    public function activate_acf_pro_license()
    {
        /**
         * ACF 5.11.0 introduced built-in support for the license in wp-config.php
         *
         * @see https://www.advancedcustomfields.com/blog/acf-5-11-release-rest-api/#license-key-improvements
         */
        if (version_compare($this->get_acf_version(), '5.11.0') >= 0) return;

        if (
            !function_exists('acf_pro_get_license_key')
            || !defined('ACF_PRO_LICENSE')
            || empty(ACF_PRO_LICENSE)
            || acf_pro_get_license_key()
        ) {
            return;
        }
        $_POST['acf_pro_licence'] = ACF_PRO_LICENSE;
        if ($acf_admin_updates = acf_get_instance('ACF_Admin_Updates')) {
            $acf_admin_updates->activate_pro_licence();
        }
    }

    /**
     * Overwrites some qtranslate defaults
     *
     * @return void
     */
    public function overwrite_qtranslate_defaults()
    {
        global $q_config;
        if (!isset($q_config)) return;
        // disable qtranslate styles on the admin LSBs
        $q_config['lsb_style'] = 'custom';
        // do not highlight translatable fields
        $q_config['highlight_mode'] = 0;
        // hide the 'copy from' button
        $q_config['hide_lsb_copy_content'] = true;
    }

    /**
     * Redirects the default edit.php screen
     *
     * @return void
     */
    public function redirect_edit_php()
    {
        global $pagenow, $typenow;
        if ($pagenow !== 'edit.php') return;
        if ($typenow) return;

        // Allow themes to deactivate the redirect
        if (!apply_filters('rhau/redirect_edit_php', true)) return;

        $redirect_url = admin_url('/edit.php?post_type=page');

        // Allow themes to filter the redirect url
        $redirect_url = apply_filters('rhau/edit_php_redirect_url', $redirect_url);

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Disable some caps for all users
     *
     * @param array $caps
     * @param string $cap
     * @param int $user_id
     * @param array $args
     * @return array
     */
    function disable_capabilities($caps, $cap, $user_id, $args)
    {
        $disabled_capabilities = apply_filters('rhau/disabled_capabilities', ['customize']);
        if (!in_array($cap, $disabled_capabilities)) return $caps;
        $caps[] = 'do_not_allow';
        return $caps;
    }

    /**
     * Remove some nodes from WP_Admin_Bar
     *
     * @param \WP_Admin_Bar $ab
     * @return void
     */
    public function admin_bar_menu(\WP_Admin_Bar $ab): void
    {
        $ab->remove_node('wp-logo');
        $ab->remove_node('new-content');
        $ab->remove_node('wpseo-menu');
    }

    /**
     * Automatically set Github Updater options
     *
     * @return array
     */
    public function github_updater_options(): array
    {
        return array(
            'github_access_token' => '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',
        );
    }

    /**
     * Remove some admin menu pages for some users
     *
     * @return void
     */
    public function admin_menu()
    {
        if (!current_user_can('administrator')) remove_menu_page('tools.php');
    }

    /**
     * Disables the debug bar for certain users
     *
     * @param boolean $enable
     * @return boolean
     */
    public function debug_bar_enable(bool $enable): bool
    {
        if (!current_user_can('administrator')) return false;
        return $enable;
    }

    /**
     * Changes cap to to manage the privacy page from manage_options to edit_others_posts
     *
     * @param array $caps
     * @param string $cap
     * @param integer $user_id
     * @param [type] $args
     * @return array
     */
    public function map_meta_cap_privacy_options(array $caps, string $cap, int $user_id, $args): array
    {
        if (!is_user_logged_in()) return $caps;

        if ($cap !== 'manage_privacy_options') return $caps;

        $caps = ['edit_others_posts'];

        return $caps;
    }

    /**
     * Create custom columns for each post type
     *
     * @return void
     */
    public function edit_screen_columns()
    {
        $post_types = get_post_types(['show_ui' => true]);
        foreach ($post_types as $pt) {
            add_filter("manage_edit-{$pt}_columns", [$this, 'default_edit_columns']);
            add_action("manage_{$pt}_posts_custom_column", [$this, 'render_edit_column'], 10, 2);
        }
    }

    /**
     * Adjust default edit columns
     *
     * @param array $columns
     * @return array
     */
    public function default_edit_columns(array $columns): array
    {
        unset($columns['language']);
        return $columns;
    }

    /**
     * Render custom edit column
     *
     * @param string $column
     * @param int $post_id
     * @return void
     */
    public function render_edit_column(string $column, int $post_id): void
    {
    }

    /**
     * Add custom classes to the admin body
     *
     * @param string $class
     * @return string
     */
    public function admin_body_class(string $class): string
    {
        global $pagenow;
        if ($pagenow !== 'user-edit.php') return $class;

        // allows for themes to disable hiding the application passwords
        if (apply_filters('rhau/misc/hide-application-passwords', true)) {
            $class = "hide-application-passwords $class";
        }

        return $class;
    }
}