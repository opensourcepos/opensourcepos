FROM php:7.4-apache AS ospos
LABEL maintainer="jekkos"

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libicu-dev \
    libgd-dev \
    openssl

RUN a2enmod rewrite headers
RUN docker-php-ext-install mysqli bcmath intl gd
RUN echo "date.timezone = \"\${PHP_TIMEZONE}\"" > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /app
COPY . /app
RUN ln -s /app/*[^public] /var/www && rm -rf /var/www/html && ln -nsf /app/public /var/www/html
RUN chmod -R 750 /app/public/uploads /app/application/logs && chown -R www-data:www-data /app/public /app/application

FROM ospos AS ospos_test

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get install -y libzip-dev wget git
RUN wget https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh -O /bin/wait-for-it.sh && chmod +x /bin/wait-for-it.sh
RUN docker-php-ext-install zip
RUN composer install -d/app 
RUN php /app/vendor/kenjis/ci-phpunit-test/install.php -a /app/application -p /app/vendor/codeigniter/framework
RUN sed -i 's/backupGlobals="true"/backupGlobals="false"/g' /app/application/tests/phpunit.xml
RUN sed -i '13,17d' /app/application/tests/controllers/Welcome_test.php 
WORKDIR /app/application/tests

CMD ["/app/vendor/phpunit/phpunit/phpunit"]

FROM ospos AS ospos_dev

RUN mkdir -p /app/bower_components && ln -s /app/bower_components /var/www/html/bower_components
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
