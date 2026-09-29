---
"rh-admin-utils": minor
---

Ship one identical plugin directory through both install channels

The plugin previously had two shapes. A composer install got the git archive of the
tag, with no plugin-local `vendor/` and an unprefixed `symfony/var-dumper` installed
into the consuming site's root vendor. The release zip got a php-scoper build with its
own prefixed `vendor/` and a stripped-down `composer.dist.json`. On a site that
installs via composer but updates through the WP admin, a WP-admin update left the
unprefixed `symfony/var-dumper` behind in the site's vendor folder as a dangling
dependency, and the site's `composer.lock` kept reporting the pre-update version.

Dependencies are now prefixed by [Strauss](https://github.com/BrianHenryIE/strauss)
into `vendor-prefixed/`, which is committed to the repo. Because the prefixed code is
inside the git tag, `git archive` and the release zip carry the same files, and the
plugin loads identically either way. Nothing prefixed remains in `require`, so composer
no longer installs anything into the consuming site on the plugin's behalf.

- `symfony/var-dumper` moved to `require-dev`; `require` is now only `php` and
  `composer/installers`. Keeping it in `require-dev` means `composer audit` still
  reports CVEs in the code that ships.
- The plugin's own classes are autoloaded by a small PSR-4 registration in the main
  plugin file instead of a composer autoloader, so it no longer depends on a
  plugin-local `vendor/` that only ever existed in one of the two channels.
- Strauss prefixes var-dumper's `dump()`/`dd()` to `rhau_vendor_*`, so the plugin
  re-serves them: `RH\AdminUtils\dump()`/`dd()` for its own code, and global
  `dump()`/`dd()` for everything else on the site. The globals are declared only when
  nothing else has, so a site that already loads an unprefixed var-dumper keeps its own
  copy. Define `RHAU_GLOBAL_DEBUG_FUNCTIONS` as `false` in wp-config.php to opt out.
- `composer.dist.json`, `config/scoper.config.php` and the php-scoper WordPress
  symbol excludes are gone.
- `composer prefix` regenerates `vendor-prefixed/`, wired to `post-update-cmd` only —
  not `post-install-cmd`, which would rewrite committed output on every install.
- `config/cli/cli.js verify:prefixed` proves the committed output matches
  `composer.lock` and that no unprefixed namespace survived. It runs in CI and before
  every release.
- `symfony/var-dumper` is constrained to `^7.4`. Its 8.x line requires PHP >= 8.4.1,
  which the prefixed autoloader enforces via a platform check, so the plugin would have
  been installable on 8.4.0 and then thrown from that check. The 7.x line needs PHP
  >= 8.2, so the gate now matches the declared 8.4.
