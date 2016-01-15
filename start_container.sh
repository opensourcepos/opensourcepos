#!/bin/bash
if [ ! -f /mysql-configured ]; then
    /usr/bin/mysqld_safe &
    sleep 10s
    MYSQL_PASSWORD=`pwgen -c -n -1 12`
    echo mysql root password: $MYSQL_PASSWORD
    echo $MYSQL_PASSWORD > /mysql-root-pw.txt
    mysqladmin -u root password $MYSQL_PASSWORD
    cp /app/application/config/database.php.tmpl /app/application/config/database.php 
    sed -i "s/\(password...=.\).*/\1'${MYSQL_PASSWORD}';/g" /app/application/config/database.php
    mysql -e "CREATE DATABASE IF NOT EXISTS ospos; use ospos; source /app/database/tables.sql; source /app/database/constraints.sql;" -uroot -p${MYSQL_PASSWORD}
    touch /mysql-configured
    killall mysqld
    sleep 10s
fi
supervisord -n
