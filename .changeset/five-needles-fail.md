---
"rh-admin-utils": patch
---

Do not scope the src/ folder at all anymore. Instead, copy it over to the scoped folder. This works for now as rh-admin-utils doesn't actually use any prefixed dependencies, yet (except for the exposed `dd` and `dump` functions)
