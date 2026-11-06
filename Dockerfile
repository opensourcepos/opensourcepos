FROM php:8.2-apache AS ospos
LABEL maintainer="jekkos"

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libgd-dev \
    && docker-php-ext-install mysqli bcmath intl gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

RUN echo "date.timezone = \"\${PHP_TIMEZONE}\"" > /usr/local/etc/php/conf.d/timezone.ini \
    && echo "expose_php = Off" > /usr/local/etc/php/conf.d/security.ini

WORKDIR /app
COPY . /app

RUN --mount=type=bind,from=composer:2,source=/usr/bin/composer,target=/usr/local/bin/composer \
    composer --no-interaction --no-plugins --no-scripts check-platform-reqs --lock --no-dev

RUN chown -R root:www-data /app \
    && chmod -R go-w,o= /app \
    && chown -R www-data:www-data /app/writable /app/public/uploads \
    && chmod 750 /app/writable/logs /app/writable/uploads /app/writable/cache /app/public/uploads /app/public/uploads/item_pics \
    && chmod 640 /app/writable/uploads/importCustomers.csv \
    && ln -s /app/*[^public] /var/www \
    && rm -rf /var/www/html \
    && ln -nsf /app/public /var/www/html

FROM ospos AS ospos_dev

ARG USERID
ARG GROUPID

RUN echo "Adding user uid $USERID with gid $GROUPID"
RUN ( addgroup --gid $GROUPID ospos || true ) && ( adduser --uid $USERID --gid $GROUPID ospos )

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
