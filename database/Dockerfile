FROM debian:jessie
MAINTAINER jekkos

ADD database.sql /docker-entrypoint-initdb.d/database.sql
VOLUME /docker-entrypoint-initdb.d
