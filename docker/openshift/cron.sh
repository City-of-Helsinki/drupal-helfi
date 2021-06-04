#!/bin/bash

echo "Running cron"

while true
do
  drush migrate:import tpr_unit
  drush migrate:import tpr_service
  drush migrate:import tpr_errand_service
  drush migrate:import tpr_service_channel
  # Sleep for 6 hours.
  sleep 21600
done
