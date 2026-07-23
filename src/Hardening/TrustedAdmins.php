<?php

declare(strict_types=1);

namespace RH\AdminUtils\Hardening;

/**
 * Restricts high-privilege administrator capabilities to an explicit allowlist of trusted admins.
 *
 * Inactive by default: a site must either define the `RHAU_TRUSTED_ADMINS`
 * constant or hook `rhau/trusted_admins` (as an array of `user_login`s)
 * to activate this. The constant takes precedence when both are present. Sites that
 * do neither see zero behavior change.
 *
 * Additionally, once active, trusted admins can only be deleted by other
 * trusted admins, never by a non-trusted administrator.
 */
final class TrustedAdmins
{
    public const RESTRICTED_CAPS = [
        'install_plugins',
        'install_themes',
        'delete_themes',
        'delete_plugins',
        'edit_plugins',
        'edit_themes',
        'edit_files',
        'create_users',
        'delete_users',
        'unfiltered_html',
        'activate_plugins',
    ];

    public static function init(): void
    {
        add_filter('map_meta_cap', self::mapMetaCap(...), 10, 4);
    }

    /**
     * @return list<string>|null
     */
    private static function getTrustedAdmins(): ?array
    {
        if (defined('RHAU_TRUSTED_ADMINS')) {
            return constant('RHAU_TRUSTED_ADMINS');
        }

        return apply_filters('rhau/trusted_admins', null);
    }

    private static function mapMetaCap(array $caps, string $cap, int $user_id, array $args): array
    {
        $trusted_admins = self::getTrustedAdmins();

        if ($trusted_admins === null) {
            return $caps;
        }

        if (
            $cap === 'delete_user'
            && !self::isTrustedAdmin($user_id, $trusted_admins)
            && self::isTrustedAdmin($args[0] ?? null, $trusted_admins)
        ) {
            $caps[] = 'do_not_allow';

            return $caps;
        }

        if (!in_array($cap, self::RESTRICTED_CAPS, true)) {
            return $caps;
        }

        if (self::isTrustedAdmin($user_id, $trusted_admins)) {
            return $caps;
        }

        $caps[] = 'do_not_allow';

        return $caps;
    }

    private static function isTrustedAdmin(mixed $user_id, array $trusted_admins): bool
    {
        if (!is_numeric($user_id)) {
            return false;
        }

        $user = get_userdata((int) $user_id);

        return $user && in_array($user->user_login, $trusted_admins, true);
    }
}
