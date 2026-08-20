FROM alpine:3.14
LABEL maintainer="jekkos"

ADD database.sql /docker-entrypoint-initdb.d/database.sql
VOLUME /docker-entrypoint-initdb.d
