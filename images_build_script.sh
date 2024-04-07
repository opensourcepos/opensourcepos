#!/bin/bash

# get branch and rev info
branch=${GITHUB_REF#refs/heads/}
rev=$GITHUB_SHA
date=`date +%Y%m%d%H%M%S`
version=$VERSION

# Run Docker commands
docker run --rm -v "$(pwd):/app" jekkos/composer composer install
docker run --rm -v "$(pwd):/app" jekkos/composer php bin/install.php translations develop
sed -i '' "s/'\(dev\)'/'$version'/g" application/config/config.php

#build
npm version "$version-$branch-$rev" --force || true
docker run -it -v "$(pwd):/app" -w /app opensourcepos/node-grunt-bower sh -c "npm config set registry http://registry.npmjs.org/ --global && npm cache clear --force && npm set maxsockets 3 && npm install coffeescript --also=dev && npm install --verbose && bower install && grunt package --force"
docker build . -t registry.digitalocean.com/dc-apps-registry/opensourcepos:latest-app
docker build database/ -t registry.digitalocean.com/dc-apps-registry/opensourcepos:sqlscript

docker push registry.digitalocean.com/dc-apps-registry/opensourcepos:latest-app
docker build database/ -t registry.digitalocean.com/dc-apps-registry/opensourcepos:sqlscript
