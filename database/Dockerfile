FROM debian:jessie
MAINTAINER jekkos

ADD database.sql /docker-entrypoint-initdb.d/database.sql
VOLUME /docker-entrypoint-initdb.d

ADD docker_mysql.cnf /etc/mysql/conf.d/my.cnf
VOLUME /etc/mysql/conf.d
