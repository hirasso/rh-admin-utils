#!/bin/bash

# Scopes no-dev composer dependencies to avoid version conflicts in WordPress

# Stop execution on errors
set -e

# Validate that we are at the project root
if [[ ! -f "$PWD/.gitignore" ]]; then
  echo "❌ "$(basename "$0")" must run from the package root"
  exit 1
fi

echo "-------------------------------"
echo "💡 Creating a scoped release..."
echo "-------------------------------"

if [ "$GITHUB_ACTIONS" = "true" ]; then
  echo "💡 Installing composer dependencies..."
  composer install --no-scripts
fi

echo "💡 Ensuring php-scoper is available..."
test -f bin/php-scoper.phar || curl -sLo bin/php-scoper.phar https://github.com/humbug/php-scoper/releases/latest/download/php-scoper.phar

echo "💡 Scoping namespaces using php-scoper..."
rm -rf scoped && php bin/php-scoper.phar add-prefix --quiet --output-dir=scoped --config=scripts/scoper.config.php
echo "✅ Successfully scoped all namespaces!..."

echo "💡 Dumping the autoloader in the scoped directory..."
composer dump-autoload --working-dir=scoped --classmap-authoritative

echo "💡 Cleaning up the scoped directory..."
rm scoped/composer.json scoped/composer.lock

echo "💡 Creating a zip file from the scoped directory..."
zip -rq scoped-release.zip scoped/

echo "-------------------------------------------"
echo "✅ Created a scoped release asset: scoped.zip"
echo "-------------------------------------------"