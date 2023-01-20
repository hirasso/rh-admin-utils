<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AdminUtils extends Singleton
{

    private $deprecated_plugins = [
        'rh-wpsc-clear-cache/rh-wpsc-clear-cache.php',
        'rh-editors-add-users/rh-editors-add-users.php',
        'toolbar-publish-button/toolbar-publish-button.php',
        'rh-environments/rh-environments.php',
        'disable-comments/disable-comments.php',
        'search-and-replace/inpsyde-search-replace'
    ];

    public function __construct()
    {

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'admin_init'], 11);
        add_action('admin_notices', [$this, 'show_admin_notices']);
    }

    /**
     * Admin init
     *
     * @return void
     */
    public function admin_init()
    {
        $this->delete_conflicting_plugins();
    }

    /**
     * Enqueues Admin Assets
     *
     * @return void
     */
    public function enqueue_admin_assets()
    {
        wp_enqueue_style("rhau-admin", $this->asset_uri("assets/rhau-admin.css"), [], null);
        wp_enqueue_script("rhau-admin", $this->asset_uri("assets/rhau-admin.js"), ['jquery'], null, true);
    }

    /**
     * Helper function to get versioned asset urls
     *
     * @param [type] $path
     * @return void
     */
    public function asset_uri($path)
    {

        $uri = plugins_url($path, 'rh-admin-utils/rh-admin-utils');
        $file = RHAU_PLUGIN_DIR . ltrim($path, '/');

        if (file_exists($file)) {
            $version = filemtime($file);
            $uri .= "?v=$version";
        }
        return $uri;
    }

    /**
     * Helper function to transform an array to an object
     *
     * @param array $array
     * @return stdClass
     */
    private function to_object($array)
    {
        return json_decode(json_encode($array));
    }

    /**
     * Helper function to detect a development environment
     */
    private function is_dev()
    {
        return defined('WP_ENV') && WP_ENV === 'development';
    }

    /**
     * Get a template
     *
     * @param string $template_name
     * @param mixed $value
     * @return string
     */
    public function get_template($template_name, $value = null)
    {
        $value = $this->to_object($value);
        $path = $this->get_plugin_path("templates/$template_name.php");
        $path = apply_filters("rhau/template/$template_name", $path);
        if (!file_exists($path)) return "<p>$template_name: Template doesn't exist</p>";
        ob_start();
        if ($this->is_dev()) echo "<!-- Template Path: $path -->";
        include($path);
        return ob_get_clean();
    }

    /**
     * Check if on acf options page
     *
     * @return boolean
     */
    public function is_admin_acf_options_page()
    {
        if (!function_exists('acf_get_options_page')) return false;
        if (!$slug = $_GET['page'] ?? null) return false;
        if (!$options_page = acf_get_options_page($slug)) return false;
        $prepare_slug = preg_replace("/[\?|\&]page=$slug/", "", basename($_SERVER['REQUEST_URI']));
        if (!empty($options_page['parent_slug']) && $options_page['parent_slug'] !== $prepare_slug) return false;
        return true;
    }

    /**
     * Delete deprecated plugins
     *
     * @return void
     */
    public function delete_conflicting_plugins()
    {
        $found_one = false;
        $is_redirect = $_GET['rhau-deleted-depreated'] ?? null;
        foreach ($this->deprecated_plugins as $id => $plugin_slug) {
            $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_slug;
            if (file_exists($plugin_file)) {
                $found_one = true;
                $plugin_data = get_plugin_data($plugin_file);
                deactivate_plugins([$plugin_slug], true);
                delete_plugins([$plugin_slug]);
                $this->add_admin_notice("plugin-deleted-$id", "[RH Admin Utils] Deleted conflicting plugin „{$plugin_data['Name']}“.", "success");
            }
        }
        if ($found_one && !$is_redirect) {
            wp_safe_redirect(add_query_arg('rhau-deleted-depreated', true));
            exit;
        }
    }

    /**
     * Adds an admin notice
     *
     * @param string $key
     * @param string $message
     * @param string $type
     * @return void
     */
    public function add_admin_notice($key, $message, $type = 'warning', $is_dismissible = false)
    {
        $notices = get_transient("rhau-admin-notices");
        if (!$notices) $notices = [];
        $notices[$key] = [
            'message' => $message,
            'type' => $type,
            'is_dismissible' => $is_dismissible
        ];
        set_transient("rhau-admin-notices", $notices);
    }

    /**
     * Shows admin notices from transient
     *
     * @return void
     */
    public function show_admin_notices()
    {
        $notices = get_transient("rhau-admin-notices");
        delete_transient("rhau-admin-notices");
        if (!is_array($notices)) return;
        foreach ($notices as $notice) {
            ob_start() ?>
            <div class="notice notice-<?= $notice['type'] ?> <?= $notice['is_dismissible'] ? 'is-dismissible' : '' ?>">
                <p><?= $notice['message'] ?></p>
            </div>
<?php echo ob_get_clean();
        }
    }

    /**
     * Conditional to check if inside WP_CLI
     *
     * @return boolean
     */
    public function is_wp_cli(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }
}