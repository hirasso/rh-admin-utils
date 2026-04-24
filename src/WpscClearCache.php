<?php

namespace RH\AdminUtils;

use WP_Admin_Bar;

class WpscClearCache extends Singleton
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'wp_super_cache_init']);
        add_action('acf/save_post', [$this, 'acf_save_post']);
        if (rhau()->is_wp_cli()) {
            \WP_CLI::add_command('rhau wpsc-clear-cache', [$this, 'wp_cli_clear_cache']);
        }
    }

    /**
     * WP Super Cache Init
     *
     * @return void
     */
    public function wp_super_cache_init()
    {
        if (is_plugin_active('wp-super-cache/wp-cache.php')) {
            add_action('admin_bar_menu', [$this, 'replace_wp_super_cache_admin_bar_button'], 999);
        }
        if (intval($_GET['rh_clear_cache'] ?? null) === 1) {
            $this->clear_cache_and_redirect();
        }
    }

    /**
     * Deletes the cache and redirects to referrer
     *
     * @return void
     */
    private function clear_cache_and_redirect()
    {
        global $cache_path;
        check_admin_referer('rh_clear_cache');

        $this->clear_cache();

        do_action('rh/wpsc-cc/clear_cache');

        $redirect_url = remove_query_arg('_wpnonce');
        $redirect_url = remove_query_arg('rh_clear_cache', $redirect_url);

        $notice = apply_filters('rh/wpsc-cc/cache_deleted_notice', __('Cache deleted.'));
        rhau()->add_admin_notice('cache-cleared', $notice, 'success');

        rhau()->redirect($redirect_url);
    }

    /**
     * Adds the Item to the admin bar menu. Replaces WPSC's item
     */
    public function replace_wp_super_cache_admin_bar_button(WP_Admin_Bar $wp_admin_bar): void
    {
        global $super_cache_enabled, $cache_enabled;

        $wp_admin_bar->remove_menu('delete-cache');
        if (!current_user_can('edit_others_posts') || !($super_cache_enabled && $cache_enabled)) {
            return;
        }

        $url = $_SERVER['REQUEST_URI'];
        $url = add_query_arg('rh_clear_cache', '1', $url);
        $text = __('Delete Cache', 'wp-super-cache');
        $text = apply_filters('rh/wpsc-cc/menu_item_text', $text);

        $args = [
            'parent' => '',
            'id' => 'rhau-delete-cache',
            'title' => '<span class="ab-icon"></span>' . $text,
            'meta' => ['title' => __('Delete Super Cache cached files', 'wp-super-cache'), 'target' => '_self'],
            'href' => wp_nonce_url($url, 'rh_clear_cache'),
        ];

        $wp_admin_bar->add_menu($args);
    }

    /**
     * Clear WP Super Cache cache directory on acf save post of options pages
     *
     * @see https://support.advancedcustomfields.com/forums/topic/clear-wp-super-cache-on-update/
     */
    public function acf_save_post(mixed $post_id): void
    {
        if (is_string($post_id)) {
            $this->clear_cache();
        }
    }

    /**
     * Clear the cache from WP CLI
     */
    public function wp_cli_clear_cache(): void
    {
        $parsed_home_url = parse_url(home_url());
        $host = $parsed_home_url['host'];

        \WP_CLI::log("🔥 Clearing cache on $host...");

        if ($this->clear_cache()) {
            \WP_CLI::log('✅ Successfully cleared the cache!');
            return;
        }

        \WP_CLI::log("❌ Couldn't clear the cache, WP Super Cache is not active.");
    }

    /**
     * Calls the WP Super Cache API function to clear the full cache directory
     */
    private function clear_cache(): bool
    {
        // bail early if wp super cache isn't installed
        if (!function_exists('wp_cache_clear_cache')) {
            return false;
        }
        wp_cache_clear_cache();
        return true;
    }
}
