<?php

/**
* Plugin Name:     ACF oEmbed Whitelist
* Description:     Only allow certain providers for selected oEmbed fields
* Version:         1.0.0
* Requires PHP:    8.0
* Author:          Rasso Hilber
* Author URI:      https://rassohilber.com/
*/

namespace RH\AdminUtils;

class ACFOembedWhitelist
{
    /** Init */
    public static function init()
    {
        add_action('acf/render_field_settings/type=oembed', [__CLASS__, 'render_field_settings']);
        /** Runs before ACF's own handler (priority 10) so that it can short-circuit it */
        add_action('wp_ajax_acf/fields/oembed/search', [__CLASS__, 'validate_oembed_search'], 9);
    }

    /**
     * Render a custom ACF field setting for the whitelist
     */
    public static function render_field_settings(array $field): void
    {
        acf_render_field_setting($field, [
            'label'  => __('Whitelist', 'rh-admin-utils'),
            'instructions'  => 'Comma-separated list of allowed hosts, for example <code>vimeo.com,youtube.com</code>',
            'name' => 'rhau_oembed_whitelist',
            'type' => 'text',
        ]);
    }

    /**
     * Only allow certain hosts for oembeds.
     *
     * Bails out silently for anything it doesn't handle, so that ACF's own
     * handler (which verifies the nonce again) stays in charge.
     */
    public static function validate_oembed_search(): void
    {
        if (!function_exists('acf_verify_ajax') || !function_exists('acf_request_arg')) {
            return;
        }

        /** Same check ACF performs in acf_field_oembed::ajax_query() */
        if (!acf_verify_ajax(acf_request_arg('nonce', ''), acf_request_arg('field_key', ''), true)) {
            die();
        }

        /**
         * Read the raw $_POST rather than acf_request_args(), which runs values
         * through wp_kses(). ACF's handler passes the raw $_POST to
         * acf_field_oembed::get_ajax_query(), so validating the sanitized copies
         * would leave a gap: `https://youtube.com<x @evil.com>/` survives kses as
         * `https://youtube.com/` but is fetched with `evil.com` as its host.
         */
        $url = $_POST['s'] ?? null;
        $field_key = $_POST['field_key'] ?? null;

        if (!is_string($url) || $url === '' || !is_string($field_key)) {
            return;
        }

        $field = acf_get_field($field_key);
        if (!$field) {
            return;
        }

        $allowed_hosts = self::get_allowed_hosts($field['rhau_oembed_whitelist'] ?? '');
        if (empty($allowed_hosts)) {
            return;
        }

        if (self::is_allowed_url($url, $allowed_hosts)) {
            return;
        }

        $message = sprintf(
            /* translators: %s: comma-separated list of allowed hosts */
            __('Please provide a valid value (allowed: %s).', 'rh-admin-utils'),
            esc_html(implode(', ', $allowed_hosts))
        );

        wp_send_json([
            'url' => "",
            'html' => "
                <div style='padding: 1rem;'>
                    <div class='acf-notice -error acf-error-message oembed-error'>
                        <p>$message</p>
                    </div>
                </div>",
        ]);
    }

    /**
     * Parse the comma-separated whitelist setting into a list of normalized hosts.
     *
     * Entries may be plain hosts (`youtube.com`) or full URLs (`https://youtube.com/`).
     *
     * @return list<string>
     */
    private static function get_allowed_hosts(mixed $whitelist): array
    {
        if (!is_string($whitelist) || trim($whitelist) === '') {
            return [];
        }

        $hosts = array_map(
            function (string $entry): ?string {
                $entry = trim($entry);

                /** Allow full URLs to be pasted into the setting */
                if (str_contains($entry, '://')) {
                    $entry = (string) wp_parse_url($entry, PHP_URL_HOST);
                }

                /** Drop anything following the host, e.g. a path or a port */
                $entry = preg_replace('#[/:?\#].*$#', '', $entry) ?? '';

                return self::normalize_host($entry);
            },
            explode(',', $whitelist)
        );

        return array_values(array_unique(array_filter($hosts)));
    }

    /**
     * Normalize a host for comparison: lowercase, no trailing dot, no `www.` prefix
     */
    private static function normalize_host(mixed $host): ?string
    {
        if (!is_string($host)) {
            return null;
        }

        $host = strtolower(trim($host, " \t\n\r\0\x0B."));

        if ($host === '') {
            return null;
        }

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host ?: null;
    }

    /**
     * Check a URL's host against the allowed hosts.
     *
     * Matches the host itself and any of its subdomains, so that `youtube.com`
     * also allows `www.youtube.com` and `music.youtube.com`.
     *
     * @param list<string> $allowed_hosts
     */
    private static function is_allowed_url(string $url, array $allowed_hosts): bool
    {
        /**
         * Reject anything that isn't a clean URL. Parsers disagree about where the
         * host ends in a URL containing whitespace, angle brackets, a backslash or
         * control characters, so never hand those on to be resolved elsewhere.
         */
        if (preg_match('/[\s<>\\\\]|[\x00-\x1f\x7f]/', $url)) {
            return false;
        }

        $scheme = strtolower((string) wp_parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = self::normalize_host(wp_parse_url($url, PHP_URL_HOST));
        if (!$host) {
            return false;
        }

        foreach ($allowed_hosts as $allowed) {
            if ($host === $allowed || str_ends_with($host, ".$allowed")) {
                return true;
            }
        }

        return false;
    }
}
