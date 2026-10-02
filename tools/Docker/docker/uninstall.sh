#!/bin/bash

cd docker

. ./.env

docker-compose -f ../docker-compose.yml down
