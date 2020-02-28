FROM php:7.4-fpm-alpine

RUN wget https://get.symfony.com/cli/installer -O /tmp/symfony_installer \
    && sed -ri 's/^binary_dest=.*$/binary_dest=\/usr\/local\/bin/g' /tmp/symfony_installer \
    && sed -ri 's/builtin echo/echo/g' /tmp/symfony_installer \
    && sh /tmp/symfony_installer \
    && rm /tmp/symfony_installer

RUN apk update && apk add icu-dev git util-linux zlib-dev libzip-dev sqlite-dev

RUN docker-php-ext-install -j$(nproc) pdo_sqlite intl opcache zip

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN wget https://getcomposer.org/installer -O /tmp/composer_installer \
        && php /tmp/composer_installer --install-dir=/usr/local/bin --filename=composer \
        && rm /tmp/composer_installer

VOLUME /var/www/html
WORKDIR /var/www/html
