# from https://www.drupal.org/docs/8/system-requirements/drupal-8-php-requirements
FROM php:7.4-fpm-alpine3.12

# install the PHP extensions we need
# postgresql-dev is needed for https://bugs.alpinelinux.org/issues/3642
RUN set -eux; \
	\
	apk add --no-cache --virtual .build-deps \
		coreutils \
		freetype-dev \
		libjpeg-turbo-dev \
		libpng-dev \
		libzip-dev \
		postgresql-dev \
	; \
	\
	docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg=/usr/include \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		gd \
		opcache \
		pdo_mysql \
		pdo_pgsql \
		zip \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --virtual .drupal-phpexts-rundeps $runDeps; \
	apk del .build-deps

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
		echo 'opcache.fast_shutdown=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# https://github.com/drupal/drupal/blob/9.0.1/composer.lock#L4052-L4053
COPY --from=composer:2.0 /usr/bin/composer /usr/local/bin/

# https://www.drupal.org/node/3060/release
ENV DRUPAL_VERSION 9.0.6

# Install required tools (https://docs.acquia.com/blt/install/)
# alpine-sdk required for the make script in composer.json
RUN apk update && apk upgrade && \
    apk add --no-cache git patch rsync bash mariadb-client alpine-sdk

## Install phpredis
#RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
#      && pecl install redis \
#      && docker-php-ext-enable redis \
#      && apk del pcre-dev ${PHPIZE_DEPS} \
#      && rm -rf /tmp/pear

WORKDIR /opt/drupal

# Copy Drupal project from Git repo
COPY / /opt/drupal
# Install Drupal app
RUN set -eux; \
	export COMPOSER_HOME="$(mktemp -d)"; \
	composer install --no-progress --profile --prefer-dist --no-interaction
## 	chown -R www-data:www-data web/sites web/modules web/themes; \
## 	rmdir /var/www/html; \
## 	ln -sf /opt/drupal/web /var/www/html; \
##	# delete composer cache
##	rm -rf "$COMPOSER_HOME"

ENV PATH=${PATH}:/opt/drupal/vendor/bin

# vim:set ft=dockerfile:

# Create build artifact
#RUN ./vendor/bin/blt artifact:build

# Copy start script
COPY /docker/openshift/docker-entrypoint.sh /usr/local/bin/

#WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]

CMD []
