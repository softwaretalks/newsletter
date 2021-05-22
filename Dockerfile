FROM php:7.4-cli

RUN apt-get update
RUN apt-get install -y zip libzip-dev libicu-dev
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl


RUN curl -sS https://getcomposer.org/installer | php -- --version=2.0.9 --install-dir=/usr/local/bin --filename=composer

COPY . /app/newsletter
WORKDIR /app/newsletter

RUN composer install

RUN apt-get autoremove -y

ENTRYPOINT ["php"]