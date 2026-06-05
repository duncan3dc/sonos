ARG PHP_VERSION=8.1
FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install -y libxml2-dev && docker-php-ext-install soap
RUN docker-php-ext-install sockets

ARG COVERAGE
RUN if [ "$COVERAGE" = "pcov" ]; then pecl install pcov && docker-php-ext-enable pcov; fi

RUN apt update && apt install -y git zip
COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app
RUN git config --global --add safe.directory /app
