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

# Only install no-dev dependencies
composer install --no-dev --no-scripts --working-dir=scopeme

# scope the vendor dir
rm -rf scoped && php bin/php-scoper.phar add-prefix scopeme --output-dir=scoped --config=scripts/scoper.config.php

# dump the autoloader in the scoped dir
composer dump-autoload --working-dir scoped --classmap-authoritative

# move the scoped vendor folder to ./vendor-prefixed and clean up
rm -rf vendor-prefixed && cp -Rf scoped/vendor vendor-prefixed

# clean up
rm -rf scopeme scoped