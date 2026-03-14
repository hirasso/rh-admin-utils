#!/usr/bin/env bash

wp i18n make-pot . languages/rh-admin-utils.pot \
  --include="src,rh-admin-utils.php" \
  --slug="rh-admin-utils" \
  --headers='{"Report-Msgid-Bugs-To":"https://github.com/hirasso/rh-admin-utils/","POT-Creation-Date":""}'