Version
=======

[![Build Status]](http://travis-ci.org/herrera-io/php-version)

Version is a library for creating, editing, and comparing semantic version
numbers. Currently, v2.0.0 of the [Semantic Versioning][] specification
is supported.

```php
use Herrera\Version\Dumper;
use Herrera\Version\Parser;

$builder = Parser::toBuilder('1.2.3-alpha+2');
$builder->incrementMajor();
$builder->clearBuild();
$builder->clearPreRelease();

echo Dumper::toString($builder); // echoes "2.0.0"

$finalVersion = $builder->getVersion();
```

Documentation
-------------

- [Installing][]
- Usage
    - [Building][]
    - [Comparing][]
    - [Dumping][]
    - [Parsing][]
    - [Validating][]

[Build Status]: https://secure.travis-ci.org/herrera-io/php-version.png?branch=master
[Semantic Versioning]: http://semver.org/spec/v2.0.0.html
[Installing]: doc/00-Installing.md
[Building]: doc/01-Building.md
[Comparing]: doc/02-Comparing.md
[Dumping]: doc/03-Dumping.md
[Parsing]: doc/04-Parsing.md
[Validating]: doc/05-Validating.md
