<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
        'promote_users'
    ];

    private $allowed_roles = [
        'editor', 'editor_in_chief'
    ];

    public function __construct()
    {

        register_activation_hook(__FILE__, array($this, 'activation_hook'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation_hook'));

        add_action('admin_init', array($this, 'admin_init'));
        add_filter('editable_roles', array($this, 'filter_editable_roles'));
        add_action('admin_footer', array($this, 'admin_footer'));
        add_filter('map_meta_cap', array($this, 'map_meta_cap'), 10, 4);
    }

    /**
     * Admin init hook
     *
     * @return void
     */
    function admin_init()
    {
        if ($this->plugin_version_changed()) {
            $this->activation_hook();
        }
    }

    /**
     * Check if the plugin version has changed
     *
     * @return void
     */
    function plugin_version_changed()
    {
        $option_key = 'rh_editors_add_users_version';
        $plugin = get_plugin_data(__FILE__);
        $version = $plugin['Version'] ?? false;
        $db_version = get_option($option_key);
        update_option($option_key, $version);
        return $db_version !== $version;
    }

    /**
     * Plugin activation hook
     *
     * @return void
     */
    function activation_hook()
    {
        $this->add_caps();
    }

    /**
     * Plugin deactivation hook
     *
     * @return void
     */
    function deactivation_hook()
    {
        $this->remove_caps();
    }

    /**
     * Remove caps
     *
     * @return void
     */
    function remove_caps()
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
     *
     * @return void
     */
    function add_caps()
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
        return  $this;
    }

    /**
     * Remove Administrators from editable roles for users lower than editors
     *
     * @param array $roles
     * @return array
     */
    function filter_editable_roles($roles)
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
     *
     * @return void
     */
    function admin_footer()
    {
        global $pagenow;
        if (current_user_can('administrator')) return;
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
    function map_meta_cap($caps, $cap, $user_id, $args)
    {
        $check_caps = [
            'edit_user',
            'remove_user',
            'promote_user',
            'promote_users',
            'delete_user',
            'delete_users',
        ];
        // $check_caps = $this->caps;
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
