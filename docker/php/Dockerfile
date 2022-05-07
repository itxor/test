FROM composer as composer

FROM php:8.0-fpm

RUN apt-get update; apt-get install -y software-properties-common \
    libpq-dev \
    git && \
    docker-php-ext-install pdo pdo_pgsql pgsql

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p ~/.composer