# lib

Third-party libraries bundled with this plugin, copied in verbatim.

They live here rather than in `require` so that a Composer install of this plugin doesn't
pull them into the consuming project's `vendor/` folder, where they would constrain the
whole site's dependency graph.

## plugin-update-checker

- Version: **5.7**
- Upstream: https://github.com/YahnisElsts/plugin-update-checker

Loaded on demand by `RH\AdminUtils\UpdateChecker::loadLibrary()`. It is excluded from
Pint, and copied into the release archive as-is by `tools/cli/support.js`.

To update, replace the folder with a fresh copy of the release and bump the version above:

```shell
rm -rf lib/plugin-update-checker
curl -sfL https://github.com/YahnisElsts/plugin-update-checker/archive/refs/tags/v5.7.tar.gz \
  | tar -xz -C lib && mv lib/plugin-update-checker-* lib/plugin-update-checker
```

The namespace is version-pinned (`v5p7`), so a minor bump also means updating the guard in
`src/UpdateChecker.php`.
