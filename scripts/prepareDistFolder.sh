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

# Make sure `prefixNamespaces.sh` was executed
if [ ! -d "vendor" ]; then
  echo "❌ The 'vendor' folder does not exist. Please run prefixNamespaces.sh first."
  exit 1
fi

# Stuff that is already prepared in GitHub Actions
if [ "$GITHUB_ACTIONS" != "true" ]; then
  # clean up
  rm -rf release.zip dist

  echo "💡 cloning the dist repo into dist/"
  git clone -b empty git@github.com:hirasso/rh-admin-utils-dist.git dist/
fi

echo "💡 Checking out the empty tagged root commit"
git -C dist checkout --detach empty

echo "💡 Creating the release.zip"
git archive --format=zip --output=release.zip HEAD

echo "💡 Injecting additional untracked files into release.zip"
zip -r release.zip vendor

echo "💡 Unpacking the release.zip into dist/"
unzip release.zip -d dist

echo "💡 Overwriting the composer.json in dist/"
mv dist/composer.dist.json dist/composer.json