FROM php:8.1-fpm

RUN apt-get update && apt-get -yqq install apt-transport-https curl git nano nginx libpq-dev libzip-dev zip \
    && curl -s https://getcomposer.org/installer > composer_install.php \
    && php composer_install.php && mv composer.phar /usr/local/bin/composer \
    && rm composer_install.php \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=true" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && docker-php-ext-install pdo_pgsql pgsql bcmath zip

WORKDIR /var/www

COPY . /var/www

RUN ls /etc/nginx/sites-enabled && \
    mv docker_configs/nginx-host.conf /etc/nginx/sites-available/default \
    && composer install
USER root

ENTRYPOINT php-fpm -D -R && nginx -g "daemon off;"