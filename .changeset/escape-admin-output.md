---
"rh-admin-utils": patch
---

Escape dynamic values printed in the admin

Several admin sinks printed dynamic values unescaped: the stored admin notice
message and type, locked page attributes and the sample permalink (both built from
post titles and URLs), the oEmbed cache flush link, the environment links, the
protected-templates checkbox values and the "not allowed to delete" message.

Notice messages go through `wp_kses_post()` rather than `esc_html()`, since the
`rh/wpsc-cc/cache_deleted_notice` filter lets a theme put markup in one.

The ACF post-date field label is printed inside a CSS string in a `<style>` block,
where entity escaping renders literally instead of protecting anything. It now gets
the CSS string delimiters escaped and `</` broken up so the element can't be closed
early.
