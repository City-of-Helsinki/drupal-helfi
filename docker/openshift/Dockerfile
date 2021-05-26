FROM druidfi/drupal:8.0-web

RUN chgrp -R 0 /usr/local/share/ca-certificates/ && chmod g+w -R /usr/local/share/ca-certificates/
# Fetch certificate for the Azure MariaDB service
RUN wget https://www.digicert.com/CACerts/BaltimoreCyberTrustRoot.crt.pem -O /usr/local/share/ca-certificates/BaltimoreCyberTrustRoot.crt.pem

# Configure nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Override default fpm pool conf to run nginx and php-fpm as same user.
COPY php-fpm-pool.conf /etc/php8/php-fpm.d/www.conf
