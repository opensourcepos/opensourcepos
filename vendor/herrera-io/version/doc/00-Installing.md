Installing
==========

Composer
--------

The easiest way to install Version is by using [Composer][]:

    $ composer require herrera-io/version=~1.0

You may then load it by requiring the Composer autoloader:

```php
require 'vendor/autoload.php';
```

PSR-0
-----

You may use any class loader that supports [PSR-0][].

```php
$loader = new SplClassLoader();
$loader->add('Herrera\\Version', 'src/lib');
```

[Composer]: http://getcomposer.org/
[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md