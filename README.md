# RH Admin Utils

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hirasso/rh-admin-utils.svg)](https://packagist.org/packages/hirasso/rh-admin-utils)
[![Test Status](https://img.shields.io/github/actions/workflow/status/hirasso/rh-admin-utils/ci.yml?label=tests)](https://github.com/hirasso/rh-admin-utils/actions/workflows/ci.yml)

**A WordPress utility plugin 🥞**

> [!IMPORTANT]
> This plugin is provided **without public support**. No issues, no discussions, no PRs accepted.
> You can browse the source code and pick and choose what you find useful for your projects.

## Docs

- [**🔌 Installation**](./INSTALLATION.md)
- [**📚 Changelog**](./CHANGELOG.md)

## Things this plugin does (I know, too many 🤷‍♂️)

- Adds a publish/save button to the admin bar
- Removes plugin ads (Looking at you, Yoast SEO...)
- Allows users with role "Editor" to add new users (highest role: `editor`)
- Adds opt-in page resitrictions that can only be changed by Administrators (slug, hierarchy, page template, ...)
- Adds an environment switcher
- Adds several ACF field enhancements (browse the source of the `scr/ACF...` classes)
- Adds a robust embed cache
- Disables comments
- Adds a new role "Editor in chief" that can update the WP core and plugins
- Redirects uppercase URLs to lowercase on the frontend
- Adds a badge with a count to the admin menu for pending reviews
- Adds a download button to TinyMCE (classic editor)
- if WP Super Cache is installed, adds a button to the admin bar to clear the whole cache
- Adds a WP CLI command `wp rhau test-imagick` to check which image formats Imagick can actually encode
- Adds a WP CLI command `wp rhau acf-sync-field-groups` to sync all ACF field groups
- Adds a WP CLI command `wp rhau simply-static run` to generate a static version of your site (requires the plugin simply-static to be installed)

## Other Features

- Can be installed via composer or manually
- Ships with an instance of [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) to support updates from wp-admin
- Does not rely on the WP.org plugin repository
- Installs identically via composer or as a plugin zip, so a site can use both

## Development

esbuild (`tools/build.mjs`), SCSS, [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) for local dev, [changesets](https://github.com/changesets/changesets) for releases. Local URL: `pnpm env status --json`.

```bash
pnpm env start              # start wp-env
pnpm watch                  # esbuild watch with live reload
pnpm build                  # production asset build
pnpm format                 # Prettier + PHP Pint
composer analyse            # PHPStan static analysis
pnpm env:test start         # start the wp-env test environment
pnpm test:pest              # Pest tests in the wp-env test environment
pnpm test:e2e               # Playwright e2e tests (`test:e2e:ui`, `test:e2e:install`)
pnpm test                   # Pest + fresh test env + e2e
pnpm wp …                   # WP-CLI inside wp-env
```
