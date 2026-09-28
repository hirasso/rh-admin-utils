---
"rh-admin-utils": major
---

Composer installs now come from this repository instead of the separate `-dist` repository.

Dependencies are no longer scoped for Composer installs — Composer resolves `symfony/var-dumper` and `yahnis-elsts/plugin-update-checker` into your project's own vendor directory, and the plugin folder no longer contains a bundled `vendor/`. The zip release asset is unchanged: it still ships a fully scoped, self-contained build for manual and WP-admin installs.

`sniccowp/php-scoper-wordpress-excludes` is no longer a Composer dependency; the release script fetches the WordPress symbol lists directly during the build.

`symfony/var-dumper` is now required as `*` so it can never conflict with a version your project already uses.
