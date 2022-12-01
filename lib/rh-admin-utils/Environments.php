<?php


namespace RH\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Environments extends Singleton {

  private $env;

  /**
   * Constructor
   */
  public function __construct() {

    if( !$this->globals_defined() ) return;

    $this->env = $this->get_environment_type();
    add_action('admin_init', [$this, 'update_network_sites']);
    add_filter('network_admin_url', [$this, 'network_admin_url'], 10, 2);
    add_filter('auth_cookie_expiration', [$this, 'auth_cookie_expiration'], PHP_INT_MAX - 1, 3 );

    if( $this->env && $this->env !== 'production' ) {
      $this->add_non_production_hooks();
    }

    add_action( 'admin_notices', array( $this, 'site_private_notice') );

  }

  /**
   * Checks for the existance of needed globals
   *
   * @return bool
   */
  private function globals_defined(): bool {
    $globals = ['WP_HOME_DEV', 'WP_SITEURL_DEV', 'WP_SITEURL_PROD', 'WP_SITEURL_PROD'];
    foreach($globals as $var) {
      if( !defined($var) ) return false;
    }
    return true;
  }

  /**
   * Filter network admin url
   *
   * @param string $url
   * @param string $path
   * @return string $url The new URL
   */
  public function network_admin_url($url, $path) {
    if( defined('WP_SITEURL') ) {
      $path = substr($url, strpos($url, '/wp-admin/'));
      $url = WP_SITEURL . $path;
    }
    return $url;
  }

  /**
   * Updates network options
   *
   * @return void
   */
  public function update_network_sites() {
    if( !is_multisite() ) return;
    if( !defined('RH_NETWORK_SITES') || !is_array(RH_NETWORK_SITES) ) return;
    $id = 0;
    foreach( RH_NETWORK_SITES as $site ) {
      $id ++;
      update_blog_option( $id, 'siteurl', $site[$this->env]['siteurl'] );
      update_blog_option( $id, 'home', $site[$this->env]['home'] );
      $domain = wp_parse_url($site[$this->env]['home']);
      wp_update_site($id, [
        'domain' => $domain['host'],
      ]);
    }
  }

  /**
   * Add all the hooks
   *
   * @return void
   */
  private function add_non_production_hooks() {
    add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
    add_filter( 'wp_calculate_image_srcset', array(&$this, 'calculate_image_srcset'), 11 );
    add_filter( 'wp_get_attachment_url', array(&$this, 'get_attachment_url'), 11 );
    add_filter( 'document_title_parts', array( $this, 'document_title_parts') );
    add_filter( 'admin_title', array( $this, 'admin_title' ) );
    add_action( 'wp_footer', array( $this, 'environment_quick_links' ) );
    add_action( 'admin_footer', array( $this, 'environment_quick_links' ) );
    if( $this->env === 'staging' ) {
      add_filter('wp_robots', 'wp_robots_no_robots');
    }
    if( $this->env === 'development' ) {
      add_filter('https_ssl_verify', '__return_false');
    }
  }

  /**
   * Get the value of WP_ENV
   *
   * @return void
   */
  private function get_environment_type() {
    if( defined('WP_ENV') ) return WP_ENV;
    return wp_get_environment_type();
  }

  /**
   * Render quick-links to other environments
   *
   * @return void
   */
  public function environment_quick_links() {
    $dev_root = get_option('home');
    $remote_root_production = defined("WP_HOME_PROD") ? WP_HOME_PROD : false;
    $remote_root_staging = defined("WP_HOME_STAGING") ? WP_HOME_STAGING : false;
    if( is_admin() ) {
      $dev_root = defined('WP_SITEURL_DEV') ? WP_SITEURL_DEV : false;
      $remote_root_production = defined("WP_SITEURL_PROD") ? WP_SITEURL_PROD : false;
      $remote_root_staging = defined("WP_SITEURL_STAGING") ? WP_SITEURL_STAGING : false;
    }
    if( is_multisite() ) {
      $dev_root = get_option('home');
      $home_dev = defined('WP_HOME_DEV') ? WP_HOME_DEV : false;
      $remote_root_production = str_replace( $home_dev, $remote_root_production, get_option('home'));
      $remote_root_staging = str_replace( $home_dev, $remote_root_staging, get_option('home'));
    }
    ?>
    <div class="rh-environment-links" data-dev-root="<?= $dev_root ?>">
      <a class="rh-environment-link bypass-router" data-remote-root="<?= $remote_root_production ?>" href="">Production</a>
      <?php if( $remote_root_staging ) : ?>
      <a class="rh-environment-link bypass-router" data-remote-root="<?= $remote_root_staging ?>" href="">Staging</a>
      <?php endif; ?>
    </div>
    <?php
  }

  /**
   * Enqueue assets
   *
   * @return void
   */
  public function assets() {
    wp_enqueue_style( 'rh-staging-server', rhau()->asset_uri("assets/rhau-environments.css"), [], null );
    wp_enqueue_script( 'rh-staging-server', rhau()->asset_uri("assets/rhau-environments.js"), array("jquery"), null, true );
  }

  /**
   * Filter document title parts
   *
   * @param array $parts
   * @return array
   */
  public function document_title_parts( $parts ) {
    $parts['title'] = $this->document_title_env_prefix( $parts['title'] );
    return $parts;
  }

  /**
   * Filter admin title
   *
   * @param string $title
   * @return string
   */
  public function admin_title( $title ) {
    return $this->document_title_env_prefix( $title );
  }

  /**
   * Add ENV prefix to string
   *
   * @param string $title
   * @return string
   */
  private function document_title_env_prefix( $title ) {
    $title = (string) $title;
    switch ($this->env) {
      case 'development':
        return $this->prepend_to_string($title, 'DEV: ');
        return "DEV: $title";
        break;
      case 'staging':
        return $this->prepend_to_string($title, 'STAGING: ');
        return "STAGING: $title";
        break;
    }
    return $title;
  }

  /**
   * Prepend a string to another string
   *
   * @param string $text
   * @param string $prepend
   * @return string
   */
  private function prepend_to_string(string $text, string $prepend): string {
    if( strpos($text, $prepend) === 0 ) return $text;
    return $prepend . $text;
  }

  /**
   * Filter attachments
   *
   * @param string $url
   * @return string
   */
  function get_attachment_url( $url ) {
    $url = $this->maybe_get_remote_url( $url );
    return $url;
  }

  /**
   * Filter srcsets
   *
   * @param [type] $sources
   * @return void
   */
  function calculate_image_srcset( $sources ) {

    foreach( $sources as &$source ) {
      $url = !empty( $source['url'] ) ? $source['url'] : false;
      if( !$url ) {
        continue;
      }
      $url = $this->maybe_get_remote_url( $url );
      $source['url'] = $url;
    }
    return $sources;
  }

  /**
   * Try to load remote file url if missing locally
   *
   * @param string $url
   * @return string
   */
  function maybe_get_remote_url( $url ) {

    // bail early if the $url is external
    if( !str_starts_with($url, get_option('home')) ) return $url;

    $upload_dir = wp_upload_dir();
    $file = $upload_dir["basedir"] . str_replace( $upload_dir["baseurl"], "", $url );

    if( file_exists( $file ) ) {
      return  $url;
    }

    switch( $this->env ) {
      case 'development':
      case 'dev':
        if( defined('WP_CONTENT_URL_DEV') ) {
          $url = str_replace(WP_CONTENT_URL_DEV, WP_CONTENT_URL_PROD, $url);
        }
        break;
      case 'staging':
        if( defined('WP_CONTENT_URL_STAGING') ) {
          $url = str_replace(WP_CONTENT_URL_STAGING, WP_CONTENT_URL_PROD, $url);
        }
        break;
    }

    return $url;
  }

  /**
   * Show a notice if the site is private (blog_public is set to 0)
   *
   * @return void
   */
  function site_private_notice() {
    if( get_option('blog_public') !== '0' ) {
      return;
    }
    $admin_email = apply_filters('rh/environments/admin_email', 'mail@rassohilber.com');
    $headline = "<strong>This site is still in private mode, so search engines are being blocked.</strong>";
    $body = "If you want to go live, please <a href='mailto:$admin_email'>notify your site's administrator</a>.";
    echo "<div class='notice notice-warning'><p><span class='dashicons dashicons-hidden'></span> {$headline} {$body}</p></div>";
  }

  /**
   * Set the auth cookie expiration to one year if in development
   *
   * @param int $ttl            time to live. default DAY_IN_SECOND*2
   * @param int $user_id
   * @param bool $remember
   * @return int
   */
  public function auth_cookie_expiration(int $ttl, int $user_id, bool $remember ): int {

    // Adjust to your working environment needs.
    $dev_environment_types = [ 'development', 'local' ];

    if( in_array( $this->env, $dev_environment_types, true ) ) $ttl = YEAR_IN_SECONDS;

    return $ttl;
  }

}
