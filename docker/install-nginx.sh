#!/bin/bash

cd docker

# load local environment variables
if [ ! -e ".env" ]; then
  echo "The .env (environment variables) file is missing"
  exit 1
fi

. ./.env

docker-compose -f ../docker-compose.nginx.yml build

/bin/bash ./init-letsencrypt.sh
