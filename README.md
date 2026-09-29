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
- Adds a WP CLI command `wp rhau acf-sync-field-groups` to sync all ACF field groups
- Adds a WP CLI command `wp rhau simply-static run` to generate a static version of your site (requires the plugin simply-static to be installed)

## Other Features

- Ships with an instance of [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) to support updates directly from GitHub
- Does not rely on the WP.org plugin repository
- Installs identically via composer or as a plugin zip, so a site can use both (see [Dependencies](#dependencies))

## Dependencies

Runtime dependencies are prefixed into `vendor-prefixed/` by [Strauss](https://github.com/BrianHenryIE/strauss)
and committed to this repo. That directory is build output, but it has to live in the
git tag: composer serves the tag's archive, so committing it is what makes a composer
install and the release zip the same directory. It is regenerated with:

```shell
composer prefix
```

Two consequences worth knowing before you touch a dependency:

- **Nothing that gets prefixed may live in `require`.** Runtime dependencies belong in
  `require-dev` and are listed under `extra.strauss.packages`. Anything left in
  `require` would be installed _unprefixed_ into the vendor folder of every site that
  installs this plugin via composer. Keeping them in `require-dev` also means
  `composer audit` still reports CVEs in the code that ships.
- **Prefixing takes away globals the dependency used to declare.** var-dumper's
  `dump()` and `dd()` become `rhau_vendor_*`, so the plugin re-serves both — namespaced
  as `RH\AdminUtils\dump()`/`dd()`, and globally for themes and other plugins. The
  global pair is only declared if nothing else has, and can be turned off with
  `define('RHAU_GLOBAL_DEBUG_FUNCTIONS', false)` in wp-config.php. Any future dependency
  that ships global functions needs the same treatment.
- **`src/` references the prefixed namespaces directly** (`RH\AdminUtils\Vendor\...`),
  in development as well as in a release. There is no unprefixed variant of the source,
  so static analysis and your editor resolve what actually runs in production.

After changing a dependency, verify the committed output:

```shell
node config/cli/cli.js verify:prefixed
```

This regenerates `vendor-prefixed/`, fails if the result differs from what is committed,
and fails if any prefixed namespace survived unprefixed — the failure mode a static
rewriter can produce with dynamic class names. It also runs in CI and before a release.
