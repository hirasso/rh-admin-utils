#!/usr/bin/env bash
set -e

pnpm run env:cli wp theme activate twentytwentyfive
pnpm run env:cli wp rewrite structure '/%postname%/' --hard
pnpm run env:cli wp plugin activate --all

pnpm run env:tests-cli wp theme activate twentytwentyfive
pnpm run env:tests-cli wp rewrite structure '/%postname%/' --hard
pnpm run env:tests-cli wp plugin activate --all