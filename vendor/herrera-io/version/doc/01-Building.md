Building
========

The Version library provides a programmatic way of creating and manipulating
semantic version numbers. Some methods will even automatically apply the logic
defined by Semantic Versioning specification.

Creating a Builder
------------------

### Fresh Start

- `Herrera\Version\Builder::create()`

To create a fresh version builder, call `Builder::create()`:

```php
$builder = Herrera\Version\Builder::create();
```

The default version is `0.0.0`.

Importing Existing Versions
---------------------------

### From Components (Advanced)

- `Herrera\Version\Builder::importComponents(array $components)`

If you have extracted the components of a version string representation using
the `Herrera\Version\Parser::toComponents()` method, you can call the method,
`importComponents()`:

```php
$builder->importComponents($components);
```

Any version information missing from the given components array will be reset
to their default values in the builder:

- **Major Version**: `0`
- **Minor Version**: `0`
- **Patch Version**: `0`
- **Pre-release Version**: none
- **Build Metadata**: none

### From a String Representation

- `Herrera\Version\Builder::importString(string $version)`

To simplify the parsing and building of versions, the `importString()` method
exists for your convenience:

```php
$builder->importString('1.0.0-alpha.1+2');
```

This is the equivalent of doing the following:

```php
$builder->importComponents(
    Herrera\Version\Parser::toComponents('1.0.0-alpha.1+2')
);
```

The caveat about missing version information applies.

### From an Existing Version

- `Herrera\Version\Builder::importVersion(Herrera\Version\Version $version)`

If you already have an instance of `Herrera\Version\Version`, you can import
that directly to the builder:

```php
$builder->importVersion($version);
```

Changing Version Information
----------------------------

- `Herrera\Version\Builder::setMajor(int $number)`
- `Herrera\Version\Builder::setMinor(int $number)`
- `Herrera\Version\Builder::setPatch(int $number)`
- `Herrera\Version\Builder::setPreRelease(array $identifiers)`
- `Herrera\Version\Builder::setBuild(array $identifiers)`

You may use any of the above methods to directly alter the version information.
Note that you may only use integer values for `setMajor()`, `setMinor()`, and
`setPatch()`. See the documentation for [intval()][] to understand how string
numbers are converted to integers.

To automatically apply the logic defined in the specification, you may use:

- `Herrera\Version\Builder::incrementMajor(int $amount = 1)` &mdash; increments
  the major version number by `$amount`, and resets the minor and patch version
  numbers to zero
- `Herrera\Version\Builder incrementMinor(int $amount = 1)` &mdash; increments
  the minor version number by `$amount`, and resets the patch version number
  to zero

For the sake of completeness and convenience, the method

- `Herrera\Version\Builder::incrementPatch(int $amount = 1)`

has been added. It will simply increment the patch version number by the
`$amount` specified.

[intval()]: http://php.net/intval

### Resetting Identifiers

A couple of convenience methods are available for resetting the pre-release
version identifiers and build metadata identifiers:

```php
$builder->clearBuild();

// same as

$builder->setBuild(array());
```

```php
$builder->clearPreRelease();

// same as

$builder->setPreRelease(array());
```

Getting the Final Version
-------------------------

Once you have built your final version number, you may retrieve an instance
of the `Herrera\Version\Version` class. This class maintains a read-only copy
of the version information.

```php
$version = $builder->getVersion();
```
