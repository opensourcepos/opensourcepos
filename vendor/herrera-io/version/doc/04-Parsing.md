Parsing
=======

There are three ways of parsing version information with the
`Herrera\Version\Parser` class.

String to Builder
----------------

- `Herrera\Version\Builder toBuilder(str $version)`

The `toBuilder()` method will return a version builder for the given string
representation:

```php
use Herrera\Version\Parser;

$builder = Parser::toBuilder('1.0.0-alpha.1+2');
```

String to Components
--------------------

If you just need the version string broken down into its components, you may
use the `toComponenets()` method:

```php
$componenets = Parser::toComponents('1.0.0-alpha.1+2');

// is the equivalent to:

$components = array(
    Parser::MAJOR => 1,
    Parser::MINOR => 0,
    Parser::PATCH => 0,
    Parser::PRE_RELEASE => array('alpha', '1'),
    Parser::BUILD => array('2')
);
```

String to Version
-----------------

If you simply need an instance of `Herrera\Version\Version`, you may use the
`toVersion()` method:

```php
$version = Parser::toVersion('1.0.0-alpha.1+2');
```
