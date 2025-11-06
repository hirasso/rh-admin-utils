<?php

namespace RH\AdminUtils;

class Misc extends Singleton
{
    public function __construct()
    {
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('acf/get_field_label', [$this, 'modify_image_field_label'], 10, 2);
        add_action('admin_init', [$this, 'redirect_edit_php']);
        add_action('current_screen', [$this, 'redirect_initial_admin_url']);
        add_action('plugins_loaded', [$this, 'limit_revisions']);
        add_action('after_setup_theme', [$this, 'after_setup_theme']);
        add_action('admin_bar_menu', [$this, 'admin_bar_menu'], 999);
        add_filter('gu_set_options', [$this, 'gu_set_options']);
        add_action('admin_menu', [$this, 'remove_admin_menu_tools'], 11);
        add_filter('debug_bar_enable', [$this, 'debug_bar_enable']);
        add_action('map_meta_cap', [$this, 'map_meta_cap_privacy_options'], 1, 4);
        add_action('admin_init', [$this, 'remove_privacy_policy_notice']);
        add_action('init', [$this, 'edit_screen_columns'], 999);
        add_filter('admin_body_class', [$this, 'admin_body_class']);
        // Disable Siteground Security logs
        add_filter('pre_option_sg_security_disable_activity_log', '__return_true');
        add_filter('pre_option_sg_security_login_attempts', static fn () => 10);

        // qtranslate
        add_action('admin_init', [$this, 'overwrite_qtranslate_defaults']);
        add_action('admin_enqueue_scripts', [$this, 'remove_qtranslate_admin_styles'], 11);

        add_filter('wp_admin_notice_markup', [$this, 'maybe_hide_update_nag'], 10, 3);
    }

    public function after_setup_theme()
    {
        add_filter('map_meta_cap', [$this, 'disable_capabilities'], 10, 4);
        add_action('init', [$this, 'schedule_sg_security_cronjob']);
        self::native_emoji();
    }

    public function remove_privacy_policy_notice()
    {
        remove_action('admin_notices', ['WP_Privacy_Policy_Content', 'notice']);
    }

    /**
     * Enable native emojis / disable twemojis
     */
    private function native_emoji()
    {
        if (!(bool) apply_filters('rhau/native_emoji', true)) {
            return;
        }

        remove_filter('wp_head', 'print_emoji_detection_script', 7);
        remove_filter('wp_print_styles', 'print_emoji_styles');
        remove_filter('admin_print_scripts', 'print_emoji_detection_script');
        remove_filter('admin_print_styles', 'print_emoji_styles');
        add_filter('tiny_mce_plugins', fn ($plugins) => array_diff($plugins, ['wpemoji']));
    }

    /**
     * Limit revisions
     *
     * @return void
     */
    public function limit_revisions()
    {
        if (defined('WP_POST_REVISIONS')) {
            return;
        }
        $revisions = intval(apply_filters('rhau/settings/post_revisions', 3));
        define('WP_POST_REVISIONS', $revisions);
    }

    /**
     * Add general instructions to image fields
     *
     * @param string $label
     * @return array $field
     */
    public function modify_image_field_label(string $label, array $field)
    {
        if (! is_admin() || $field['type'] !== 'image') {
            return $label;
        }

        return "$label <span title='JPG for photos or drawings, PNG for transparency or simple graphics (larger file size).' class='dashicons dashicons-info acf-js-tooltip rhau-icon--info'></span>";
    }

    /**
     * Overwrites some qtranslate defaults
     *
     * @return void
     */
    public function overwrite_qtranslate_defaults()
    {
        global $q_config;
        if (! isset($q_config)) {
            return;
        }
        // disable qtranslate styles on the admin LSBs
        $q_config['lsb_style'] = 'custom';
        // do not highlight translatable fields. Set to QTX_HIGHLIGHT_MODE_CUSTOM_CSS
        $q_config['highlight_mode'] = 9;
        // insert an empty space as custom CSS, so that the qtranslate options page doesn't break
        $q_config['highlight_mode_custom_css'] = '  ';
        // hide the 'copy from' button
        $q_config['hide_lsb_copy_content'] = true;
    }

    /**
     * Don't enqueue custom qtranslate lsb styles. Otherwise the plugin
     * will try to enqueue a missing /css/lsb/custom css style :(
     */
    public function remove_qtranslate_admin_styles()
    {
        wp_dequeue_style('qtranslate-admin-lsb');
    }

    /**
     * Redirects the default edit.php screen
     *
     * @return void
     */
    public function redirect_edit_php()
    {
        global $pagenow, $typenow;
        if ($pagenow !== 'edit.php') {
            return;
        }
        if ($typenow) {
            return;
        }

        // Allow themes to deactivate the redirect
        if (! apply_filters('rhau/redirect_edit_php', true)) {
            return;
        }

        $redirect_url = admin_url('/edit.php?post_type=page');

        // Allow themes to filter the redirect url
        $redirect_url = apply_filters('rhau/edit_php_redirect_url', $redirect_url);

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Redirects from the dashboard to another admin page
     * You can deactivate this using this filter:
     * add_filter('rhau/initial_admin_url', fn() => 'index.php');
     * ...or customize it:
     * add_filter('rhau/initial_admin_url', fn() => 'my-admin-page.php');
     */
    public function redirect_initial_admin_url()
    {
        if (rhau()->getCurrentScreen()?->id !== 'dashboard') {
            return;
        }

        $initial_admin_url = trim(
            apply_filters('rhau/initial_admin_url', 'edit.php?post_type=page'),
            '/'
        );

        if ($initial_admin_url === 'index.php') {
            return;
        }

        wp_safe_redirect(admin_url("/$initial_admin_url"));
        exit;
    }

    /**
     * Disable some caps for all users
     *
     * @param  array  $caps
     * @param  string  $cap
     * @param  int  $user_id
     * @param  array  $args
     * @return array
     */
    public function disable_capabilities($caps, $cap, $user_id, $args)
    {
        $disabled_capabilities = apply_filters('rhau/disabled_capabilities', ['customize']);
        if (! in_array($cap, $disabled_capabilities)) {
            return $caps;
        }
        $caps[] = 'do_not_allow';

        return $caps;
    }

    /**
     * Remove some nodes from WP_Admin_Bar
     */
    public function admin_bar_menu(\WP_Admin_Bar $ab): void
    {
        $ab->remove_node('wp-logo');
        $ab->remove_node('new-content');
        $ab->remove_node('wpseo-menu');
    }

    /**
     * Automatically set Github Updater options
     */
    public function gu_set_options(?array $options = null): ?array
    {
        $options ??= [];
        if ($token = UpdateChecker::getGitHubToken()) {
            $options['github_access_token'] = $token;
        }

        return $options;
    }

    /**
     * Remove the tools admin menu if it doesn't have any sub pages
     *
     * @return void
     */
    public function remove_admin_menu_tools()
    {
        if (current_user_can('manage_options')) {
            return;
        }
        global $submenu;

        if (count($submenu['tools.php'] ?? []) < 2) {
            remove_menu_page('tools-php');
        }

        remove_submenu_page('tools.php', 'tools.php');
    }

    /**
     * Disables the debug bar for certain users
     */
    public function debug_bar_enable(bool $enable): bool
    {
        if (! current_user_can('administrator')) {
            return false;
        }

        return $enable;
    }

    /**
     * Changes cap to to manage the privacy page from manage_options to edit_others_posts
     */
    public function map_meta_cap_privacy_options(
        array $caps,
        string $cap,
        int $user_id,
        $args
    ): array {
        if (! is_user_logged_in()) {
            return $caps;
        }

        if ($cap !== 'manage_privacy_options') {
            return $caps;
        }

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
     */
    public function default_edit_columns(array $columns): array
    {
        unset($columns['language']);

        return $columns;
    }

    /**
     * Render custom edit column
     */
    public function render_edit_column(string $column, int $post_id): void
    {
    }

    /**
     * Add custom classes to the admin body
     */
    public function admin_body_class(string $class): string
    {
        global $pagenow;
        if ($pagenow !== 'user-edit.php') {
            return $class;
        }

        // allows for themes to disable hiding the application passwords
        if (apply_filters('rhau/misc/hide-application-passwords', true)) {
            $class = "hide-application-passwords $class";
        }

        return $class;
    }

    /**
     * Make sure sg-security's cronjob is being scheduled
     */
    public function schedule_sg_security_cronjob(): void
    {
        if (! rhau()->is_plugin_active('sg-security/sg-security.php')) {
            return;
        }
        if (wp_next_scheduled('siteground_security_clear_logs_cron')) {
            return;
        }

        wp_schedule_event(time(), 'daily', 'siteground_security_clear_logs_cron');
    }

    /**
     * Hide the update nag where applicable
     */
    public function maybe_hide_update_nag(
        string $markup,
        string $message,
        array $args
    ): string {

        $is_update_nag = array_search('update-nag', $args['additional_classes'] ?? [], true) !== false;
        if (! $is_update_nag) {
            return $markup;
        }

        /** don't show the update nag if DISALLOW_FILE_EDIT is true */
        if (! wp_is_file_mod_allowed('capability_update_core')) {
            return '';
        }

        /** don't show the update nag to editors */
        if (! current_user_can('manage_options')) {
            return '';
        }

        return $markup;
    }
}
