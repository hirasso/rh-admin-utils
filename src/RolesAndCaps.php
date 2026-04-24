<?php

namespace RH\AdminUtils;

use WP_Role;

final class RolesAndCaps extends Singleton
{
    public const EDITOR_IN_CHIEF_ROLE = 'editor_in_chief';

    public function __construct()
    {
        /** editor in chief */
        add_action('admin_init', $this->add_editor_in_chief(...));

        /** privacy options */
        add_action('admin_init', $this->allow_manage_privacy_options(...));
        add_action('plugins_loaded', $this->redirect_to_privacy_policy_guide(...));
        add_filter('map_meta_cap', $this->map_meta_cap_privacy_options(...), 1, 2);
    }

    /**
     * Allow editors to manage privacy options
     */
    private function allow_manage_privacy_options(): void
    {
        /** @var list<WP_Role> $roles */
        $roles = array_filter(
            [
                get_role('editor'),
                get_role(self::EDITOR_IN_CHIEF_ROLE)
            ],
            fn ($role) => $role instanceof WP_Role
        );

        foreach ($roles as $role) {
            if (!$role->has_cap('manage_privacy_options')) {
                $role->add_cap('manage_privacy_options');
            }
        }
    }

    /**
     * Redirect /wp-admin/options-privacy.php?tab=policyguide which is only
     * accessible to users with cap `manage_options` to wp-admin/privacy-policy-guide.php,
     * which can be accessed by all users with the `manage_privacy_options` cap
     */
    private function redirect_to_privacy_policy_guide(): void
    {
        global $pagenow;

        if (current_user_can('manage_options')) {
            return;
        }

        if ($pagenow !== 'options-privacy.php') {
            return;
        }

        if (($_GET['tab'] ?? null) !== 'policyguide') {
            return;
        }

        if (current_user_can('manage_privacy_options')) {
            rhau()->redirect(admin_url('privacy-policy-guide.php'));
        }
    }

    /**
     * Add the role if it doesn't exist already
     */
    private function add_editor_in_chief(): void
    {
        $wp_roles = wp_roles();

        if (
            !$wp_roles->is_role('editor')
            || $wp_roles->is_role(self::EDITOR_IN_CHIEF_ROLE)
        ) {
            return;
        }

        $caps = wp_parse_args([
            'update_core' => true,
            'update_plugins' => true,
        ], get_role('editor')->capabilities);

        add_role(
            self::EDITOR_IN_CHIEF_ROLE,
            translate_user_role('Editor in Chief', 'rh-admin-utils'),
            $caps
        );
    }

    /**
     * Changes cap to to manage the privacy page from manage_options to edit_others_posts
     */
    private function map_meta_cap_privacy_options(array $caps, string $cap): array
    {
        if (! is_user_logged_in()) {
            return $caps;
        }

        if ($cap !== 'manage_privacy_options') {
            return $caps;
        }

        return ['edit_others_posts'];
    }
}
