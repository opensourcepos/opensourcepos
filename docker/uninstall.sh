#!/bin/bash

cd "$(dirname "$0")"

. ./.env

docker-compose -f ../docker-compose.yml down
