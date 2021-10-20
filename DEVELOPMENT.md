# package.json

## scripts

`npm run scriptname`

- `clean` - wipe the dependencies downloaded by Composer, Bower, and npm
```
grunt clean:composer & grunt clean:bower & grunt clean:npm
```
- `install` - automatically runs the Composer and Bower install commands after installing the npm dependencies
```
composer install & bower install
```
- `update` - updates Composer and the dependencies downloaded by Composer and npm
```
npm update & composer self-update & composer update
```

## devDependencies

- `grunt` - used for copying files downloaded by npm
- `grunt-contrib-clean` - Grunt plugin to clean files before copying
- `grunt-contrib-copy` - Grunt plugin to copy files downloaded by npm
- `npm` - downloads project dependencies
- the others are unlisted for now, because this will probably change pretty significantly in the near future

## dependencies

- `bootstrap` - main CSS framework used
- `bootswatch` - themes for the UI

## devDependencies (global)

- `bower` - for the time being, Bower is still necessary


# composer.json

## require

- `php` - this application runs on PHP
- `codeigniter/framework` - the CodeIgniter PHP framework this application is build on
- `dompdf/dompdf` - no description
- `tamtamchik/namecase` - no description
- `paragonie/random_compat` - no description
- `vlucas/phpdotenv` - no description

## require-dev

- `mikey179/vfsstream` - no description
- `phpunit/phpunit` - no description
- `kenjis/ci-phpunit-test` - no description