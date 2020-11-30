#!/usr/bin/env bash

echo "Copy Drupal to the root folder of the web server"
# copy files to Azure Disk after the container has started and volumes have been mounted
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

echo "To install Drupal for the first time (clears database), execute"
echo "drush site:install --existing-config"
echo "in /var/www/html/public via the terminal"
