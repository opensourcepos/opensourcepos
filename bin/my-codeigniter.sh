#!/bin/sh

cd `dirname $0`
cd ..

# Install translations
php bin/install.php translations develop

# Install Roave Security Advisories
composer require roave/security-advisories:dev-master

# Install CodeIgniter Simple and Secure Twig
composer require kenjis/codeigniter-ss-twig:1.0.x@dev
php vendor/kenjis/codeigniter-ss-twig/install.php

# Install Codeigniter Matches CLI
php bin/install.php matches-cli master

# Install Cli for CodeIgniter
composer require kenjis/codeigniter-cli --dev
php vendor/kenjis/codeigniter-cli/install.php

# Install CI PHPUnit Test
composer require kenjis/ci-phpunit-test --dev
php vendor/kenjis/ci-phpunit-test/install.php

# Install CodeIgniter Deployer
composer require kenjis/codeigniter-deployer:1.0.x@dev --dev
php vendor/kenjis/codeigniter-deployer/install.php
