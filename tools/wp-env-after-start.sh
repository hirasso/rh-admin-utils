#!/usr/bin/env bash
set -e

# Each `wp-env run` is a separate docker exec round trip, so batch the commands
# per environment instead of invoking wp-cli once per command.
setup="wp theme activate twentytwentyfive \
  && wp rewrite structure '/%postname%/' --hard \
  && wp plugin activate --all"

# TEMPORARY: timestamp markers to measure afterStart. Revert after measuring.
mark() { echo "[afterStart] $(date -u +%H:%M:%S) $*"; }

mark "begin (CI=${CI:-unset})"

# CI only ever talks to the tests environment, so don't set up the development one there.
if [[ -z "$CI" ]]; then
  mark "development: start"
  pnpm run env-cli sh -c "$setup"
  mark "development: done"
else
  mark "development: SKIPPED (CI)"
fi

mark "tests: start"
pnpm run env-tests-cli sh -c "$setup"
mark "tests: done"
