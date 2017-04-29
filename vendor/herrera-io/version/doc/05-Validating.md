Validating
==========

The `Herrera\Version\Validator` class is meant to be used indirectly through
the other classes. However, you may use it to validate version information
according to the Semantic Versioning specification:

- `bool isIdentifier(str $identifier)` &mdash; returns `true` if the identifier
  is valid, false if not. An identifier may belong to either a pre-release
  version number or build metadata
- `bool isNumber(int|str $number)` &mdash; returns `true` if the version
  number is valid, or false if not
- `bool isVersion(str $version)` &mdash; returns `true` if the string is a
  valid version string representation, `false` if not
