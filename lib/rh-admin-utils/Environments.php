<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Environments extends Singleton
{
    private string $env;
    private array $environments = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Allow to define DISALLOW_INDEXING in the config
        if (defined('DISALLOW_INDEXING') && DISALLOW_INDEXING === true) {
            add_action('pre_option_blog_public', '__return_zero');
        };

        $this->environments = $this->get_environments();

        if (count($this->environments) < 2) return;

        add_action('after_setup_theme', [$this, 'setup']);
    }

    public function setup()
    {
        $this->env = $this->get_environment_type();

        add_action('admin_init', [$this, 'update_network_sites']);
        add_filter('network_admin_url', [$this, 'network_admin_url'], 10, 2);
        add_filter('auth_cookie_expiration', [$this, 'auth_cookie_expiration'], PHP_INT_MAX - 1, 3);

        $this->add_non_production_hooks();
        $this->init_environment_links();

        add_action('admin_notices', array($this, 'disallow_indexing_notice'));
    }

    /**
     * Get available environments
     */
    private function get_environments(): array
    {
        if (defined('ENVIRONMENTS') && is_array(ENVIRONMENTS)) {
            return ENVIRONMENTS;
        }

        $environments = [
            'development' => defined('WP_HOME_DEV') ? WP_HOME_DEV : null,
            'staging' => defined('WP_HOME_STAG') ? WP_HOME_STAG : null,
            'production' => defined('WP_HOME_PROD') ? WP_HOME_PROD : null,
        ];

        return array_filter(
            $environments,
            fn ($host) => !empty($host)
        );
    }

    /**
     * Filter network admin url
     */
    public function network_admin_url(string $url, string $path): string
    {
        if (defined('WP_SITEURL')) {
            $path = substr($url, strpos($url, '/wp-admin/'));
            $url = WP_SITEURL . $path;
        }
        return $url;
    }

    /**
     * Updates network options
     */
    public function update_network_sites(): void
    {
        if (!is_multisite()) return;
        if (!defined('RH_NETWORK_SITES') || !is_array(RH_NETWORK_SITES)) return;

        $id = 0;
        foreach (RH_NETWORK_SITES as $site) {
            $id++;

            update_blog_option($id, 'siteurl', $site[$this->env]['siteurl']);
            update_blog_option($id, 'home', $site[$this->env]['home']);
            $domain = wp_parse_url($site[$this->env]['home']);

            wp_update_site($id, [
                'domain' => $domain['host'],
            ]);
        }
    }

    /**
     * Add all the hooks
     */
    private function add_non_production_hooks(): void
    {
        if ($this->env === 'production') return;

        add_filter('wp_calculate_image_srcset', array(&$this, 'calculate_image_srcset'), 11);
        add_filter('wp_get_attachment_url', array(&$this, 'get_attachment_url'), 11);
        add_filter('document_title_parts', array($this, 'document_title_parts'));
        add_filter('admin_title', array($this, 'admin_title'));

        if ($this->env === 'staging') {
            add_filter('wp_robots', 'wp_robots_no_robots');
        }
        if ($this->env === 'development') {
            add_filter('https_ssl_verify', '__return_false');
        }
    }

    /**
     * Renders the environment links
     */
    private function init_environment_links(): void
    {
        if (!current_user_can('administrator')) return;

        add_action('wp_enqueue_scripts', array($this, 'assets'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('wp_footer', array($this, 'render_environment_links'));
        add_action('admin_footer', array($this, 'render_environment_links'));
    }

    /**
     * Get the value of WP_ENV
     */
    private function get_environment_type(): string
    {
        if (defined('WP_ENV')) return WP_ENV;
        return wp_get_environment_type();
    }

    /**
     * Render quick-links to other environments
     */
    public function render_environment_links(): void
    {
?>
        <rhau-environment-links>
            <i tabindex="0"></i>

            <?php foreach ($this->environments as $environment => $url) : ?>
                <?php if ($environment === $this->env) continue; ?>
                <rhau-environment-link tabindex="0" data-remote-root="<?= $url ?>">
                    <?= ucfirst($environment) ?>
                </rhau-environment-link>
            <?php endforeach; ?>

            <i tabindex="0"></i>
        </rhau-environment-links>

<?php
    }

    /**
     * Enqueue assets
     */
    public function assets(): void
    {
        wp_enqueue_style('rhau-environment-links', rhau()->asset_uri("assets/rhau-environment-links.css"), [], null);
        wp_enqueue_script('rhau-environment-links', rhau()->asset_uri("assets/rhau-environment-links.js"), array("jquery"), null, true);
    }

    /**
     * Filter document title parts
     */
    public function document_title_parts(array $parts): array
    {
        if (!empty($parts['title'])) {
            $parts['title'] = $this->prepend_to_string($parts['title'], "[$this->env] ");
            return $parts;
        }

        if (!empty($parts['site'])) {
            $parts['site'] = $this->prepend_to_string($parts['site'], "[$this->env] ");
        }

        return $parts;
    }

    /**
     * Filter admin title
     *
     * @param string $title
     * @return string
     */
    public function admin_title(?string $title): ?string
    {
        return $this->prepend_to_string($title, "[$this->env] ");
    }



    /**
     * Prepend a string to another string
     */
    private function prepend_to_string(?string $text, string $prepend): string
    {
        $text ??= '';
        if (strpos($text, $prepend) === 0) return $text;

        return $prepend . $text;
    }

    /**
     * Filter attachments
     */
    public function get_attachment_url(string $url): string
    {
        return $this->maybe_get_remote_url($url);
    }

    /**
     * Filter srcsets
     */
    public function calculate_image_srcset($sources)
    {

        foreach ($sources as &$source) {
            $url = !empty($source['url']) ? $source['url'] : false;
            if (!$url) {
                continue;
            }
            $url = $this->maybe_get_remote_url($url);
            $source['url'] = $url;
        }
        return $sources;
    }

    /**
     * Try to load remote file url if missing locally
     */
    private function maybe_get_remote_url(string $url): string
    {
        $remote_origin = $this->environments['production'];
        if (defined('REMOTE_ASSETS_ORIGIN')) {
            $remote_origin = REMOTE_ASSETS_ORIGIN;
        }

        // bail early if the $url is external
        if (!str_starts_with($url, get_option('home'))) return $url;

        $upload_dir = wp_upload_dir();
        $file = $upload_dir["basedir"] . str_replace($upload_dir["baseurl"], "", $url);

        if (file_exists($file)) {
            return  $url;
        }

        $local_origin = $this->environments[$this->env];

        if ($local_origin === $remote_origin) return $url;

        return str_replace($local_origin, $remote_origin, $url);
    }

    /**
     * Show a notice if the site is set not to be indexed by...
     *
     * - Either activating "Discourage search engines from indexing this site" under wp-admin/options-reading.php
     * - Or defining DISALLOW_INDEXING in the config (should be done in staging)
     */
    public function disallow_indexing_notice()
    {
        if ((bool) (int) get_option('blog_public')) return;

        $admin_email = apply_filters('rh/environments/admin_email', 'mail@rassohilber.com');
        $headline = "<strong>This site is still in private mode, so search engines are being blocked.</strong>";
        $body = "If you want to go live, please <a href='mailto:$admin_email'>notify your site's administrator</a>.";
        echo "<div class='notice notice-warning'><p><span class='dashicons dashicons-hidden'></span> {$headline} {$body}</p></div>";
    }

    /**
     * Set the auth cookie expiration to one year if in development
     *
     * @param int $ttl            time to live. default DAY_IN_SECOND*2
     */
    public function auth_cookie_expiration(int $ttl, int $user_id, bool $remember): int
    {
        // Adjust to your working environment needs.
        $dev_environment_types = ['development', 'local'];

        if (in_array($this->env, $dev_environment_types, true)) $ttl = YEAR_IN_SECONDS;

        return $ttl;
    }
}
