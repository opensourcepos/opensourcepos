Dumping
=======

To dump a `Herrera\Version\Version` instance as a string, you will need to use
the `Herrera\Version\Dumper::toString()` method:

```php
use Herrera\Version\Dumper;
use Herrera\Version\Version;

$version = new Version(
    1,
    2,
    3,
    array('alpha', '1'),
    array('2')
);

echo Dumper::toString($version); // echoes "1.2.3-alpha.1+2"
```

If you need the version data as a simple array, you may use the `toComponents()`
method:

```php
use Herrera\Version\Parser;

$components = Dumper::toComponents($version);

// is the equivalent to:

$components = array(
    Parser::MAJOR => 1,
    Parser::MINOR => 2,
    Parser::PATCH => 3,
    Parser::PRE_RELEASE => array('alpha', '1'),
    Parser::BUILD => array('2')
);
```
