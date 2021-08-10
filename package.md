# package.json readme

## scripts

`npm run scriptname`

- clean - wipes the dependencies downloaded by Composer and npm
  `grunt clean:composer & grunt clean:npm`
- copy - copies the dependencies downloaded by Composer and npm to the public folder
  `grunt clean:ui & grunt clean:licenses & grunt copy & npm run gen-licenses`
- install - runs the Composer install command after installing the npm dependencies
  `composer install`
- full - runs the `reset`, `copy` and `prettier` commands
  `npm run reset & npm run copy & npm run prettier`
- gen-licenses - generates the license files
  `npm ls --prod --json > public/license/npm-prod.licenses & npm ls --dev --json > public/license/npm-dev.licenses & composer licenses --format=json > public/license/composer.licenses`
- prettier - runs Prettier in the public CSS and JS folders
  `prettier --write public/css/_.css & prettier --write public/js/_.js`
- reset - the dependencies downloaded by Composer and npm are wiped, then re-downloaded and updated if needed
  `npm run clean & npm install & npm run install & npm run update`
- update - updates Composer and the dependencies downloaded by Composer and npm
  `npm update & composer self-update & composer update`

## devDependencies

- grunt - used for copying files downloaded by npm
- grunt-contrib-clean - extension to clean files before copying
- grunt-contrib-copy - extension to copy files downloaded by npm
- npm - downloads project dependencies
- prettier - use prettier to format css and js

## dependencies

- bootstrap - main css framework used
- bootstrap-icons - icons used in the ui
- bootstrap-select - used for putting icons in select elements
- bootstrap-table - advanced table management
- bootswatch - themes for the ui
- clipboard - used in configuration > system info to copy the text
- elegant-circles - main navigation icons
- jasny-bootstrap - used in configuration > information for the logo upload
- jquery - needed for jasny-bootstrap and bootstrap-select

## devDependencies (future use?)

- eslint - used for linting js
- stylelint - used for linting css
- stylelint-config-standard - default stylelint config
