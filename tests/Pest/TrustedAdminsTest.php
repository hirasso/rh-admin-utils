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

    /**
     * `switch_to_user` (from the "User Switching" plugin) always evaluates to
     * false without a target user id, regardless of trust status, so it can't
     * be asserted in the generic loops below. It gets its own dedicated tests.
     */
    private const CAPS_REQUIRING_TARGET = [
        'switch_to_user',
    ];

    public function test_filter_not_hooked_leaves_administrator_caps_untouched(): void
    {
        $admin = $this->factory()->user->create_and_get(['role' => 'administrator']);
        wp_set_current_user($admin->ID);

        foreach (array_diff(TrustedAdmins::RESTRICTED_CAPS, self::CAPS_REQUIRING_TARGET) as $cap) {
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
        foreach (array_diff(TrustedAdmins::RESTRICTED_CAPS, self::CAPS_REQUIRING_TARGET) as $cap) {
            $this->assertTrue(current_user_can($cap), "Expected alice to have '$cap'");
        }

        wp_set_current_user($bob->ID);
        foreach (array_diff(TrustedAdmins::RESTRICTED_CAPS, self::CAPS_REQUIRING_TARGET) as $cap) {
            $this->assertFalse(current_user_can($cap), "Expected bob to NOT have '$cap'");
        }
    }

    public function test_empty_allowlist_restricts_every_administrator(): void
    {
        $admin = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);

        add_filter('rhau/trusted_admins', fn () => []);

        wp_set_current_user($admin->ID);
        foreach (array_diff(TrustedAdmins::RESTRICTED_CAPS, self::CAPS_REQUIRING_TARGET) as $cap) {
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

    public function test_switch_to_user_allowed_for_administrator_when_not_restricted(): void
    {
        $admin = $this->factory()->user->create_and_get(['role' => 'administrator']);
        $target = $this->factory()->user->create_and_get(['role' => 'administrator']);

        wp_set_current_user($admin->ID);
        $this->assertTrue(current_user_can('switch_to_user', $target->ID), "Expected administrator to have 'switch_to_user'");
    }

    public function test_switch_to_user_denied_for_non_trusted_administrator(): void
    {
        $alice = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);
        $bob = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'bob']);

        add_filter('rhau/trusted_admins', fn () => ['alice']);

        wp_set_current_user($bob->ID);
        $this->assertFalse(current_user_can('switch_to_user', $alice->ID), "Expected bob (not trusted) to NOT have 'switch_to_user'");
    }

    public function test_switch_to_user_allowed_for_trusted_administrator_switching_to_a_non_trusted_administrator(): void
    {
        $alice = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'alice']);
        $bob = $this->factory()->user->create_and_get(['role' => 'administrator', 'user_login' => 'bob']);

        add_filter('rhau/trusted_admins', fn () => ['alice']);

        wp_set_current_user($alice->ID);
        $this->assertTrue(current_user_can('switch_to_user', $bob->ID), "Expected alice (trusted) to have 'switch_to_user', even targeting a non-trusted admin");
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
        foreach (array_diff(TrustedAdmins::RESTRICTED_CAPS, self::CAPS_REQUIRING_TARGET) as $cap) {
            $this->assertTrue(current_user_can($cap), "Expected alice to have '$cap'");
        }

        wp_set_current_user($bob->ID);
        foreach (array_diff(TrustedAdmins::RESTRICTED_CAPS, self::CAPS_REQUIRING_TARGET) as $cap) {
            $this->assertFalse(current_user_can($cap), "Expected bob to NOT have '$cap' (constant should override filter)");
        }
    }
}
