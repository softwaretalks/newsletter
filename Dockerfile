FROM php:7.4-cli-alpine

RUN apk add --update --no-cache zip libzip-dev icu-dev
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl

RUN curl -sS https://getcomposer.org/installer | php -- --version=2.0.9 --install-dir=/usr/local/bin --filename=composer

COPY . /app/newsletter
WORKDIR /app/newsletter

RUN composer install

ENTRYPOINT ["php"]
