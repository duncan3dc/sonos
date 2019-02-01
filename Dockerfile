ARG PHP_VERSION=7.2
FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install -y libxml2-dev && docker-php-ext-install soap
RUN docker-php-ext-install sockets

ARG COVERAGE
RUN if [ "$COVERAGE" = "pcov" ]; then pecl install pcov && docker-php-ext-enable pcov; fi

# Install composer to manage PHP dependencies
RUN apt-get update && apt-get install -y git zip
RUN curl https://getcomposer.org/download/1.9.0/composer.phar -o /usr/local/sbin/composer
RUN chmod +x /usr/local/sbin/composer
RUN composer self-update

WORKDIR /app
