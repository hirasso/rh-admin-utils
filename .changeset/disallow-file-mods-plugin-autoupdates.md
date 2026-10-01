---
"rh-admin-utils": patch
---

Keep plugin auto-updates disabled if `DISALLOW_FILE_MODS` is `true`. The `automatic_updater` exception re-enabled them, even for plugins opted in via the database.
