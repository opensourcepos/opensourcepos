#!/bin/bash

docker run --rm -v $(pwd):/app jekkos/composer composer install
docker run --rm -v $(pwd):/app jekkos/composer php bin/install.php translations develop
sed -i "s/'\(dev\)'/'$rev'/g" application/config/config.php
docker run --rm -it -v $(pwd):/app -w /app digitallyseamless/nodejs-bower-grunt sh -c "npm install && bower install && grunt package"
