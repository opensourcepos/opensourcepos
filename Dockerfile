FROM php:5-apache
MAINTAINER jekkos
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    php5-apcu \
    libicu-dev \
    libgd-dev

RUN a2enmod rewrite
RUN docker-php-ext-install mysql mysqli bcmath intl gd
RUN echo "date.timezone = \"UTC\"" > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /app
COPY . /app
RUN ln -s /app/* /var/www/html

RUN cp application/config/database.php.tmpl application/config/database.php && \
    sed -i -e "s/\(localhost\)/web/g" test/ospos.js && \
    sed -i -e "s/\(user.*\?=.\).*\(.\)$/\1getenv('MYSQL_USERNAME')\2/g" application/config/database.php && \
    sed -i -e "s/\(password.*\?=.\).*\(.\)$/\1getenv('MYSQL_PASSWORD')\2/g" application/config/database.php && \
    sed -i -e "s/\(database.*\?=.\).*\(.\)$/\1getenv('MYSQL_DB_NAME')\2/g" application/config/database.php && \
    sed -i -e "s/\(hostname.*\?=.\).*\(.\)$/\1getenv('MYSQL_HOST_NAME')\2/g" application/config/database.php
