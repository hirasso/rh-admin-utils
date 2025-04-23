<?php

namespace RH\AdminUtils;

/**
 * Allows editors to add users
 */
class EditorsAddUsers extends Singleton
{
    private $caps = [
        'create_users',
        'edit_users',
        'edit_user',
        'delete_user',
        'delete_users',
        'list_users',
        'promote_user',
        'promote_users',
    ];

    private $allowed_roles = [
        'editor', 'editor_in_chief',
    ];

    public function __construct()
    {

        register_activation_hook(__FILE__, [$this, 'activation_hook']);
        register_deactivation_hook(__FILE__, [$this, 'deactivation_hook']);

        add_action('admin_init', [$this, 'admin_init']);
        add_filter('editable_roles', [$this, 'filter_editable_roles']);
        add_action('admin_footer', [$this, 'admin_footer']);
        add_filter('map_meta_cap', [$this, 'map_meta_cap'], 10, 4);
    }

    /**
     * Admin init hook
     */
    public function admin_init(): void
    {
        if ($this->plugin_version_changed()) {
            $this->activation_hook();
        }
    }

    /**
     * Check if the plugin version has changed
     */
    private function plugin_version_changed(): bool
    {
        $option_key = 'rhau_editors_add_users_version';
        $plugin = get_plugin_data(pluginFile());
        $version = $plugin['Version'] ?? null;
        $db_version = get_option($option_key);
        update_option($option_key, $version);
        return $db_version !== $version;
    }

    /**
     * Plugin activation hook
     */
    public function activation_hook(): void
    {
        $this->add_caps();
    }

    /**
     * Plugin deactivation hook
     */
    public function deactivation_hook(): void
    {
        $this->remove_caps();
    }

    /**
     * Remove caps
     */
    private function remove_caps()
    {
        foreach ($this->allowed_roles as $r) {
            $role = get_role($r);
            if (!$role || is_wp_error($role)) {
                continue;
            }
            foreach ($this->caps as $cap) {
                $role->remove_cap($cap);
            }
        }
        return $this;
    }

    /**
     * Add caps
     */
    private function add_caps(): void
    {
        $this->remove_caps();
        foreach ($this->allowed_roles as $r) {
            $role = get_role($r);
            if (!$role || is_wp_error($role)) {
                continue;
            }
            foreach ($this->caps as $cap) {
                $role->add_cap($cap);
            }
        }
    }

    /**
     * Remove Administrators from editable roles for users lower than editors
     */
    public function filter_editable_roles(array $roles): array
    {
        $user = wp_get_current_user();
        if (!current_user_can('administrator')) {
            unset($roles['administrator']);
        }
        if (current_user_can('editor')) {
            unset($roles['editor_in_chief']);
        }
        return $roles;
    }

    /**
     * Uncheck 'Send user notification'-checkbox
     */
    public function admin_footer(): void
    {
        global $pagenow;
        if (current_user_can('administrator')) {
            return;
        }
        if ($pagenow === 'user-new.php') : ?>
            <script>
                document.getElementById("send_user_notification").checked = false;
            </script>
        <?php endif;
    }

    /**
     * Filters a user's capabilities depending on specific context and/or privilege.
     *
     * Inspired by https://wordpress.stackexchange.com/questions/4479/editor-can-create-any-new-user-except-administrator
     *
     * @since 2.8.0
     *
     * @param string[] $caps    Array of the user's capabilities.
     * @param string   $cap     Capability name.
     * @param int      $user_id The user ID.
     * @param array    $args    Adds the context to the cap. Typically the object ID.
     */
    public function map_meta_cap(array $caps, string $cap, $user_id, $args)
    {
        $check_caps = [
            'edit_user',
            'remove_user',
            'promote_user',
            'promote_users',
            'delete_user',
            'delete_users',
        ];
        if (!in_array($cap, $check_caps) || current_user_can('administrator')) {
            return $caps;
        }
        $other = get_user_by('id', $args[0] ?? false);
        if ($other && $other->has_cap('administrator')) {
            $caps[] = 'do_not_allow';
        }
        return $caps;
    }
}
