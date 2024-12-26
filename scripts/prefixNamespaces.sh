#!/bin/bash

# Scopes no-dev composer dependencies to avoid version conflicts in WordPress

# download php-scoper.phar
test -f bin/php-scoper.phar || curl -sLo bin/php-scoper.phar https://github.com/humbug/php-scoper/releases/latest/download/php-scoper.phar

# copy the composer.json to a temporary folder for scoping
rm -rf scopeme && mkdir scopeme && cp composer.json scopeme/composer.json

# require WordPress excludes
composer require sniccowp/php-scoper-wordpress-excludes --no-scripts --working-dir=scopeme

# copy wordpress excludes to the root of scopeme/
cp -Rf scopeme/vendor/sniccowp/php-scoper-wordpress-excludes scopeme/

# remove WordPress excludes
composer remove sniccowp/php-scoper-wordpress-excludes --no-scripts --working-dir=scopeme

# Install composer in the scopeme directory
if [ "$GITHUB_ACTIONS" = "true" ]; then
  composer install --no-dev --no-scripts --working-dir=scopeme
else
  composer install --no-scripts --working-dir=scopeme
fi

# scope the vendor dir
rm -rf scoped && php bin/php-scoper.phar add-prefix scopeme --output-dir=scoped --config=scripts/scoper.config.php

# replace the vendor folder with the scoped one
rm -rf vendor && cp -Rf scoped/vendor vendor

# dump the autoloader
if [ "$GITHUB_ACTIONS" = "true" ]; then
  composer dump-autoload --classmap-authoritative
else
  composer dump-autoload
fi

# clean up
rm -rf scopeme scoped