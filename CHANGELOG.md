# Changelog

## 3.0.23

### Patch Changes

- ff5b8b3: Automatically activate Polylang Pro if `RHAU_POLYLANG_PRO_LICENSE` is defined

## 3.0.22

### Patch Changes

- 25727d0: Fix `gu_set_options` argument and return types

## 3.0.21

### Patch Changes

- b6c8972: Make `Environments::is_internal_url()` more robust

## 3.0.20

### Patch Changes

- 40deb55: New ACF field setting to restrict field visibility to selected post types only

## 3.0.19

### Patch Changes

- 9a28259: Preserve synced ACF post dates when post_status is `draft`

## 3.0.18

### Patch Changes

- b5bee37: Do not scope the src/ folder at all anymore. Instead, copy it over to the scoped folder. This works for now as rh-admin-utils doesn't actually use any prefixed dependencies, yet (except for the exposed `dd` and `dump` functions)
- 3e3b307: Also copy toplevel php files over to the scoped folder

## 3.0.17

### Patch Changes

- 7452788: Also use the constant `RHAU_GITHUB_TOKEN` for afragen/git-updater, if available

## 3.0.16

### Patch Changes

- a13174c: Exclude more namespaces from scoping

## 3.0.15

### Patch Changes

- 3e91b61: Optimize scoped package

## 3.0.14

### Patch Changes

- c79007f: Check for empty constant `RHAU_REMOTE_ASSETS_ORIGIN`

## 3.0.13

### Patch Changes

- 5e11ec4: Move the link arrows out of the links in the readme.txt

## 3.0.12

### Patch Changes

- af1eeb8: Make the links in the plugin readme more beautifuller 💅💅

## 3.0.11

### Patch Changes

- aaf0c23: Make links bold in WordPress readme

## 3.0.10

### Patch Changes

- 42701d6: Add a readme.txt and a Plugin URI header

## 3.0.9

### Patch Changes

- bf51688: Remove the `Plugin Name` header from the helper plugin to hide it on the plugins overview screen

## 3.0.8

### Patch Changes

- 5b8f368: Automatically install a must-use helper plugin to hide the `_load_textdomain_just_in_time` warning in WP 6.7.1

## 3.0.7

### Patch Changes

- ec313cc: Fix patcher for plugin-update-checker

## 3.0.6

### Patch Changes

- 73fb7fb: Switch to `exclude-files` for excluding plugin-update-checker

## 3.0.5

### Patch Changes

- f3f9fcb: Prevent conflicts with Plugin Updater Checker and rh-updater

## 3.0.4

### Patch Changes

- Require `sniccowp/php-scoper-wordpress-excludes` as no-dev and exclude it from the scoped directory manually

## 3.0.3

### Patch Changes

- 048b7f7: Require php 8.2 and composer/installers in composer.dist.json

## 3.0.2

### Patch Changes

- c71e09e: Use `actions/checkout@v4` everywhere
- ba91f56: Clean up workflows

## 3.0.1

### Patch Changes

- a7b271b: Make package name and repo detection in workflow dynamic

## 3.0.0

### Major Changes

- afbef93: Prefix all constants with `RHAU_` to prevent conflicts:

  - `ENVIRONMENTS` is now `RHAU_ENVIRONMENTS`
  - `REMOTE_ASSET_MIRROR` is now `RHAU_REMOTE_ASSET_MIRROR`

### Minor Changes

- adfe522: Support setting `RHAU_GITHUB_TOKEN` to prevent being blocked by GitHub

### Patch Changes

- 8db6610: Add more docs
- 2b9ba06: Make `validateCWD()` less strict and rename it to `isAtRootDir()`

## 2.3.47

### Patch Changes

- 713f600: Make tooling more modular

## 2.3.46

### Patch Changes

- 7ad28d1: pushReleaseToDist: fix `success` import

## 2.3.45

### Patch Changes

- 1a0a188: trigger a release :))

## 2.3.44

### Patch Changes

- c4f64dd: Trigger a release to test tooling

## 2.3.43

### Patch Changes

- fc576e8: Clean-up binaries

## 2.3.42

### Patch Changes

- f978d34: Preserve the release body when uploading artifacts
- 44993de: Add assets.zip to release assets

## 2.3.41

### Patch Changes

- dcb4a23: Revert to using `HIRASSO_ACTIONS_TOKEN` for changesets
- 5ff5d76: Try using `${{ secrets.GITHUB_TOKEN }}` for changesets
- e08380c: Do not add composer.dist.json to archive

## 2.3.40

### Patch Changes

- f0cde16: trigger a release

## 2.3.39

### Patch Changes

- bc497c6: Try it again with the latest tag

## 2.3.38

### Patch Changes

- cdff06e: Try ncipollo/release-action@v1 for adding artifacts to the release

## 2.3.37

### Patch Changes

- 7c6a018: try to use softprops/action-gh-release@v2 for the release

## 2.3.36

### Patch Changes

- 71bd101: Fix the release asset_path name

## 2.3.35

### Patch Changes

- Fix the name of the release asset

## 2.3.34

### Patch Changes

- 2ebc659: Create a scoped release asset only for consumption by `yahnis-elsts/plugin-update-checker`

## 2.3.33

### Patch Changes

- edf5274: trigger a release

## 2.3.32

### Patch Changes

- 6ca5222: Append rh-admin-utils.zip to releases

## 2.3.31

### Patch Changes

- 2e99bc9: Move the githooks setup to the `prepare` hook
- 0c8471d: Prevent duplicate scoping of dependencies

## 2.3.30

### Patch Changes

- d03b5f3: Fail faster in workflows

## 2.3.29

### Patch Changes

- ab95a49: Do not assume no-dev folders exist

## 2.3.28

### Patch Changes

- afbfeb7: Always prefix namespaces

## 2.3.27

### Patch Changes

- 2c74ccf: Only create vendor-prefixed in releases

## 2.3.26

### Patch Changes

- 3007467: Prefix namespaces using php-scoper

## 2.3.25

### Patch Changes

- 105d73b: Add `composer/installers` and php 8.2 dependency

## 2.3.24

### Patch Changes

- 5a51982: Various improvements to the dist workflow

## 2.3.23

### Patch Changes

- 034b829: use `HIRASSO_ACTIONS_TOKEN` for dist repo

## 2.3.22

### Patch Changes

- 3c49499: add permissions inside the release job

## 2.3.21

### Patch Changes

- d727d93: Update the token for the workflow

## 2.3.20

### Patch Changes

- 3f4dfc8: try again

## 2.3.19

### Patch Changes

- 55d2425: try again with global git config

## 2.3.18

### Patch Changes

- 157f851: Configure git for pushing to dist repo

## 2.3.17

### Patch Changes

- be28d74: Trigger push to dist repo, again

## 2.3.16

### Patch Changes

- 0427434: Expand on the version-or-publish workflow to release a bundle to the dist repo

## 2.3.15

### Patch Changes

- a1d1910: Clean up of various things

## 2.3.14

### Patch Changes

- a93d1db: Add `composer/installers` as a dependency to allow for installation as `wordpress-plugin`

## 2.3.13

### Patch Changes

- bb519cf: Re-add the composer.json to the release zip

## 2.3.12

### Patch Changes

- 8e384c2: allow version and tag even though private

## 2.3.11

### Patch Changes

- e199ab3: Move publish action to npm script

## 2.3.10

### Patch Changes

- 295f2a8: Use `pnpm changeset tag` in publish action

## 2.3.9

### Patch Changes

- 5842fde: Remove the `.github` folder from release assets

## 2.3.8

### Patch Changes

- 7d1d4a2: Prefix dependencies
- 7d1d4a2: Add automated version and publish workflow

## 2.3.7

### Patch Changes

- Exclude the current Environment from the environment links

## 2.3.6

### Patch Changes

- Fix loading remote images in `calculate_image_srcset`

## 2.3.5

### Patch Changes

- Switch to changesets for CHANGELOG generation

## 2.3.4 (2024-11-08)

- Update package.json version (#0b2b8de)
- Change case of changelog (#cbe0824)
- Untrack changelog (#96f9b73)
- Optimize changelog generator (#1cdb26f)
- Add a description for packagist (#29518a1)
- v2.3.4 (#c41bdc5)

## 2.3.3 (2024-11-08)

- Optimize `disallow_indexing_notice` (#8e9f599)
- Read GITHUB_ACCESS_TOKEN from constant instead of using a hard-coded one. (#ac876c5)

## 2.3.2 (2024-10-28)

- Remove afragen/git-updater (#ca3cdcf)
- Hide more plugin adverts (#23438ad)

## 2.3.1 (2024-10-28)

- Add GitHub Plugin URI (#60844cf)
- Add readme (#221d19e)
- Change package name to hirasso/rh-admin-utils (#1e95e6b)
- Add `"minimum-stability": "dev"` to composer.json (#55acbdd)
- Make environment links work even with only one environment (#fdd57cd)

## 2.3.0 (2024-10-11)

- Hide the update nag where applicable (#f6b87d4)

## 2.2.9 (2024-09-04)

- v 2.2.9 (#cf6a66b)

## 2.2.8 (2024-09-04)

- Update environment links to use a native `dialog` (#ecd7d48)
- Fix the position of toolbar publish & save buttons (#20f9bd3)

## 2.2.7 (2024-08-02)

- Add license information (#5b670f5)
- Switch to pnpm, optimize watch and build processes (#f1048ef)
- Require PHP 8.2 (#0633a3e)

## 2.2.6 (2024-07-31)

- Optimize qtranslate fixes (#ad16e8d)

## 2.2.5 (2024-07-26)

- Revert hiding the privacy settings from editors (#f24948a)
- Never display the admin privacy settings menu item (#418e67c)

## 2.2.4 (2024-07-23)

- Remove custom page row actions added by Simple Page Ordering (#513695d)
- Support installation as must-use plugin (#fb10eba)

## 2.2.3 (2024-07-09)

- Make sure the `window.acf` is being used (#6fa074d)

## 2.2.2 (2024-07-08)

- Optimize `handleACFWysiwygField` (#72adb99)
- Initialize delayed ACF WYSIWYG fields as soon as they enter the viewport (#f3d2c69)

## 2.2.1 (2024-07-07)

- Optimize order of environment switcher (#d7b3773)

## 2.2.0 (2024-07-03)

- Make toggleButtonState more robust (#50c5326)
- Only allow adding links to files to selected plaintext (#a2a3fa5)
- Render a button in every TinyMCE editor that allows to link selected text to a file (#f49cb8f)

## 2.1.8 (2024-06-11)

- v2.1.8 (#7149b9a)

## 2.1.7 (2024-06-11)

- Fall back to WP_HOME for $local_origin (#631c8c1)
- Only init page restrictions if in admin (#c60964f)
- Don't assume the function `get_current_screen` exists (#a93c610)

## 2.1.6 (2024-05-23)

- Fix return type of `prepare_field` (#13c3865)

## 2.1.5 (2024-05-23)

- Respect elements with `[tabindex]` when pressing space to open the environment switch (#251e2ba)

## 2.1.4 (2024-05-14)

- v 2.1.4 (#083038a)

## 2.1.3 (2024-05-14)

- Allow multiple different roles in `ACFRestrictFieldAccess` (#4dd9389)
- v2.1.3 (#f0419f0)

## 2.1.2 (2024-05-08)

- ACFSyncPostDate: force last second of day if date_picker (#a6e4014)
- Make sure sg-security's cronjob is being scheduled (#b0bda0b)

## 2.1.1 (2024-04-15)

- v2.1.1 (#0e77865)

## 2.1.0 (2024-04-15)

- Optimize filter prio for `document_title_parts` (#ec91f41)
- Handle missing "production" env in `maybe_get_remote_url` (#4524699)
- Use emojis for environment hints in document title (#5375f73)
- v2.1.0 (#69b5410)

## 2.0.9 (2024-04-04)

- Update browserslist, build assets (#3cbe808)
- Support for `REMOTE_ASSETS_ORIGIN` (#e9e99f1)
- Adjust the environments module to Bedrock-Like setups (#f376b6d)
- New helper class: `ACFOembedWhitelist` (#85741d3)

## 2.0.8 (2024-02-29)

- Don't allow ACF custom fields to be attached to the cache post type (#31b4742)
- Various optimizations (#20e9575)
- Update documentation (#2dbac72)
- Update Explanation for the global ACFOembedCache helper post (#68fd898)
- Add a global cache for ACF oEmbed fields used outside of the loop (#1d65ee8)
- Prevent accidental deletion of locked posts for _everyone_ (#23fa2d3)
- Make `ACFOembedCache` more robust for iframe embeds (#76f1cff)

## 2.0.7 (2024-02-26)

- Redirect the dashboard to a (customizable) other admin url (#395db65)

## 2.0.6 (2024-02-26)

- Restrict changing the post type to administrators (#0e86051)
- Make sure restricted page fields can't be changed programmatically (#f5a404d)
- Don't assume `get_current_screen` is defined (#5561f0a)
- Visually hide restricted page settings in block editor view (#b75acb6)
- Clean-up permission checks (#1068e48)
- Add better tooltips for page restrictions (#cf1856f)
- Fix saving page restrictions (#37d7b40)
- Remove over-engineered query for posts without children allowed (#8c85391)
- Simplify Page permissions (#106c048)
- Only format files inside lib/rh-admin-utils (#f486682)
- Set `$wp_embed->post_ID` when caching ACF oEmbed fields (#1bee29d)
- Format using PHP_CodeSniffer (#7a737ed)
- Cache ACF oEmbed fields (#1464d97)

## 2.0.5 (2024-02-16)

- Remove the ACF dependency for the global page restrictions options page (#5d56ad3)
- Add (non-functioning) vanilla options page (#55ab126)
- Version 2.0.5 (#f347234)

## 2.0.4 (2024-02-16)

- Make sure `is_post_status_restricted` returns a `boolean` (#dd3be70)
- Render more info about locked pages, allow to lock the post status (#22a0861)
- Render a hint for administrators that a template is locked (#8e40c4c)
- Rename `PagePermissions` to `PageRestrictions` (#666f6f3)
- New Page Permission: Lock parent (#f34f316)
- Leave `post_date_modified` untouched when syncing date fields (#c3439e9)
- Add option to sync ACF `date_picker` and `date_time_picker` fields with the `post_date` (#22e0de7)
- Optimize WP queries if `__rhau_optimize_query` is set as a query arg (#3b53259)
- Cache `get_pages_with_no_children_allowed` (#c36b0d0)
- Add PHP Requirement to plugin headers (#b33c2dc)
- Use `get_current_screen` for page templates restriction (#665cf02)
- Optimizations for the PagePermissions module (#f0a5077)
- Simplify `render_protected_page_template` (#ffc8244)
- New module: Page Permissions (#132db4d)

## 2.0.3 (2024-02-14)

- Make sure comments and trackbacks are always disallowed (#1c7b3ee)

## 2.0.2 (2024-02-13)

- More filters to disable comments (#6f5c256)

## 2.0.1 (2024-02-09)

- Restrict access to ACF fields by role (#81462da)

## 2.0.0 (2024-01-30)

- V2.0.0 (#a52146a)

## 1.2.0 (2024-01-30)

- V1.2.0 (#6fa6ee2)
- cast document title to string (#c7ae4d0)

## 1.9.7 (2024-01-23)

- Fix version number (#dc3fe05)

## 1.6.0 (2024-01-23)

- Remove automatic ACF PRO activation (#0b935ff)
- Only send pending notifications each 5 minutes (#7d04c22)
- Utilities for ACF password field: reveal, generate & copy (#0a95ad5)

## 1.9.5 (2024-01-18)

- Remove client-side redirect (#6a6e430)
- Add PHP header (#d7506c9)
- Fix usage example (#0baba5d)
- Make filters more granular: `rhau/force_lowercase_urls`, (#5e05aca)
- Force lowercase URLs when serving a cached file (JavaScript) (#f0a106f)
- Add Filter `rhau/force_lowercase_urls` (#04dda67)
- Add hook to force lowercase URLs in the frontend (#38894f6)

## 1.9.4 (2023-11-07)

- Ensure compatibilty with paged.js `counter(pages)` (#189c912)

## 1.9.3 (2023-10-07)

- npm update (#7551cf2)
- Refactor ACFSyncFieldGroups (#a519599)

## 1.9.2 (2023-06-13)

- Repeat `initQtranslateSwitcher` three times (#feab125)
- Fix for qTranslate on ACF options pages (#02ddacc)

## 1.9.1 (2023-05-30)

- Compatibility with ACF `6.1` (#1ac6ed5)

## 1.9.0 (2023-04-12)

- Disable Siteground Security logs (#d752608)

## 1.8.9 (2023-03-29)

- Environment links: Optimize focus trapping (#400ade8)

## 1.8.8 (2023-03-29)

- Only stop propagation of clicks if environment links are open (#d865d0a)

## 1.8.7 (2023-03-29)

- v1.8.7 (#aaf72fa)

## 1.8.6 (2023-03-29)

- Refactor Environment Links UI (#9672227)
- Fix changelog generator (commonjs in `type=modue`) (#6aaf6c5)

## 1.8.5 (2023-03-20)

- Also allow searching for `id:12345` on list view of `upload.php` (#f869981)

## 1.8.4 (2023-03-20)

- Allow searching for attachments IDs using `id:12345` (#f35cf83)

## 1.8.3 (2023-02-21)

- v1.8.3 (#04a768e)

## 1.8.2 (2023-02-21)

- Migrate from Parcel to WebPack (#2b0cc3a)
- Add Alpine.js mask support for ACF text fields (#1225f3a)

## 1.8.1 (2023-02-21)

- Allow Line Wrapping for Code Field (#a3235b9)

## 1.8.0 (2023-02-20)

- Optimize `ACFRelationshipField` for usage in repeater/flexible content fields (#8814a51)
- Optimize `ACFCodeField` for usage in repeater/flexible content fields (#a2a38b1)

## 1.7.9 (2023-02-20)

- Add enhancements for ACF relationship field (#1f90d66)

## 1.7.8 (2023-02-17)

- Only render the ACF field setting `Code Field` for fields of type `textarea` (#8a573ba)

## 1.7.7 (2023-02-16)

- Remove `WpscHtaccessHelper`, since it's now all possible with WP Super Cache itself (#ad12041)

## 1.7.6 (2023-02-08)

- New ACF field setting to render textareas as code editors (#172fedf)

## 1.7.5 (2023-01-26)

- Version 1.7.5 (#db87e40)

## 1.7.4 (2023-01-26)

- Optimize `wp_cli_clear_cache` (#3173065)
- Add command `wp rhau wpsc-clear-cache` (#c23eacc)

## 1.7.3 (2023-01-20)

- Add WP CLI command `rhau do-action-save-post` (#f29fc0f)

## 1.7.2 (2023-01-20)

- Make `is_wp_cli()` available throughout the plugin (#69618f7)
- Add plugin headers (#e267b86)
- New wp cli command: `wp rhau acf-sync-field-groups` (#83b66f4)

## 1.7.0 (2022-12-15)

- Add filters for WP Super Caches `Cache-Control` and (#9047233)
- New Class `WpscHtaccessHelper` (#8eb77a8)

## 1.6.9 (2022-12-01)

- Add lib/vendor to repo (#ddf55b1)
- Apply formatting according to PSR-2 (#954b696)
- Refactor main class access (#d531c60)
- Fix `asset_uri` with new folder structure (#115b46e)
- Introduce PSR-4 autoloading using Composer (#91cc238)
- Remove `Limit Login Attempts Reloaded` tweaks. The plugin should NOT BE USED ☠️ (#afdd8ed)

## 1.6.8 (2022-11-30)

- Hide top-level menu item from `Limit Login Attempts Reloaded` (#f276e74)
- Optimize `Limit Login Attempts Reloaded` ads prevention (#2a37f42)

## 1.6.6 (2022-11-30)

- Prevent admin ads from Limit Login Attempts Reloaded (#c4ef87e)
- Add `search-and-replace` to deprecated plugins (#e8494f2)
- Remove unnecessary dashboard widgets (#81ab211)

## 1.6.5 (2022-10-25)

- Check user role before rendering pending badges (#3f7e487)

## 1.6.4 (2022-10-25)

- Render `pending` badges in the admin menu for all post types that support `rhau-pending-badge` (#504bab1)

## 1.6.3 (2022-10-25)

- Optimize Pending Review Emails (check for user, add filters) (#c899670)

## 1.6.2 (2022-09-21)

- bump version (#fabf6e1)

## 1.6.1 (2022-09-21)

- Get home url directly (#f1135aa)
- Remove plugin header from class.environments (#4dbadad)
- Fix conflict with WP Super Cache delete cache button (#6008c8f)

## 1.5.1 (2022-06-30)

- Optimizations for `edit-user.php` (#ba186aa)

## 1.5.0 (2022-06-29)

- Add email notifications for pending reviews (#3ace8b4)

## 1.4.5 (2022-04-05)

- change prio for wp_calculate_image_srcset to `11` (#5256e1a)
- don't rewrite attachment urls for external attachment urls (#b0a1cc6)

## 1.4.4 (2022-03-01)

- Actually disable `https_ssl_verify` in `development`, not `staging` (#6042abb)
- Disable `https_ssl_verify` in `dev` environment (#960067f)

## 1.4.3 (2022-01-27)

- Fix wp_robots_no_robots for staging environments (#7735e6f)

## 1.4.2 (2022-01-25)

- V 1.4.2 (#b019719)

## 1.4.1 (2022-01-25)

- Clear WP Super Cache when updating ACF options pages (#b8fa670)
- Removes 'comments' support for all post types. Completely disable xmlrpc (#05130ce)

## 1.4.0 (2022-01-23)

- Add more hooks to completely disable WP comments (#51ffbcd)

## 1.3.9 (2022-01-23)

- Add class to disable comments (#0546672)

## 1.3.8 (2022-01-16)

- v 1.3.8 (#4009b8e)

## 1.3.7 (2021-12-20)

- Respect WordPress color profiles (#0093512)

## 1.3.6 (2021-11-24)

- V 1.3.6 (#7ce9d85)

## 1.3.5 (2021-11-24)

- PHP8 Compatibility (#4bd1f29)

## 1.3.4 (2021-11-19)

- Skip `activate_acf_pro_license` for ACF >= 5.11.0 (#b35b601)

## 1.3.3 (2021-10-04)

- qTranslate Switcher on ACF options pages (#eda535d)

## 1.3.2 (2021-08-27)

- optimize qTranslate admin language switchers (#a3c9c13)
- V 1.3.2 (#ba58e37)

## 1.3.1 (2021-08-03)

- add boolean filter `rhau/redirect_edit_php` (#eb3de76)
- Update GitHub Updater Token (#411290f)

## 1.3.0 (2021-07-13)

- Late init for `reopenSavedAcfFieldObjects` (#c0e35df)

## 1.2.9 (2021-07-08)

- Late init for edit columns (#e7b80fa)
- Fix z-index for language switch (#60a60dc)
- Hide `Languages` from edit columns (#51385f7)
- Change cap to to manage the privacy page (#e1db0bb)
- Compatibility with Git Updater 10.3.4 (#50054d1)

## 1.2.8 (2021-05-26)

- Multisite compat (#012dd1e)

## 1.2.7 (2021-05-20)

- More sensible styles for hidden ACF fields (#0442114)

## 1.2.6 (2021-04-26)

- Allow Editors to edit the privacy page (#9cc96e5)

## 1.2.5 (2021-04-19)

- Late initialization for `map_meta_cap` (#851ef10)

## 1.2.4 (2021-04-15)

- cleanup (#e89b99b)
- Stay logged in in dev environments (#acad660)

## 1.2.3 (2021-04-14)

- Disallow "editor_in_chief" in editable roles (#2525ec8)
- Add Role "Editor in Chief" (#12b313a)

## 1.2.2 (2021-04-13)

- Do not allow editors to add editos with update capabilities (#63a9e6f)
- Optimize Admin Menu (#4e18b9f)
- Cleanup (#b2c9ede)

## 1.2.1 (2021-04-13)

- Add new Role "Editor (+ Run Updates)" (#4ddeddd)
- Remove some nodes from WP_Admin_Bar (#28a832d)
- Disable cap `customize` for all users (#398e2ff)

## 1.1.9 (2021-02-23)

- Limit `WP_POST_REVISIONS` to 3 (#d07aef1)
- Deactivate deprecated plugins before deleting them (#f052ae6)
- Include environments module (#286bbf7)

## 1.1.8 (2021-02-04)

- Redirect default `edit.php` screen to (#d43635c)
- optimizations for acf-autosize and qtranslate (#d697451)
- fix WYSIWYG autosize issues with qTranslate (#ccabc16)

## 1.1.6 (2021-01-25)

- add qtranslate admin styles (#1ce43a1)
- Check for function `acf_pro_get_license_key` (#929b15d)

## 1.1.5 (2020-11-09)

- Add function for automatically activating (#4c0e1fc)

## 1.1.4 (2020-11-04)

- ACF 5.9.3 compatibility (#1b62b25)

## 1.1.3 (2020-11-03)

- Minor Fixes (#6a473c5)

## 1.1.2 (2020-11-02)

- – Fix Update-Button on ACF validation errors (#6b77ec0)

## 1.1.1 (2020-07-22)

- Hide Email Address Encoder's admin notices (#f25cc1e)

## 1.1.0 (2020-07-21)

- Merge all admin util classes (#dec7612)

## 1.0.9 (2020-07-20)

- Add WP Super Cache Admin Bar Item (#d0fead6)

## 1.0.8 (2020-07-17)

- Remove rh-updater dependency (#8d1b391)

## 1.0.7 (2020-07-14)

- fix updater connector (#2a3603b)
- add connector class (#bb6f509)

## 1.0.6 (2020-07-14)

- fix notice for missing updater (#59575a1)

## 1.0.5 (2020-07-10)

- Make show_notice_missing_rh_updater public (#77b78a1)

## 1.0.4 (2020-06-25)

- Remove YOAST SEO ads (#9f39e77)

## 1.0.3 (2020-06-04)

- Also show update button for acf options pages (#70de851)

## 1.0.2 (2020-06-01)

- fix undefined object 'acf' on some admin pages (#e590043)

## 1.0.1 (2020-05-29)

- Support for acf-field-groups (#546cfd2)
- hide buttons before load (#c7a1814)
- only show admin bar buttons if pendant is visible (#8638d96)

## 1.0.0 (2020-05-29)

- Add changelog (#6f71f3c)
- Add admin optimizations (#729c098)
- Intial commit (#3c570be)
