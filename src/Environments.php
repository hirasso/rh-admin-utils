<?php

namespace RH\AdminUtils;

use InvalidArgumentException;

class Environments extends Singleton
{
    private string $env;

    /** @var array<string, Environment> $environments */
    private readonly array $environments;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Allow to define DISALLOW_INDEXING in the config
        if (defined('DISALLOW_INDEXING') && DISALLOW_INDEXING === true) {
            add_action('pre_option_blog_public', '__return_zero');
        };

        $this->environments = $this->parse_environments();

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

        add_action('admin_notices', [$this, 'disallow_indexing_notice']);
    }

    /**
     * Parse available environments from the config
     *
     * @return array<string, Environment>
     */
    private function parse_environments(): array
    {
        /** @var array<string, string> $config */
        $raw = defined('RHAU_ENVIRONMENTS') && is_array(RHAU_ENVIRONMENTS)
            ? RHAU_ENVIRONMENTS
            : [
                'production' => defined('WP_HOME_PROD') ? WP_HOME_PROD : null,
                'development' => defined('WP_HOME_DEV') ? WP_HOME_DEV : null,
                'staging' => defined('WP_HOME_STAG') ? WP_HOME_STAG : null,
            ];

        $raw = array_map(fn ($url) => $this->get_origin($url), $raw);
        $raw = array_filter($raw, $this->is_non_empty_string(...));

        $result = [];

        foreach ($raw as $name => $url) {
            $result[$name] = new Environment($name, $url);
        }

        return $result;
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
        if (!is_multisite()) {
            return;
        }
        if (!defined('RH_NETWORK_SITES') || !is_array(RH_NETWORK_SITES)) {
            return;
        }

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
        if ($this->env === 'production') {
            return;
        }

        add_filter('wp_calculate_image_srcset', [$this, 'calculate_image_srcset'], 11, 5);
        add_filter('wp_get_attachment_url', [$this, 'maybe_get_remote_url'], 11, 2);
        add_filter('document_title_parts', [$this, 'document_title_parts'], PHP_INT_MAX - 100);
        add_filter('admin_title', [$this, 'admin_title']);

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
        if (!rhau()->is_dev() && !current_user_can('administrator')) {
            return;
        }

        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('login_enqueue_scripts', [$this, 'assets']);
        add_action('wp_footer', [$this, 'render_environment_links']);
        add_action('admin_footer', [$this, 'render_environment_links']);
        add_action('login_footer', [$this, 'render_environment_links']);
    }

    /**
     * Get the value of WP_ENV
     */
    private function get_environment_type(): string
    {
        if (defined('WP_ENV')) {
            return WP_ENV;
        }
        return wp_get_environment_type();
    }

    /**
     * Render quick-links to other environments
     */
    public function render_environment_links(): void
    {
        ?>
        <dialog is="rhau-environment-links" data-rhau-environment-links>
            <?php foreach ($this->environments as $environment) : ?>
                <?php if ($environment->name === $this->env) {
                    continue;
                } ?>
                <rhau-environment-link tabindex="0" data-remote-origin="<?= $environment->origin ?>">
                    <?= ucfirst($environment->name) ?>
                </rhau-environment-link>
            <?php endforeach; ?>
        </dialog>
<?php
    }

    private function is_non_empty_string(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }

    /**
     * Get the origin from a URL
     */
    private function get_origin(mixed $url): ?string
    {
        if (!$this->is_non_empty_string($url)) {
            return rhau()->throw_in_dev(
                "Please provide a non-empty string",
                InvalidArgumentException::class
            );
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (!$scheme) {
            return rhau()->throw_in_dev(
                sprintf("No scheme in %s", $url),
                InvalidArgumentException::class
            );
        }

        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

        return $port
            ? "{$scheme}://{$host}:{$port}"
            : "{$scheme}://{$host}";
    }

    /**
     * Get the current origin
     */
    private function get_current_origin(): ?string
    {
        return isset($_SERVER['HTTP_HOST'])
            ? set_url_scheme('http://' . ($_SERVER['HTTP_HOST'] ?? ''))
            : null;
    }

    /**
     * Enqueue assets
     */
    public function assets(): void
    {
        wp_enqueue_style('rhau-environment-links', rhau()->asset_uri("assets/rhau-environment-links.css"), [], null);
        wp_enqueue_script('rhau-environment-links', rhau()->asset_uri("assets/rhau-environment-links.js"), [], null, true);
    }

    /**
     * Filter document title parts
     */
    public function document_title_parts(array $parts): array
    {
        $short_env = $this->short_env($this->env);
        if (!empty($parts['title'])) {
            $parts['title'] = $this->prepend_to_string($parts['title'], "$short_env ");
            return $parts;
        }

        if (!empty($parts['site'])) {
            $parts['site'] = $this->prepend_to_string($parts['site'], "$short_env ");
        }

        return $parts;
    }

    /**
     * Get a short representation of the current environment
     */
    private function short_env(string $long): string
    {
        if ($long === 'development') {
            return '🛠️';
        }
        if ($long === 'staging') {
            return '🎤';
        }
        return $long;
    }

    /**
     * Filter admin title
     *
     * @param string $title
     * @return string
     */
    public function admin_title(?string $title): ?string
    {
        $short_env = $this->short_env($this->env);
        return $this->prepend_to_string($title, "$short_env ");
    }



    /**
     * Prepend a string to another string
     */
    private function prepend_to_string(?string $text, string $prepend): string
    {
        $text ??= '';
        if (strpos($text, $prepend) === 0) {
            return $text;
        }

        return $prepend . $text;
    }

    /**
     * Filter srcsets
     */
    public function calculate_image_srcset(
        array $sources,
        array $size_array,
        string $image_src,
        array $image_meta,
        int $attachmentID
    ) {
        $sources = array_map(
            function (array $source) use ($attachmentID): array {

                $url = trim($source['url'] ?? '');

                if (!empty($url)) {
                    $source['url'] = $this->maybe_get_remote_url($url, $attachmentID);
                }

                return $source;
            },
            $sources
        );

        return $sources;
    }

    /**
     * Strip the protocol from a url
     */
    private function strip_protocol(string $url): string
    {
        return preg_replace('#^https?://#', '', $url);
    }

    /**
     * Check if a url is internal
     */
    private function is_internal_url(string $url): bool
    {
        if (!$currentOrigin = $this->get_current_origin()) {
            return false;
        }

        return str_starts_with(
            (string) wp_parse_url($url, PHP_URL_HOST),
            (string) wp_parse_url($currentOrigin, PHP_URL_HOST)
        );
    }

    /**
     * Get the remote assets origin
     */
    private function get_remote_assets_origin(): ?string
    {
        if (defined('RHAU_REMOTE_ASSETS_ORIGIN')) {
            return $this->get_origin(RHAU_REMOTE_ASSETS_ORIGIN);
        }

        $env = $this->environments['production']
            ?? $this->environments['staging']
            ?? null;

        return $env->origin ?? null;
    }

    /**
     * Try to load files from a different environment if they don't exist locally
     */
    public function maybe_get_remote_url(string $attachmentURL, int $attachmentID): string
    {
        if (file_exists(get_attached_file($attachmentID))) {
            return $attachmentURL;
        }

        if (!$currentOrigin = $this->get_current_origin()) {
            return $attachmentURL;
        }

        if (!$remoteOrigin = $this->get_remote_assets_origin()) {
            return $attachmentURL;
        }

        $remoteHost = $this->strip_protocol($remoteOrigin);
        $currentHost = $this->strip_protocol($currentOrigin);

        // Bail early if the current host is the remote host
        if ($currentHost === $remoteHost) {
            return $attachmentURL;
        }

        // Bail early if the attachment URL is external
        if (!$this->is_internal_url($attachmentURL)) {
            return $attachmentURL;
        }

        return str_replace($currentHost, $remoteHost, $attachmentURL);
    }

    /**
     * Show a notice if the site is set not to be indexed by...
     *
     * - Either activating "Discourage search engines from indexing this site" under wp-admin/options-reading.php
     * - Or defining DISALLOW_INDEXING in the config (should be done in staging)
     */
    public function disallow_indexing_notice()
    {
        /**
         * This will also be false if DISALLOW_INDEXING is set to true
         */
        if ((bool) get_option('blog_public')) {
            return;
        }

        $icon = '<span class="dashicons dashicons-hidden"></span>';
        $message1 = sprintf(
            __('%1$sSearch engine indexing%2$s has been discouraged on this site.', 'rh-admin-utils'),
            '<a href="https://en.wikipedia.org/wiki/Search_engine_indexing" target="_blank">',
            '</a>'
        );
        $message2 = sprintf(
            __('If you want to go live, please <a href="mailto:%1$s">notify your site administrator</a>.', 'rh-admin-utils'),
            get_option('admin_email')
        );

        echo "<div class='notice notice-warning'><p>{$icon} {$message1} {$message2}</p></div>";
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

        if (in_array($this->env, $dev_environment_types, true)) {
            $ttl = YEAR_IN_SECONDS;
        }

        return $ttl;
    }
}
