---
"rh-admin-utils": patch
---

Require a capability to clear the WP Super Cache cache

Clearing the cache was gated on a valid nonce alone. It now also checks
`edit_others_posts`, the capability its admin bar button already required.
