FROM composer as composer

FROM php:8.1-fpm-alpine

RUN apk add --no-cache bash

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p ~/.composer