Phar Update
===========

[![Build Status](https://travis-ci.org/herrera-io/php-phar-update.png?branch=master)](https://travis-ci.org/herrera-io/php-phar-update)

A library for self-updating Phars.

Summary
-------

This library handles the updating of applications packaged as distributable Phars. The modular design allows for a more customizable update process.

Installation
------------

Add it to your list of Composer dependencies:

```sh
$ composer require herrera-io/phar-update=1.*
```

Usage
-----

```php
<?php

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;

$manager = new Manager(Manifest::loadFile(
    'http://box-project.org/manifest.json'
));

// update to the next available 1.x update
$manager->update('1.0.0', true);
```