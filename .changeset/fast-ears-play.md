---
"rh-admin-utils": patch
---

Allow pretty permalinks for custom post stati (private, draft etc.). This adds support for [@swup/fragment-plugin](https://swup.js.org/plugins/fragment-plugin/) to preview URLs. Can also be used for future posts:

```php
/**
 * Allow pretty permalinks for posts with post status 'future':
 */
add_filters('rhau/pretty_permalinks/post_stati', function(array $post_stati, string $post_type): array {
  if ($post_type === 'event') {
    $post_stati[] = 'future';
  }
  return $post_stati;
}, 10, 2);
```
