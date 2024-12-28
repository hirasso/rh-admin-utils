#!/bin/bash

# Prepares the dist folder, including untracked but required dist files

# Stop execution on errors
set -e

echo "💡 Preparing the dist folder..."

# Validate that we are at the project root
if [[ ! -f "$PWD/.gitignore" ]]; then
  echo "❌ "$(basename "$0")" must run from the package root"
  exit 1
fi


# Make sure `createScopedReleaseAsset` was executed
if [ ! -d "scoped" ]; then
  echo "❌ The 'scoped' folder does not exist. Please run createScopedReleaseAsset.sh first."
  exit 1
fi

# Initialize the dist folder if it doesn't exist
if [ "$GITHUB_ACTIONS" != "true" ]; then
  echo "💡 cloning the dist repo into dist/"
  rm -rf dist && git clone -b empty git@github.com:hirasso/rh-admin-utils-dist.git dist/
fi

echo "💡 Checking out the empty tagged root commit"
git -C dist checkout --detach empty

echo "💡 Copying all files from scoped/ to dist/"
cp -Rf scoped/* dist/

# echo "💡 Overwriting the composer.json in dist/"
cp composer.dist.json dist/composer.json