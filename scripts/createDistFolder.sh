#!/bin/bash

# Creates the dist folder, including untracked but required dist files

# clean up
rm -rf archive.zip dist

# clone the dist repo into dist/
git clone -b empty git@github.com:hirasso/rh-admin-utils-dist.git dist

# create the archive and save it in the dist/ dir
git archive --format=zip --output=archive.zip HEAD

# add bin/ to the dist.zip
zip -r archive.zip vendor-prefixed

# unzip the archive into dist
unzip archive.zip -d dist

# clean up
rm -rf archive.zip