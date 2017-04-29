Nette Bootstrap
===============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/bootstrap.svg)](https://packagist.org/packages/nette/bootstrap)
[![Build Status](https://travis-ci.org/nette/bootstrap.svg?branch=master)](https://travis-ci.org/nette/bootstrap)
[![Coverage Status](https://coveralls.io/repos/github/nette/bootstrap/badge.svg?branch=master)](https://coveralls.io/github/nette/bootstrap?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/bootstrap/v/stable)](https://github.com/nette/bootstrap/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/bootstrap/blob/master/license.md)

File `bootstrap.php` loads Nette Framework and all libraries that we depend on:

```php
require __DIR__ . '/../vendor/autoload.php';
```

Class `Configurator` creates so called DI container and handles application initialization.

```php
$configurator = new Nette\Configurator;
```

Activates Tracy in strict mode:

```php
//$configurator->setDebugMode(TRUE);
$configurator->enableTracy(__DIR__ . '/../log');
```

Setup directory for temporary files

```php
$configurator->setTempDirectory(__DIR__ . '/../temp');
```

Activate [autoloading](https://doc.nette.org/en/auto-loading#toc-nette-loaders-robotloader), that will automatically load all the files with our classes:

```php
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->register();
```

And according to the configuration files it generates a DI container. Everything else depends on it.

We will return this DI Container as a result of calling `app/boostrap.php`

```php
$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');
return $configurator->createContainer();
```

and we will store it as local variable in `www/index.php` and run the application:

```php
$container = require __DIR__ . '/../app/bootstrap.php';
$container->getService('application')->run();
```

That's it.
