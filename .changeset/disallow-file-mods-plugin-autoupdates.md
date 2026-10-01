---
"rh-admin-utils": patch
---

Keep plugin and theme auto-updates disabled if `DISALLOW_FILE_MODS` is `true`. The `automatic_updater` exception re-enabled them, even for items opted in via the database.
