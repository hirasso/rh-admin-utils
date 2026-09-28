---
"rh-admin-utils": patch
---

Require a capability to apply the .htaccess hardening

Applying the directives was gated on a valid nonce alone. It now also checks
`edit_others_posts`. The notice no longer prints the directives it is about to
write; the fallback notice shown when the write fails still does, since its
purpose is to let them be applied by hand.
