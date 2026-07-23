<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\Hardening\TrustedAdmins;

class TrustedAdminsTest extends IntegrationTestCase
{
    private const EXEMPT_CAPS = [
        'update_core',
        'update_plugins',
        'update_themes',
    ];

    public function test_filter_not_hooked_leaves_administrator_caps_untouched(): void
    {
        $admin = $this->factory()->user->create_and_get(['role' => 'administrator']);
        wp_set_current_user($admin->ID);

        foreach (TrustedAdmins::RESTRICTED_CAPS as $cap) {
            $this->assertTrue(current_user_can($cap), "Expected administrator to have '$cap'");
        }
        foreach (self::EXEMPT_CAPS as $cap) {
            $this->assertTrue(current_user_can($cap), "Expected administrator to have '$cap'");
        }
    }

    public function test_trusted_user_login_keeps_caps_others_lose_them(): void
    {
        $alice = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);
        $bob = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'bob']);

        add_filter('rhau/trusted_admins', fn () => ['alice']);

        wp_set_current_user($alice->ID);
        foreach (TrustedAdmins::RESTRICTED_CAPS as $cap) {
            $this->assertTrue(current_user_can($cap), "Expected alice to have '$cap'");
        }

        wp_set_current_user($bob->ID);
        foreach (TrustedAdmins::RESTRICTED_CAPS as $cap) {
            $this->assertFalse(current_user_can($cap), "Expected bob to NOT have '$cap'");
        }
    }

    public function test_empty_allowlist_restricts_every_administrator(): void
    {
        $admin = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);

        add_filter('rhau/trusted_admins', fn () => []);

        wp_set_current_user($admin->ID);
        foreach (TrustedAdmins::RESTRICTED_CAPS as $cap) {
            $this->assertFalse(current_user_can($cap), "Expected '$cap' to be restricted for everyone");
        }
    }

    public function test_exempt_caps_always_remain_regardless_of_allowlist(): void
    {
        $admin = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'bob']);

        add_filter('rhau/trusted_admins', fn () => []);

        wp_set_current_user($admin->ID);
        foreach (self::EXEMPT_CAPS as $cap) {
            $this->assertTrue(current_user_can($cap), "Expected '$cap' to remain allowed");
        }
    }

    public function test_trusted_admin_cannot_be_deleted_by_a_non_trusted_administrator(): void
    {
        $alice = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);
        $bob = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'bob']);

        add_filter('rhau/trusted_admins', fn () => ['alice']);

        wp_set_current_user($bob->ID);
        $this->assertFalse(current_user_can('delete_user', $alice->ID), 'Expected bob (not trusted) to NOT be able to delete alice');
    }

    public function test_trusted_admin_can_be_deleted_by_another_trusted_administrator(): void
    {
        $alice = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);
        $carol = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'carol']);

        add_filter('rhau/trusted_admins', fn () => ['alice', 'carol']);

        wp_set_current_user($alice->ID);
        $this->assertTrue(current_user_can('delete_user', $carol->ID), 'Expected alice (trusted) to be able to delete carol (also trusted)');
    }

    /**
     * Must run last: defining the constant leaks for the rest of this process
     * and would short-circuit the filter-based tests above.
     */
    public function test_constant_takes_precedence_over_filter(): void
    {
        $alice = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);
        $bob = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'bob']);

        add_filter('rhau/trusted_admins', fn () => ['bob']);
        define('RHAU_TRUSTED_ADMINS', ['alice']);

        wp_set_current_user($alice->ID);
        foreach (TrustedAdmins::RESTRICTED_CAPS as $cap) {
            $this->assertTrue(current_user_can($cap), "Expected alice to have '$cap'");
        }

        wp_set_current_user($bob->ID);
        foreach (TrustedAdmins::RESTRICTED_CAPS as $cap) {
            $this->assertFalse(current_user_can($cap), "Expected bob to NOT have '$cap' (constant should override filter)");
        }
    }
}
