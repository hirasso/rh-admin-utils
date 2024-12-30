<?php

namespace RH\AdminUtils;

class EditorInChief extends Singleton
{
    public $role_name = 'editor_in_chief';

    public function __construct()
    {
        add_action('admin_init', [$this, 'add_role']);
    }

    /**
     * Add the role if it doesn't exist already
     *
     * @return void
     */
    public function add_role(): void
    {
        $wp_roles = wp_roles();
        if ($wp_roles->is_role($this->role_name)) {
            return;
        }
        $editor_role = get_role('editor');
        $caps = wp_parse_args([
            'update_core' => true,
            'update_plugins' => true,
        ], $editor_role->capabilities);
        add_role($this->role_name, translate_user_role('Editor in Chief', 'rh'), $caps);
    }
}
