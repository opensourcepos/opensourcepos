# Upgrade from 2.x to 4.0 


## Version

- Version 3.0 was skipped, because master branch had 3.0-dev alias with code base similar to 2.8.
  Since then there were many BC breaks, there for naming it 4.0.  


## PHP version

- Min PHP version was raised:
 
  Before: 5.3
  
  After: **5.4**


## Distribution

- PEAR support was dropped. **Use PHAR file instead**. Latest stable version can be always found at [apigen.org](http://apigen.org)

- New [Release process](wiki/Release-Process) was established. Releasing minor version **every 2 months**. 


## Cli commands

- [Symfony\Console](https://github.com/symfony/Console) replaced custom CLI solution, thus composer-like approach is used.
  In particular, you need to call specific command name in first argument.

  Before:
  
  `apigen -s source -d destination`
  
  After:
  
  `apigen generate -s source -d destination`
  
- New command `self-update` added, to upgrade `.phar` file:  

  Before:
  
  *manual update*
  
  After:
    
 `apigen self-update`


## Cli options

- Bool options are off when absent, on when present.
  
  Before:
  
  `... --tree yes # tree => true`

  `... --tree no # tree => false`
  
   After:
   
   `... --tree # tree => true`

   `... # tree => false`

- Options with values now accept multiple formats:

  Before:
  
  `... --access-levels public --access-levels protected`
  
  After:

  `... --access-levels public,protected`
  
  or
  
 `... --access-levels="public,protected"`

  or 

 `... --access-levels public --access-levels protected`
  

- Some options were dropped. To see what the available ones are, just run `apigen generate --help`.

  - `--skip-doc-prefix` was dropped, use `--skip-doc-path` instead
  - `--allowed-html` was dropped
  - `--autocomplete` was dropped; autocomplete now works for classes, constants and functions by default
  - `--report`; use [Php_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) for any custom checkstyle
  - `--wipeout`; now wipes out everytime
  - `--progressbar`; now always present
  - `--colors`; now always colors
  - `--update-check`; now always colors
  
- Some options were renamed and reversed.
  
  - `--source-code` was off by default, now it on by default; to turn it off, add `--no-source-code`  


## Markup

- Docblock markup was changed from Texy to [Markdown Markup](https://github.com/michelf/php-markdown)


## Coding standard

- [Zenify\CodingStandard](https://github.com/Zenify/CodingStandard) was introduces. It's based on [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

- Part of continuous integration testing by [Travis CI](http://travis-ci.org).


## Tests

- From no tests to unit testing with [PHPUnit](https://github.com/sebastianbergmann/phpunit). With decent coverage of ~80 %.

- Part of continuous integration testing by [Travis CI](http://travis-ci.org).
