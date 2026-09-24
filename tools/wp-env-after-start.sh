#!/usr/bin/env bash
set -e

# Each `wp-env run` is a separate docker exec round trip, so batch the commands
# per environment instead of invoking wp-cli once per command.
setup="wp theme activate twentytwentyfive \
  && wp rewrite structure '/%postname%/' --hard \
  && wp plugin activate --all"

# CI only ever talks to the tests environment, so don't set up the development one there.
if [[ -z "$CI" ]]; then
  pnpm run env-cli sh -c "$setup"
fi

pnpm run env-tests-cli sh -c "$setup"
