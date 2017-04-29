RobotLoader: comfortable autoloading
====================================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/robot-loader.svg)](https://packagist.org/packages/nette/robot-loader)
[![Build Status](https://travis-ci.org/nette/robot-loader.svg?branch=master)](https://travis-ci.org/nette/robot-loader)
[![Coverage Status](https://coveralls.io/repos/github/nette/robot-loader/badge.svg?branch=master)](https://coveralls.io/github/nette/robot-loader?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/robot-loader/v/stable)](https://github.com/nette/robot-loader/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/robot-loader/blob/master/license.md)

RobotLoader is a tool that gives you comfort of automated class loading for your entire application including third-party libraries.

- get rid of all `require`
- only necessary scripts are loaded
- requires no strict file naming conventions
- allows more classes in single file

So we can forget about those famous code blocks:

```php
require_once 'Zend/Pdf/Page.php';
require_once 'Zend/Pdf/Style.php';
require_once 'Zend/Pdf/Color/GrayScale.php';
require_once 'Zend/Pdf/Color/Cmyk.php';
...
```


Like the Google robot crawls and indexes websites, RobotLoader crawls all PHP scripts and records what classes and interfaces were found in them.
These records are then saved in cache and used during all subsequent requests. You just need to specifiy what directories to index and where to save the cache:

```php
$loader = new Nette\Loaders\RobotLoader;
// Add directories for RobotLoader to index
$loader->addDirectory('app');
$loader->addDirectory('libs');
// And set caching to the 'temp' directory on the disc
$loader->setTempDirectory('temp');
$loader->register(); // Run the RobotLoader
```

And that's all. From now on, you don't need to use `require`. Great, isn't it?

When RobotLoader encounters duplicate class name during indexing, it throws an exception and informs you about it.

The variable `$loader->autoBuild` determines whether RobotLoader should reindex the scripts if asked for nonexistent class.
This feature is disabled by default on production server.

If you want RobotLoader to skip some directory, create a file there called `netterobots.txt`:

```
Disallow: /Zend
```

From this point on, the Zend directory will not be indexed.

RobotLoader is extremely comfortable and addictive!
