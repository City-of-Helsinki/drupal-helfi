FROM druidfi/drupal:7.4-web

# Configure nginx
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Override default fpm pool conf to run nginx and php-fpm as same user.
COPY docker/php-fpm-pool.conf /etc/php7/php-fpm.d/www.conf

# Install chromedriver.
RUN sudo apk add chromium chromium-chromedriver

# Autostart chromedriver and drush server
COPY docker/entrypoints/30-chromedriver.sh /entrypoints
COPY docker/entrypoints/30-drush-server.sh /entrypoints
