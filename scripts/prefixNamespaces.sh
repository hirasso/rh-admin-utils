#!/bin/bash

# Scopes no-dev composer dependencies to avoid version conflicts in WordPress

# download php-scoper.phar
test -f bin/php-scoper.phar || curl -sLo bin/php-scoper.phar https://github.com/humbug/php-scoper/releases/latest/download/php-scoper.phar

# create a temporary folder and install no-dev composer dependencies
rm -rf tmp && mkdir tmp && cp composer.json tmp/composer.json

# require WordPress excludes
composer require sniccowp/php-scoper-wordpress-excludes --no-scripts --working-dir=tmp

# copy wordpress excludes to the root of tmp/
cp -Rf tmp/vendor/sniccowp/php-scoper-wordpress-excludes tmp/

# remove WordPress excludes
composer remove sniccowp/php-scoper-wordpress-excludes --no-scripts --working-dir=tmp

# Only install no-dev dependencies
composer install --no-dev --no-scripts --working-dir=tmp

# scope the vendor dir
rm -rf scoped && php bin/php-scoper.phar add-prefix tmp --output-dir=scoped --config=scripts/scoper.config.php

# dump the autoloader in the scoped dir
composer dump-autoload --working-dir scoped --classmap-authoritative

# move the scoped vendor folder to ./vendor-prefixed and clean up
rm -rf vendor-prefixed && cp -Rf scoped/vendor vendor-prefixed

# clean up
rm -rf tmp scoped