---
"rh-admin-utils": patch
---

Define `RHAU_TRUSTED_ADMINS` or filter `rhau/trusted_admins` to restrict install/delete/edit-file/unfiltered_html/switch-user capabilities to an allowlist of trusted admins. Inactive unless a site defines the constant or hooks the filter. Trusted admins can only be deleted by other trusted admins, never by a non-trusted administrator.
