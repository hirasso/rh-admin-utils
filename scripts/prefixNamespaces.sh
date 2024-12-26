#!/bin/bash

# Scopes no-dev composer dependencies to avoid version conflicts in WordPress

# download php-scoper.phar
test -f bin/php-scoper.phar || curl -sLo bin/php-scoper.phar https://github.com/humbug/php-scoper/releases/latest/download/php-scoper.phar

# require WordPress excludes
composer require sniccowp/php-scoper-wordpress-excludes --no-scripts

# copy wordpress excludes to the top level
cp -Rf vendor/sniccowp/php-scoper-wordpress-excludes php-scoper-wordpress-excludes

# remove WordPress excludes
composer remove sniccowp/php-scoper-wordpress-excludes --no-scripts

# Install only no-dev dependencies when releasing
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