#!/bin/bash
if [ ! -f /app/mysql-configured ]; then
    /usr/bin/mysqld_safe &
    sleep 10s
    MYSQL_PASSWORD=`pwgen -c -n -1 12`
    echo mysql root password: $MYSQL_PASSWORD
    echo $MYSQL_PASSWORD > /app/mysql-root-pw.txt
    [ -f /var/www/html/index.html ] && rm /var/www/html/index.html
    mysqladmin -u root password $MYSQL_PASSWORD
    cp /app/application/config/database.php.tmpl /app/application/config/database.php 
    sed -i -e "s/\(user.*\?=.\).*\(.\)$/\1'root'\2/g" /app/application/config/database.php
    sed -i -e "s/\(password.*\?=.\).*\(.\)$/\1'${MYSQL_PASSWORD}'\2/g" /app/application/config/database.php
    sed -i -e "s/\(database.*\?=.\).*\(.\)$/\1'ospos'\2/g" /app/application/config/database.php
    mysql -e "CREATE DATABASE IF NOT EXISTS ospos; use ospos; source /app/database/tables.sql; source /app/database/constraints.sql;" -uroot -p${MYSQL_PASSWORD}
    touch /app/mysql-configured
    killall mysqld
    sleep 10s
fi
supervisord -n
