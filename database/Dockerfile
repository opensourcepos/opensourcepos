FROM alpine:3.14
MAINTAINER jekkos

ADD compiled_database.sql /docker-entrypoint-initdb.d/database.sql
VOLUME /docker-entrypoint-initdb.d
