#!/bin/bash

# Prepares the dist folder, including untracked but required dist files

# Stop execution on errors
set -e

# Make sure `prefixNamespaces.sh` was executed
if [ ! -d "vendor" ]; then
  echo "Error: The 'vendor' folder does not exist. Please run prefixNamespaces.sh first."
  exit 1
fi

# Stuff that is already prepared in GitHub Actions
if [ "$GITHUB_ACTIONS" != "true" ]; then
  # clean up
  rm -rf archive.zip dist
  # clone the dist repo into dist/
  git clone -b empty git@github.com:hirasso/rh-admin-utils-dist.git dist
fi

# create the archive and save it in the dist/ dir
git archive --format=zip --output=archive.zip HEAD

# add the vendor folder to the dist.zip
zip -r archive.zip vendor

# unzip the archive into dist
unzip archive.zip -d dist

# Overwrite the composer.json in the dist folder
mv dist/composer.dist.json dist/composer.json

# clean up
rm -rf archive.zip