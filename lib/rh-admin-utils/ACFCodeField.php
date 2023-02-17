<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ACFCodeField
{

    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/render_field_settings/type=textarea', [__CLASS__, 'render_field_settings']);
        add_filter('acf/prepare_field/type=textarea', [__CLASS__, 'prepare_acf_code_field']);
    }

    /**
     * Render custom ACF field settings
     *
     * @param array $field
     * @return void
     */
    public static function render_field_settings(array $field): void
    {

        acf_render_field_setting($field, array(
            'label'  => __('Code field'),
            'instructions'  => 'Convert to a code field for the selected language',
            'name' => 'rhau_code_field',
            'type' => 'select',
            'allow_null' => 1,
            'choices' => [
                'json' => 'JSON',
                'html' => 'HTML'
            ],
        ));
    }

    /**
     * Handle ACF code fields
     *
     * @param array $field
     * @return array
     */
    public static function prepare_acf_code_field(array $field): array
    {
        $language = $field['rhau_code_field'] ?? null;
        if (!$language) return $field;

        $field['wrapper']['rhau-x-data'] = 'ACFCodeField';
        $field['wrapper']['data-rhau-code-language'] = esc_attr($language);
        $field['new_lines'] = '';

        return $field;
    }


}
