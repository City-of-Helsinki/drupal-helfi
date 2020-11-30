#!/usr/bin/env bash

echo "Copy Drupal to the root folder of the web server"
# copy files to Azure Files
#rsync -vhrlu "/opt/drupal/blt-deploy/" "/var/www/html" --exclude="web/sites/default/files"
# copy files to Azure Disk
rsync -vah "/opt/drupal/public/" "/var/www/html/public/" --exclude="web/sites/default/files"
rsync -vah "/opt/drupal/vendor/" "/var/www/html/vendor/"
rsync -vah "/opt/drupal/conf/" "/var/www/html/conf/"

echo "Set cron key"
if [[ ! -z "${DRUPAL_CRON_KEY}" ]]; then
    echo ${DRUPAL_CRON_KEY}
    ./vendor/bin/drush sset system.cron_key ${DRUPAL_CRON_KEY}
fi

echo "Run PHP-FPM in the background as a daemon"
php-fpm

# echo "Install or update (default) Drupal"
# # DRUPAL_DEPLOY_STRATEGY can be install or update
# # Running the blt drupal:install command drops the existing database tables, and then installs Drupal from scratch.
# # Running the blt drupal:update command clears cache, updates database, and imports configuration.
# if [[ ! -z "${DRUPAL_DEPLOY_STRATEGY}" ]]; then
#     echo "Deploy drupal using ${DRUPAL_DEPLOY_STRATEGY}"
#     ./vendor/bin/blt drupal:${DRUPAL_DEPLOY_STRATEGY} --no-interaction
#     ./vendor/bin/blt drupal:config:import --no-interaction
# else
#     echo "Deploy drupal with update"
#     ./vendor/bin/blt drupal:update --no-interaction
#     ./vendor/bin/blt drupal:config:import --no-interaction
# fi

