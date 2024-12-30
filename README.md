# RH Admin Utils

A utility plugin I use on all my WordPress websites ⚙️

> [!IMPORTANT]
> This plugin is provided without public support. No issues, no discussions, no accepted PRs.
> You can browse the source code if you wish and fork it if you find something useful.

[**🔌 Installation**](./INSTALLATION.md) &nbsp;&nbsp;・&nbsp;&nbsp; [**📚 Changelog**](./CHANGELOG.md)

### Things this plugin does (I know, too many 😅)

- Adds a publish/save button to the admin bar
- Removes plugin ads (Looking at you, Yoast SEO...)
- Allows users with role "Editor" to add new users (highest role: `editor`)
- Adds certain page resitrictions (slug, hierarchy, page template, ...)
- Adds an environment switcher
- Adds several ACF field enhancements (browse the source of the `scr/ACF...` classes)
- Adds a robust embed cache
- Disables comments
- Adds a new role "Editor in chief" that can update the WP core and plugins
- Redirects uppercase URLs to lowercase on the frontend
- Adds a badge with a count to the admin menu for pending reviews
- Adds a download button to TinyMCE (classic editor)
- Adds a global button to the admin bar to clear the cache (WP Super Cache only)
- Adds a WP CLI command `wp rhau acf-sync-field-groups` to sync all ACF field groups