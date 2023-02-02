FROM ubuntu:22.04
MAINTAINER "artem@aleksashkin.com" Artem Aleksashkin

# BASE
RUN \
  DEBIAN_FRONTEND=noninteractive apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends\
      ca-certificates\
      apt-utils\
      apt-transport-https\
      locales\
      language-pack-ru-base\
      tzdata\
      cron\
      wget\
      unzip\
      curl\
      git\
      mercurial\
      && \
  usermod -u 1000 www-data && \
  groupmod -g 1000 www-data && \
  mkdir -p /var/www/src && \
  mkdir -p /var/www/data && \
  mkdir -p /var/www/images && \
  mkdir -p -m 777 /tmp/app && \
  chown -R www-data:www-data /var/www &&\
  echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen &&\
  echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen &&\
  echo "ru_RU.UTF-8 UTF-8" >> /etc/locale.gen &&\
  DEBIAN_FRONTEND=noninteractive dpkg-reconfigure -f noninteractive locales &&\
  ln -snf /usr/share/zoneinfo/Europe/Moscow /etc/localtime &&\
  echo "Europe/Moscow" > /etc/timezone &&\
  DEBIAN_FRONTEND=noninteractive dpkg-reconfigure -f noninteractive tzdata &&\
  rm -rf /var/lib/apt/lists/*

ENV LANG  "ru_RU.UTF-8"
ENV LANGUAGE "ru_RU:ru"
ENV LC_MESSAGES "POSIX"
ENV TZ "Europe/Moscow"
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
#RUN \
#  composer install --no-ansi

USER root

VOLUME ["/etc/php", "/var/www/src"]

CMD ["/usr/sbin/php-fpm8.1", "-F"]

EXPOSE 9000
