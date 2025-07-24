---
"rh-admin-utils": patch
---

Use `acf/get_field_label` to render ACF image field instructions

That is necessary due to a change in ACF version 6.4.3 where HTML field labels is being escaped.
