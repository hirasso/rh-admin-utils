---
"rh-admin-utils": patch
---

Expand category divs in the `post.php` so that they don't have overflow. Behaviour can be disabled:

```php
add_filters('rhau/expand_category_divs', '__return_false');
```
