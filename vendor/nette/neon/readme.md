[NEON](http://ne-on.org): Nette Object Notation
===============================================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/neon.svg)](https://packagist.org/packages/nette/neon)
[![Build Status](https://travis-ci.org/nette/neon.svg?branch=master)](https://travis-ci.org/nette/neon)
[![Coverage Status](https://coveralls.io/repos/github/nette/neon/badge.svg?branch=master)](https://coveralls.io/github/nette/neon?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/neon/v/stable)](https://github.com/nette/neon/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/neon/blob/master/license.md)

NEON is very similar to YAML.The main difference is that the NEON supports "entities"
(so can be used e.g. to parse phpDoc annotations) and tab characters for indentation.
NEON syntax is a little simpler and the parsing is faster.

Example of Neon code:

```
# my web application config

php:
	date.timezone: Europe/Prague
	zlib.output_compression: yes  # use gzip

database:
	driver: mysql
	username: root
	password: beruska92

users:
	- Dave
	- Kryten
	- Rimmer
```

Links:
- [Neon sandbox](http://ne-on.org)
- [Neon for Javascript](https://github.com/matej21/neon-js)
