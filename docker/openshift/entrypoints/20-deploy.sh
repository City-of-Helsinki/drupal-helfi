#!/bin/bash

cd /var/www/html/public

# Make sure we have active Drupal configuration.
if [ ! -f "../conf/cmi/system.site.yml" ]; then
  echo "Codebase is not deployed properly."
  exit 1
fi

VALUE=$(drush state:get last_deploy)

# This script is run every time a container is spawned and certain environments might
# start more than one Drupal container and this is used to make sure we run deploy
# tasks only once per deploy.
# Test it's been at least 5 minutes since last deploy.
if [ ! -n "$VALUE" ] || [ ($(date +%s) -qt $(expr "$VALUE" + 300)) ]; then
  drush state:set last_deploy $(date +%s)
  drush deploy
  exit 0
fi
