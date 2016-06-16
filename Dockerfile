FROM php:5-apache
MAINTAINER jekkos
RUN sed -i -e 's/archive.ubuntu.com\|security.ubuntu.com/old-releases.ubuntu.com/g' /etc/apt/sources.list
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    php5-gd \
    php5-apcu

RUN a2enmod rewrite
RUN docker-php-ext-install mysql mysqli bcmath

WORKDIR /app
COPY . /app

RUN cp application/config/database.php.tmpl application/config/database.php && \
    sed -i -e "s/\(localhost\)/web/g" test/ospos.js && \
    sed -i -e "s/\(user.*\?=.\).*\(.\)$/\1'${MYSQL_USERNAME}'\2/g" application/config/database.php && \
    sed -i -e "s/\(password.*\?=.\).*\(.\)$/\1'${MYSQL_PASSWORD}'\2/g" application/config/database.php && \
    sed -i -e "s/\(database.*\?=.\).*\(.\)$/\1'${MYSQL_DB_NAME}'\2/g" application/config/database.php && \
    sed -i -e "s/\(hostname.*\?=.\).*\(.\)$/\1'${MYSQL_HOST_NAME}'\2/g" application/config/database.php
