#!/bin/bash

# Install Docker and Docker Compose (macOS)
#echo "Installing Docker and Docker Compose..."
#brew install docker docker-compose

# Log into Docker Hub
#echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
branch="master"
rev="9237a4"
date=`date +%Y%m%d%H%M%S`


# Run Docker commands
docker run --rm -v "$(pwd):/app" jekkos/composer composer install
docker run --rm -v "$(pwd):/app" jekkos/composer php bin/install.php translations develop
sed -i '' "s/'\(dev\)'/'$rev'/g" application/config/config.php

#version=$(grep application_version application/config/config.php | sed -E "s/.*=\s*'([^']+)';/\1/g")
version="3.3.9"
echo "this is the version: $version-$branch-$rev"
npm version "$version-$branch-$rev" --force || true
docker run --rm -it -v "$(pwd):/app" -w /app opensourcepos/node-grunt-bower sh -c "npm install --dev coffeescript && npm install && bower install && grunt package --force"
docker build . -t dc/ospos
docker build database/ -t dc/opensourcepos:sqlscript
