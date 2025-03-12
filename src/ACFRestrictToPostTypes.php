<?php

namespace RH\AdminUtils;

class ACFRestrictToPostTypes
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/render_field_settings', [__CLASS__, 'render_field_settings']);
        add_filter('acf/prepare_field', [__CLASS__, 'prepare_field']);
    }

    /**
     * Render a field setting to restrict access to a field
     */
    public static function render_field_settings($field)
    {
        acf_render_field_setting($field, [
            'label'         => __('Restrict visibility to Post Types'),
            'instructions'  => '',
            'name'          => 'rhau_restrict_to_post_types',
            'type'          => 'select',
            'choices'       => self::get_choices(),
            'multiple'      => true,
            'ui'            => true,
        ], true);
    }

    /**
     * Get the choices for the field setting
     */
    private static function get_choices(): array
    {
        return array_map(
            fn ($pt) => get_post_type_object($pt)->labels->name,
            get_post_types(['public' => true])
        );
    }

    /**
     * Restrict access to a field based on the current post type
     */
    public static function prepare_field($field): ?array
    {
        if (empty($field)) {
            return null;
        }

        /** @var string[] $postTypes */
        $postTypes = $field['rhau_restrict_to_post_types'] ?? [];

        if (empty($postTypes) || !is_array($postTypes)) {
            return $field;
        }

        global $pagenow;

        $isRestricted = !in_array(get_post_type(), $postTypes)
            || !in_array($pagenow, ['post.php', 'post-new.php']);

        return $isRestricted
            ? null
            : $field;
    }
}
