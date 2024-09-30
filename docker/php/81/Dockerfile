FROM php:8.1-cli
WORKDIR /usr/src/myapp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install zip unzip git -y \
    && pecl install xdebug \
    && pecl install pcov
