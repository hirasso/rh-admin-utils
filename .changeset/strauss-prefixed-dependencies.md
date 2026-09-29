---
"rh-admin-utils": minor
---

Install identically via composer or as a plugin zip

The plugin had two shapes. A composer install pulled an unprefixed
`symfony/var-dumper` into the consuming site's vendor folder, while the release zip
shipped a php-scoper build with its own prefixed copy. Updating through the WP admin on
a composer-installed site therefore left that dependency behind, unused.

Dependencies are now prefixed by [Strauss](https://github.com/BrianHenryIE/strauss)
into `vendor-prefixed/`, which is committed so that it ships in the tag's archive as
well as the zip. Nothing that gets prefixed remains in `require`, so composer installs
nothing on the plugin's behalf.

Global functions are left unprefixed, so `dump()` and `dd()` keep working site-wide.
They are only declared when nothing else has already defined them.
