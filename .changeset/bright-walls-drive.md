---
"rh-admin-utils": minor
---

Disable twemoji / Use native emojis

If you want to keep using twemoji, you can do so using a filter:

```php
add_filter('rhau/native_emoji', '__return_false');
```