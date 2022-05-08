FROM composer as composer

FROM php:8.0-fpm

RUN apt-get update; apt-get install -y software-properties-common \
    libpq-dev \
    libsodium23 libsodium-dev \
    git && \
    docker-php-ext-install pdo pdo_pgsql pgsql bcmath sodium sockets


RUN mkdir -p /usr/src/php/ext/redis \
   && curl -L https://github.com/phpredis/phpredis/archive/refs/tags/5.3.7.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
   && echo 'redis' >> /usr/src/php-available-exts \
   && docker-php-ext-install redis

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p ~/.composer

WORKDIR /var/www/karma8

RUN mkdir -p log
