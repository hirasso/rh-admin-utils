<?php

namespace RH\AdminUtils;

class ACFTextField
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/render_field_settings/type=text', [__CLASS__, 'render_field_settings']);
        add_filter('acf/prepare_field/type=text', [__CLASS__, 'prepare_text_field']);
    }

    /**
     * Render custom ACF field settings
     *
     * @param array $field
     * @return void
     */
    public static function render_field_settings(array $field): void
    {

        acf_render_field_setting($field, [
            'label'  => __('Mask field'),
            'instructions'  => 'Apply a mask to the field\'s value. See <a href="https://alpinejs.dev/plugins/mask">Alpine.js Mask Plugin</a>',
            'name' => 'rhau_mask_field',
            'type' => 'text',
        ]);
    }

    /**
     * Handle ACF text fields
     *
     * @param array $field
     * @return array
     */
    public static function prepare_text_field(array $field): array
    {
        $mask_field = $field['rhau_mask_field'] ?? null;
        if (!$mask_field) {
            return $field;
        }

        $field['wrapper']['rhau-x-data'] = 'ACFTextField';
        $field['wrapper']['data-rhau-input-mask'] = esc_attr($mask_field);

        return $field;
    }
}
