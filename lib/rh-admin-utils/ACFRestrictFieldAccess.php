<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ACFRestrictFieldAccess
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
        acf_render_field_setting($field, array(
            'label'         => __('Roles allowed to edit this field'),
            'instructions'  => '',
            'name'          => 'restrict_access',
            'type'          => 'select',
            'choices'       => self::get_choices(),
            'multiple'      => true,
            'ui'            => true
        ), true);
    }

    /**
     * Get the choices for the restrict access field setting
     */
    private static function get_choices(): array
    {
        $choices = [];
        $roles = get_editable_roles();
        foreach ($roles as $key => $role) {
            $choices[$key] = $role['name'];
        }
        return $choices;
    }

    /**
     * Restrict access to a field
     */
    public static function prepare_field($field)
    {

        if (empty($field)) return $field;

        $caps = $field['restrict_access'] ?? '' ?: [];

        foreach ($caps as $cap) {
            if (!current_user_can($cap)) return false;
        }

        return $field;
    }
}
