FROM ubuntu:22.04
MAINTAINER "artem@aleksashkin.com" Artem Aleksashkin

# BASE
RUN \
  DEBIAN_FRONTEND=noninteractive apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends\
      ca-certificates\
      apt-utils\
      apt-transport-https\
      wget\
      curl\
      && \
  usermod -u 1000 www-data && \
  groupmod -g 1000 www-data && \
  mkdir -p /var/www/src && \
  mkdir -p /var/www/data && \
  mkdir -p /var/www/images && \
  mkdir -p -m 777 /tmp/app && \
  chown -R www-data:www-data /var/www &&\
  rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/src

# PHP
RUN \
  DEBIAN_FRONTEND=noninteractive apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends\
      php8.1\
      php8.1-fpm\
      php8.1-curl\
      php8.1-mysql\
      php8.1-zip\
      php-json\
      php-pear\
      php-xdebug\
      php-bcmath\
      php-mbstring\
      && \
      sed -i "s|;*clear_env\s*=\s*no|clear_env = no|g" /etc/php/8.1/fpm/pool.d/www.conf && \
      sed -i -E 's|pid = .*?|pid = /run/php8.1-fpm.pid|g' /etc/php/8.1/fpm/php-fpm.conf && \
      sed -i -E 's|listen = .*?|listen = 9000|g' /etc/php/8.1/fpm/pool.d/www.conf && \
      sed -i "s|;*error_log\s*=\s*php_errors\.log|error_log = /dev/stderr|g" /etc/php/8.1/cli/php.ini && \
      wget -O /usr/local/bin/composer https://getcomposer.org/download/2.5.1/composer.phar && \
      chmod +x /usr/local/bin/composer && \
      rm -rf /var/lib/apt/lists/*

#COPY ./conf/php /etc/php

COPY --chown=www-data:www-data . .

# COMPOSER
USER www-data
RUN \
  composer install --no-ansi

USER root

VOLUME ["/etc/php", "/var/www/src"]

CMD ["/usr/sbin/php-fpm8.1", "-F"]

EXPOSE 9000
