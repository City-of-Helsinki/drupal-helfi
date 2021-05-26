FROM druidfi/drupal:8.0-web

# Fetch certificate for the Azure MariaDB service
RUN sudo wget https://www.digicert.com/CACerts/BaltimoreCyberTrustRoot.crt.pem -O /usr/local/share/ca-certificates/BaltimoreCyberTrustRoot.crt.pem

# Configure nginx
COPY docker/openshift/nginx.conf /etc/nginx/conf.d/default.conf

# Override default fpm pool conf to run nginx and php-fpm as same user.
COPY docker/openshift/php-fpm-pool.conf /etc/php8/php-fpm.d/www.conf
