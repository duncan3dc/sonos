ARG PHP_VERSION=7.3
FROM php:${PHP_VERSION}-cli AS dev

RUN apt update && apt install -y libxml2-dev && docker-php-ext-install soap
RUN docker-php-ext-install sockets

ARG COVERAGE
RUN if [ "$COVERAGE" = "pcov" ]; then pecl install pcov && docker-php-ext-enable pcov; fi

RUN apt update && apt install -y git zip
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN git config --global --add safe.directory /app

WORKDIR /app


FROM php:8.4-cli AS test
CMD ["php", "-S", "sonos-test:1400", "/app/tests/local/router.php"]
