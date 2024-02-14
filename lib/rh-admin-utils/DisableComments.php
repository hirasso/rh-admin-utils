<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DisableComments extends Singleton
{

    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'after_setup_theme']);
    }

    /**
     * Inits the class
     *
     * @return void
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function after_setup_theme(): void
    {
        // allow re-activating comments
        if (!apply_filters('rhau/disable_comments', true)) return;

        $this->overwrite_discussion_options();
        // hide comments UI
        add_action('admin_menu', [$this, 'admin_menu'], 999);
        add_action('admin_bar_menu', [$this, 'admin_bar_menu'], 999);
        // other hooks
        add_action('admin_init', [$this, 'admin_init']);
        add_action('registered_post_type', [$this, 'registered_post_type'], 999);

        // Filters taken from the plugin "Disable Comments"
        // @link https://github.com/WPDevelopers/disable-comments/blob/master/disable-comments.php
        add_filter('wp_headers', [$this, 'filter_wp_headers']);
        add_filter('xmlrpc_methods', [$this, 'disable_xmlrc_comments']);
        add_filter('rest_endpoints', [$this, 'filter_rest_endpoints']);
        add_filter('rest_pre_insert_comment', [$this, 'disable_rest_API_comments']);
        add_action('template_redirect', [$this, 'filter_query'], 9);   // before redirect_canonical.
    }

    /**
     * Remove the X-Pingback HTTP header
     *
     * @param array $headers
     * @return array
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function filter_wp_headers(array $headers): array
    {
        unset($headers['X-Pingback']);
        return $headers;
    }

    /**
     * Remove method wp.newComment
     *
     * @param array $methods
     * @return array
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function disable_xmlrc_comments(array $methods): array
    {
        unset($methods['wp.newComment']);
        return $methods;
    }

    /**
     * Remove the comments endpoint for the REST API
     *
     * @param array $endpoints
     * @return array
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function filter_rest_endpoints(array $endpoints): array
    {
        unset($endpoints['comments']);
        return $endpoints;
    }

    /**
     * Disable Rest API Comments
     *
     * @param [type] $prepared_comment
     * @param [type] $request
     * @return void
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function disable_rest_API_comments($prepared_comment, $request): void
    {
        return;
    }

    /**
     * Issue a 403 for all comment feed requests.
     */
    public function filter_query()
    {
        if (is_comment_feed()) {
            wp_die(__('Comments are closed.', 'disable-comments'), '', array('response' => 403));
        }
    }

    /**
     * Admin INIT
     *
     * @return void
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function admin_init(): void
    {
        global $pagenow;
        if ($pagenow === 'options-discussion.php') {
            rhau()->add_admin_notice('comments-disabled', __('[RH Admin Utils] Comments are disabled. Some settings on this page are being ignored and/or overwritten.'));
        }
    }

    /**
     * Automatically sets the settings for disabling comments
     *
     * @return void
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    private function overwrite_discussion_options(): void
    {
        // don't allow comments on new posts
        add_filter('option_default_comment_status', '__return_empty_string');
        // don't allow pingbacks and trackbacks
        add_filter('pre_option_default_ping_status', '__return_empty_string');
        // automatically close comment status on old posts
        add_filter('pre_option_close_comments_days_old', '__return_zero');
        add_filter('pre_option_close_comments_for_old_posts', '__return_true');
        // additional measure: Only allow logged-in users to comment
        add_filter('pre_option_comment_registration', '__return_true');
        // additional measure: All comments must be approved
        add_filter('pre_option_comment_moderation', '__return_true');
        // don't allow pings or comments
        add_filter('pings_open', '__return_false');
        add_filter('comments_open', '__return_false');
    }

    /**
     * Removes the comments node from the admin bar
     *
     * @param \WP_Admin_Bar $admin_bar
     * @return void
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function admin_bar_menu(\WP_Admin_Bar $admin_bar): void
    {
        $admin_bar->remove_node('comments');
    }

    /**
     * Removes the comments admin menu item
     *
     * @return void
     * @author Rasso Hilber <mail@rassohilber.com>
     */
    public function admin_menu(): void
    {
        remove_menu_page('edit-comments.php');
    }

    /**
     * Removes 'comments' and 'trackbacks' support for all post types
     */
    public function registered_post_type(string $post_type): void
    {
        remove_post_type_support($post_type, 'comments');
        remove_post_type_support($post_type, 'trackbacks');
    }
}
