#!/bin/bash

# Prepares the dist folder, including untracked but required dist files

# Stop execution on errors
set -e

# Validate that we are at the project root
if [[ ! -f "$PWD/.gitignore" ]]; then
  echo "❌ "$(basename "$0")" must run from the package root"
  exit 1
fi

# Validate that we are in a GitHub action
if [ "$GITHUB_ACTIONS" != "true" ]; then
  echo "❌ "$(basename "$0")" may only run in GitHub Actions"
  exit 1
fi

PACKAGE_NAME=$(basename "$PWD");
PACKAGE_VERSION=$(node ./scripts/logVersion.js)

if [[ -z "$PACKAGE_VERSION" ]]; then
  echo "❌ Empty package version. Exiting."
  exit 1
fi

echo "💡 Committing and pushing new release: 'v${PACKAGE_VERSION}'..."

cd dist/
git add .
git commit -m "Release: rh-admin-utils@${PACKAGE_VERSION}"
git tag "v${PACKAGE_VERSION}"
git push origin "v${PACKAGE_VERSION}"

echo "✅ Released 'v${PACKAGE_VERSION}' to the dist repo."