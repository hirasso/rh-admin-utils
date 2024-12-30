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
        add_action('wp_ajax_acf/fields/oembed/search', [__CLASS__, 'validate_oembed_search'], 9);
    }

    /**
     * Render a custom ACF field setting for the whitelist
     */
    public static function render_field_settings(array $field): void
    {
        acf_render_field_setting($field, [
            'label'  => __('Whitelist'),
            'instructions'  => 'Comma-separated list of allowed hosts, for example <code>vimeo.com,youtube.com</code>',
            'name' => 'rhau_oembed_whitelist',
            'type' => 'text',
        ]);
    }

    /**
     * Only allow certain values for oembeds
     */
    public static function validate_oembed_search(): void
    {
        $url = $_POST['s'] ?? null;
        if (!$url) {
            return;
        }

        $field = acf_get_field($_POST['field_key'] ?? null);
        if (!$field) {
            return;
        }

        $whitelist = trim($field['rhau_oembed_whitelist'] ?? '');
        if (empty($whitelist)) {
            return;
        }

        $whitelist = implode('|', array_map('trim', explode(',', $whitelist)));

        $response = wp_oembed_get($url, $field['width'], $field['height']);

        $is_valid = preg_match('/(' . $whitelist . ')/i', $response);

        if ($is_valid) {
            return;
        }

        wp_send_json([
            'url' => "",
            'html' => "
                <div style='padding: 1rem;'>
                    <div class='acf-notice -error acf-error-message oembed-error'>
                        <p>Please provide a valid value (allowed: $whitelist).</p>
                    </div>
                </div>",
        ]);
    }
}
