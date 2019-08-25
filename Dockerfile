ARG PHP_VERSION=7.1
FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install -y libxml2-dev && docker-php-ext-install soap
RUN docker-php-ext-install sockets

ARG COVERAGE
RUN if [ "$COVERAGE" = "xdebug" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi

# Install composer to manage PHP dependencies
RUN apt-get update && apt-get install -y git zip
RUN curl https://getcomposer.org/download/1.9.0/composer.phar -o /usr/local/sbin/composer
RUN chmod +x /usr/local/sbin/composer
RUN composer self-update

WORKDIR /app
