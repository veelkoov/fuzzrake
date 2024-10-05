FROM php:8.3.12-fpm-alpine3.20

ARG DEV_MACHINE=no
ENV DEV_MACHINE "$DEV_MACHINE"

ARG DOCKER_UID=1000
ENV DOCKER_UID "$DOCKER_UID"

#
# Packages installation
#

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev linux-headers

RUN apk add --no-cache icu php-intl util-linux zip libzip-dev libpng libpng-dev acl

RUN wget https://get.symfony.com/cli/installer -O /tmp/symfony_installer \
    && sed -ri 's/^binary_dest=.*$/binary_dest=\/usr\/local\/bin/g' /tmp/symfony_installer \
    && sed -ri 's/builtin echo/echo/g' /tmp/symfony_installer \
    && sh /tmp/symfony_installer \
    && rm /tmp/symfony_installer

RUN docker-php-ext-install -j$(nproc) intl opcache zip gd bcmath

RUN wget https://getcomposer.org/installer -O /tmp/composer_installer \
        && php /tmp/composer_installer --install-dir=/usr/local/bin --filename=composer \
        && rm /tmp/composer_installer

RUN if [ "$DEV_MACHINE" == "yes" ]; then \
        echo "Setting up develop software" \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug.so \
        && apk add --no-cache chromium chromium-chromedriver; \
    else \
        echo "Skipping develop software"; \
    fi

RUN apk del .build-deps

#
# Set up configuration files
#

COPY php.ini-production.dist php.ini docker-php-ext-xdebug.ini docker-php-ext-xdebug.ini.dist /tmp/

RUN if [ "$DEV_MACHINE" == "yes" ]; then \
        echo "Setting up develop configuration" \
        && diff "/tmp/docker-php-ext-xdebug.ini.dist" "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
        && mv   "/tmp/docker-php-ext-xdebug.ini"      "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
        && rm   "/tmp/docker-php-ext-xdebug.ini.dist"; \
    else \
        echo "Skipping develop configuration" \
        && rm "/tmp/docker-php-ext-xdebug.ini.dist" "/tmp/docker-php-ext-xdebug.ini"; \
    fi

RUN diff  "/tmp/php.ini-production.dist" "$PHP_INI_DIR/php.ini-production" \
    && mv "/tmp/php.ini"                 "$PHP_INI_DIR/php.ini" \
    && rm "/tmp/php.ini-production.dist"

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
# Allow setting Composer home to a volume, to preserve cache e.g. between Jenkins jobs
#

RUN mkdir /composer
ENV COMPOSER_HOME /composer
VOLUME /composer

#
# Development stuff, but OK to be included in production image
#

ENV PANTHER_CHROME_DRIVER_BINARY /usr/lib/chromium/chromedriver
ENV PANTHER_NO_SANDBOX 1
