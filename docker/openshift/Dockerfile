FROM ghcr.io/city-of-helsinki/drupal-docker-base:8.0

COPY / /var/www/html/
WORKDIR /var/www/html

# Install and run Composer
COPY --from=composer:2.0 /usr/bin/composer /usr/local/bin/
RUN set -eux; \
  export COMPOSER_HOME="$(mktemp -d)"; \
    composer install --no-progress --profile --prefer-dist --no-interaction
