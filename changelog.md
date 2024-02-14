#### 2.0.4 (2024-02-14)

- Add PHP Requirement to plugin headers (#a9d8315)
- Use `get_current_screen` for page templates restriction (#665cf02)
- Optimizations for the PagePermissions module (#f0a5077)
- Simplify `render_protected_page_template` (#ffc8244)
- New module: Page Permissions (#132db4d)

#### 2.0.3 (2024-02-14)

- Make sure comments and trackbacks are always disallowed (#1c7b3ee)

#### 2.0.2 (2024-02-13)

- More filters to disable comments (#6f5c256)

#### 2.0.1 (2024-02-09)

- Restrict access to ACF fields by role (#81462da)

#### 2.0.0 (2024-01-30)

- V2.0.0 (#a52146a)

#### 1.2.0 (2024-01-30)

- V1.2.0 (#6fa6ee2)
- cast document title to string (#c7ae4d0)

#### 1.9.7 (2024-01-23)

- Fix version number (#dc3fe05)

#### 1.6.0 (2024-01-23)

- Remove automatic ACF PRO activation (#0b935ff)
- Only send pending notifications each 5 minutes (#7d04c22)
- Utilities for ACF password field: reveal, generate & copy (#0a95ad5)

#### 1.9.5 (2024-01-18)

- Remove client-side redirect (#6a6e430)
- Add PHP header (#d7506c9)
- Fix usage example (#0baba5d)
- Make filters more granular: `rhau/force_lowercase_urls`, (#5e05aca)
- Force lowercase URLs when serving a cached file (JavaScript) (#f0a106f)
- Add Filter `rhau/force_lowercase_urls` (#04dda67)
- Add hook to force lowercase URLs in the frontend (#38894f6)

#### 1.9.4 (2023-11-07)

- Ensure compatibilty with paged.js `counter(pages)` (#189c912)

#### 1.9.3 (2023-10-07)

- npm update (#7551cf2)
- Refactor ACFSyncFieldGroups (#a519599)

#### 1.9.2 (2023-06-13)

- Repeat `initQtranslateSwitcher` three times (#feab125)
- Fix for qTranslate on ACF options pages (#02ddacc)

#### 1.9.1 (2023-05-30)

- Compatibility with ACF `6.1` (#1ac6ed5)

#### 1.9.0 (2023-04-12)

- Disable Siteground Security logs (#d752608)

#### 1.8.9 (2023-03-29)

- Environment links: Optimize focus trapping (#400ade8)

#### 1.8.8 (2023-03-29)

- Only stop propagation of clicks if environment links are open (#d865d0a)

#### 1.8.7 (2023-03-29)

- v1.8.7 (#aaf72fa)

#### 1.8.6 (2023-03-29)

- Refactor Environment Links UI (#9672227)
- Fix changelog generator (commonjs in `type=modue`) (#6aaf6c5)

#### 1.8.5 (2023-03-20)

- Also allow searching for `id:12345` on list view of `upload.php` (#f869981)

#### 1.8.4 (2023-03-20)

- Allow searching for attachments IDs using `id:12345` (#f35cf83)

#### 1.8.3 (2023-02-21)

- v1.8.3 (#04a768e)

#### 1.8.2 (2023-02-21)

- Migrate from Parcel to WebPack (#2b0cc3a)
- Add Alpine.js mask support for ACF text fields (#1225f3a)

#### 1.8.1 (2023-02-21)

- Allow Line Wrapping for Code Field (#a3235b9)

#### 1.8.0 (2023-02-20)

- Optimize `ACFRelationshipField` for usage in repeater/flexible content fields (#8814a51)
- Optimize `ACFCodeField` for usage in repeater/flexible content fields (#a2a38b1)

#### 1.7.9 (2023-02-20)

- Add enhancements for ACF relationship field (#1f90d66)

#### 1.7.8 (2023-02-17)

- Only render the ACF field setting `Code Field` for fields of type `textarea` (#8a573ba)

#### 1.7.7 (2023-02-16)

- Remove `WpscHtaccessHelper`, since it's now all possible with WP Super Cache itself (#ad12041)

#### 1.7.6 (2023-02-08)

- New ACF field setting to render textareas as code editors (#172fedf)

#### 1.7.5 (2023-01-26)

- Version 1.7.5 (#db87e40)

#### 1.7.4 (2023-01-26)

- Optimize `wp_cli_clear_cache` (#3173065)
- Add command `wp rhau wpsc-clear-cache` (#c23eacc)

#### 1.7.3 (2023-01-20)

- Add WP CLI command `rhau do-action-save-post` (#f29fc0f)

#### 1.7.2 (2023-01-20)

- Make `is_wp_cli()` available throughout the plugin (#69618f7)
- Add plugin headers (#e267b86)
- New wp cli command: `wp rhau acf-sync-field-groups` (#83b66f4)

#### 1.7.0 (2022-12-15)

- Add filters for WP Super Caches `Cache-Control` and (#9047233)
- New Class `WpscHtaccessHelper` (#8eb77a8)

#### 1.6.9 (2022-12-01)

- Add lib/vendor to repo (#ddf55b1)
- Apply formatting according to PSR-2 (#954b696)
- Refactor main class access (#d531c60)
- Fix `asset_uri` with new folder structure (#115b46e)
- Introduce PSR-4 autoloading using Composer (#91cc238)
- Remove `Limit Login Attempts Reloaded` tweaks. The plugin should NOT BE USED ☠️ (#afdd8ed)

#### 1.6.8 (2022-11-30)

- Hide top-level menu item from `Limit Login Attempts Reloaded` (#f276e74)
- Optimize `Limit Login Attempts Reloaded` ads prevention (#2a37f42)

#### 1.6.6 (2022-11-30)

- Prevent admin ads from Limit Login Attempts Reloaded (#c4ef87e)
- Add `search-and-replace` to deprecated plugins (#e8494f2)
- Remove unnecessary dashboard widgets (#81ab211)

#### 1.6.5 (2022-10-25)

- Check user role before rendering pending badges (#3f7e487)

#### 1.6.4 (2022-10-25)

- Render `pending` badges in the admin menu for all post types that support `rhau-pending-badge` (#504bab1)

#### 1.6.3 (2022-10-25)

- Optimize Pending Review Emails (check for user, add filters) (#c899670)

#### 1.6.2 (2022-09-21)

- bump version (#fabf6e1)

#### 1.6.1 (2022-09-21)

- Get home url directly (#f1135aa)
- Remove plugin header from class.environments (#4dbadad)
- Fix conflict with WP Super Cache delete cache button (#6008c8f)

#### 1.5.1 (2022-06-30)

- Optimizations for `edit-user.php` (#ba186aa)

#### 1.5.0 (2022-06-29)

- Add email notifications for pending reviews (#3ace8b4)

#### 1.4.5 (2022-04-05)

- change prio for wp_calculate_image_srcset to `11` (#5256e1a)
- don't rewrite attachment urls for external attachment urls (#b0a1cc6)

#### 1.4.4 (2022-03-01)

- Actually disable `https_ssl_verify` in `development`, not `staging` (#6042abb)
- Disable `https_ssl_verify` in `dev` environment (#960067f)

#### 1.4.3 (2022-01-27)

- Fix wp_robots_no_robots for staging environments (#7735e6f)

#### 1.4.2 (2022-01-25)

- V 1.4.2 (#b019719)

#### 1.4.1 (2022-01-25)

- Clear WP Super Cache when updating ACF options pages (#b8fa670)
- Removes 'comments' support for all post types. Completely disable xmlrpc (#05130ce)

#### 1.4.0 (2022-01-23)

- Add more hooks to completely disable WP comments (#51ffbcd)

#### 1.3.9 (2022-01-23)

- Add class to disable comments (#0546672)

#### 1.3.8 (2022-01-16)

- v 1.3.8 (#4009b8e)

#### 1.3.7 (2021-12-20)

- Respect WordPress color profiles (#0093512)

#### 1.3.6 (2021-11-24)

- V 1.3.6 (#7ce9d85)

#### 1.3.5 (2021-11-24)

- PHP8 Compatibility (#4bd1f29)

#### 1.3.4 (2021-11-19)

- Skip `activate_acf_pro_license` for ACF >= 5.11.0 (#b35b601)

#### 1.3.3 (2021-10-04)

- qTranslate Switcher on ACF options pages (#eda535d)

#### 1.3.2 (2021-08-27)

- optimize qTranslate admin language switchers (#a3c9c13)
- V 1.3.2 (#ba58e37)

#### 1.3.1 (2021-08-03)

- add boolean filter `rhau/redirect_edit_php` (#eb3de76)
- Update GitHub Updater Token (#411290f)

#### 1.3.0 (2021-07-13)

- Late init for `reopenSavedAcfFieldObjects` (#c0e35df)

#### 1.2.9 (2021-07-08)

- Late init for edit columns (#e7b80fa)
- Fix z-index for language switch (#60a60dc)
- Hide `Languages` from edit columns (#51385f7)
- Change cap to to manage the privacy page (#e1db0bb)
- Compatibility with Git Updater 10.3.4 (#50054d1)

#### 1.2.8 (2021-05-26)

- Multisite compat (#012dd1e)

#### 1.2.7 (2021-05-20)

- More sensible styles for hidden ACF fields (#0442114)

#### 1.2.6 (2021-04-26)

- Allow Editors to edit the privacy page (#9cc96e5)

#### 1.2.5 (2021-04-19)

- Late initialization for `map_meta_cap` (#851ef10)

#### 1.2.4 (2021-04-15)

- cleanup (#e89b99b)
- Stay logged in in dev environments (#acad660)

#### 1.2.3 (2021-04-14)

- Disallow "editor_in_chief" in editable roles (#2525ec8)
- Add Role "Editor in Chief" (#12b313a)

#### 1.2.2 (2021-04-13)

- Do not allow editors to add editos with update capabilities (#63a9e6f)
- Optimize Admin Menu (#4e18b9f)
- Cleanup (#b2c9ede)

#### 1.2.1 (2021-04-13)

- Add new Role "Editor (+ Run Updates)" (#4ddeddd)
- Remove some nodes from WP_Admin_Bar (#28a832d)
- Disable cap `customize` for all users (#398e2ff)

#### 1.1.9 (2021-02-23)

- Limit `WP_POST_REVISIONS` to 3 (#d07aef1)
- Deactivate deprecated plugins before deleting them (#f052ae6)
- Include environments module (#286bbf7)

#### 1.1.8 (2021-02-04)

- Redirect default `edit.php` screen to (#d43635c)
- optimizations for acf-autosize and qtranslate (#d697451)
- fix WYSIWYG autosize issues with qTranslate (#ccabc16)

#### 1.1.6 (2021-01-25)

- add qtranslate admin styles (#1ce43a1)
- Check for function `acf_pro_get_license_key` (#929b15d)

#### 1.1.5 (2020-11-09)

- Add function for automatically activating (#4c0e1fc)

#### 1.1.4 (2020-11-04)

- ACF 5.9.3 compatibility (#1b62b25)

#### 1.1.3 (2020-11-03)

- Minor Fixes (#6a473c5)

#### 1.1.2 (2020-11-02)

- – Fix Update-Button on ACF validation errors (#6b77ec0)

#### 1.1.1 (2020-07-22)

- Hide Email Address Encoder's admin notices (#f25cc1e)

#### 1.1.0 (2020-07-21)

- Merge all admin util classes (#dec7612)

#### 1.0.9 (2020-07-20)

- Add WP Super Cache Admin Bar Item (#d0fead6)

#### 1.0.8 (2020-07-17)

- Remove rh-updater dependency (#8d1b391)

#### 1.0.7 (2020-07-14)

- fix updater connector (#2a3603b)
- add connector class (#bb6f509)

#### 1.0.6 (2020-07-14)

- fix notice for missing updater (#59575a1)

#### 1.0.5 (2020-07-10)

- Make show_notice_missing_rh_updater public (#77b78a1)

#### 1.0.4 (2020-06-25)

- Remove YOAST SEO ads (#9f39e77)

#### 1.0.3 (2020-06-04)

- Also show update button for acf options pages (#70de851)

#### 1.0.2 (2020-06-01)

- fix undefined object 'acf' on some admin pages (#e590043)

#### 1.0.1 (2020-05-29)

- Support for acf-field-groups (#546cfd2)
- hide buttons before load (#c7a1814)
- only show admin bar buttons if pendant is visible (#8638d96)

#### 1.0.0 (2020-05-29)

- Add changelog (#6f71f3c)
- Add admin optimizations (#729c098)
- Intial commit (#3c570be)

