JSON
====

[![Build Status](https://travis-ci.org/herrera-io/php-json.png?branch=master)](https://travis-ci.org/herrera-io/php-json)

A library for simplifying JSON linting and validation.

Summary
-------

Uses the [`justinrainbow/json-schema`](https://packagist.org/packages/justinrainbow/json-schema) and [`seld/jsonlint`](https://packagist.org/packages/seld/jsonlint) libraries to lint and validate JSON data. Also decodes JSON data as to only lint when an error is encountered, minimizing performance impact.

Installation
------------

Add it to your list of Composer dependencies:

```sh
$ composer require herrera-io/json=1.*
```

Usage
-----

```php
<?php

use Herrera\Json\Json;

$json = new Json();

$json->validate($schema, $decoded); // throws Herrera\Json\Exception\JsonException

$data = $json->decode('{'); // throws Seld\JsonLint\ParsingException
```
