FROM php:8.4.14-fpm-alpine3.22

ARG DEV_MACHINE=no
ENV DEV_MACHINE="$DEV_MACHINE"

ARG DOCKER_UID=1000
ENV DOCKER_UID="$DOCKER_UID"

#
# Packages installation
#

RUN wget https://getcomposer.org/installer -O /tmp/composer_installer \
        && php /tmp/composer_installer --install-dir=/usr/local/bin --filename=composer \
        && rm /tmp/composer_installer

# Git is needed for managing Symfony recipes in the container
RUN apk add --no-cache icu php-intl util-linux libzip libpng acl \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev linux-headers libzip-dev libpng-dev \
    && docker-php-ext-install -j$(nproc) intl opcache zip gd bcmath \
    && if [ "$DEV_MACHINE" = "yes" ]; then \
        echo "Setting up develop software" \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug.so \
        && apk add --no-cache chromium chromium-chromedriver git; \
    else \
        echo "Skipping develop software"; \
    fi \
    && apk del .build-deps

#
# Set up configuration files
#

COPY php.ini-production.dist php.ini docker-php-ext-xdebug.ini docker-php-ext-xdebug.ini.dist /tmp/

RUN diff  "/tmp/php.ini-production.dist" "$PHP_INI_DIR/php.ini-production" \
    && mv "/tmp/php.ini"                 "$PHP_INI_DIR/php.ini" \
    && rm "/tmp/php.ini-production.dist"

RUN if [ "$DEV_MACHINE" = "yes" ]; then \
        echo "Setting up develop configuration" \
        && diff "/tmp/docker-php-ext-xdebug.ini.dist" "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
        && mv   "/tmp/docker-php-ext-xdebug.ini"      "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
        && rm   "/tmp/docker-php-ext-xdebug.ini.dist"; \
    else \
        echo "Skipping develop configuration" \
        && rm "/tmp/docker-php-ext-xdebug.ini.dist" "/tmp/docker-php-ext-xdebug.ini"; \
    fi

#
# Set up entrypoint
#

COPY entrypoint.sh /entrypoint.sh
CMD /entrypoint.sh

#
# Set up working directory
#

WORKDIR /var/www/html
VOLUME /var/www/html

#
# Put composer cache in var to keep in between container rebuilds
#

ENV COMPOSER_HOME=/var/www/html/var/composer

#
# Development stuff, but OK to be included in production image
#

ENV PANTHER_CHROME_DRIVER_BINARY=/usr/lib/chromium/chromedriver
ENV PANTHER_NO_SANDBOX=1
ENV CHROME_CONFIG_HOME=/tmp/chromium-config-home
ENV CHROME_USER_DATA_DIR=/tmp/chromium-user-data
