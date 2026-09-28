---
"rh-admin-utils": patch
---

Bundle plugin-update-checker in `lib/` instead of requiring it via Composer

It was a production dependency, so a Composer install pulled it into the consuming
project's `vendor/` folder, constraining the whole site to `^5.6` and loading the library
on every request. It is now vendored into the plugin and loaded on demand.
