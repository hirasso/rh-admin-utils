#!/bin/bash

# Scopes no-dev composer dependencies to avoid version conflicts in WordPress

# Stop execution on errors
set -e

# Validate that we are at the project root
if [[ ! -f "$PWD/.gitignore" ]]; then
  echo "❌ "$(basename "$0")" must run from the package root"
  exit 1
fi

echo "💡 Prefixing namespaces in the vendors folder..."

# download php-scoper.phar
test -f bin/php-scoper.phar || curl -sLo bin/php-scoper.phar https://github.com/humbug/php-scoper/releases/latest/download/php-scoper.phar

# require WordPress excludes
composer require sniccowp/php-scoper-wordpress-excludes --no-scripts

# copy WordPress excludes to the top level
cp -Rf vendor/sniccowp/php-scoper-wordpress-excludes php-scoper-wordpress-excludes

# remove WordPress excludes
composer remove sniccowp/php-scoper-wordpress-excludes --no-scripts

# Install only no-dev dependencies in CI
if [ "$GITHUB_ACTIONS" = "true" ]; then
  composer install --no-dev --no-scripts
fi

# scope the vendor dir
rm -rf scoped && php bin/php-scoper.phar add-prefix --output-dir=scoped --config=scripts/scoper.config.php

# replace the vendor folder with the scoped one
rm -rf vendor && cp -Rf scoped/vendor vendor && rm -rf scoped

# dump the autoloader
if [ "$GITHUB_ACTIONS" = "true" ]; then
  composer dump-autoload --classmap-authoritative
else
  composer dump-autoload
fi

# cleanup
rm -rf php-scoper-wordpress-excludes