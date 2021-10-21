[unreleased]: https://github.com/opensourcepos/opensourcepos/compare/3.3.5...HEAD
[3.3.5]: https://github.com/opensourcepos/opensourcepos/compare/3.3.4...3.3.5
[3.3.4]: https://github.com/opensourcepos/opensourcepos/compare/3.3.3...3.3.4
[3.3.3]: https://github.com/opensourcepos/opensourcepos/compare/3.3.2...3.3.3
[3.3.2]: https://github.com/opensourcepos/opensourcepos/compare/3.3.1...3.3.2
[3.3.1]: https://github.com/opensourcepos/opensourcepos/compare/3.3.0...3.3.1
[3.3.0]: https://github.com/opensourcepos/opensourcepos/compare/3.2.3...3.3.0
[3.2.3]: https://github.com/opensourcepos/opensourcepos/compare/3.2.2...3.2.3
[3.2.2]: https://github.com/opensourcepos/opensourcepos/compare/3.2.1...3.2.2
[3.2.1]: https://github.com/opensourcepos/opensourcepos/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/opensourcepos/opensourcepos/compare/3.1.1...3.2.0
[3.1.1]: https://github.com/opensourcepos/opensourcepos/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/opensourcepos/opensourcepos/compare/3.0.2...3.1.0
[3.0.2]: https://github.com/opensourcepos/opensourcepos/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/opensourcepos/opensourcepos/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/opensourcepos/opensourcepos/compare/2.4.0...3.0.0
[2.4.0]: https://github.com/opensourcepos/opensourcepos/compare/2.3.4...2.4.0
[2.3.4]: https://github.com/opensourcepos/opensourcepos/compare/2.3.3...2.3.4
[2.3.3]: https://github.com/opensourcepos/opensourcepos/compare/2.3.2...2.3.3
[2.3.2]: https://github.com/opensourcepos/opensourcepos/compare/2.3.1...2.3.2
[2.3.1]: https://github.com/opensourcepos/opensourcepos/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/opensourcepos/opensourcepos/compare/2.2.2...2.3.0

# Changelog

All notable changes to this project will be documented here.

## [Unreleased]

List of changes in the `master` branch.

- N/A

## [3.3.5] - 2021-08-26

- Translation updates (Romanian, Ukrainian, Vietnamese, Thai, Polish, Swedish, Portuguese, Arabic, French, Chinese, Dutch, Tamil, Turkish, Spanish)
- New responsive login page based on Bootstrap `5`
- Translation fallback to English when a string is untranslated for the selected language
- Database and performance optimizations
- Grunt/CI updates
- CSV item import improvements

### Fixes

- Username verification fix on employee insert/update
- Minor report fixes
- Attribute encoding fix
- Decimal render fix
- Fixes for Docker to make it run on Windows
- Blind SQL injection fix

## [3.3.4] - 2021-04-18

- Translation updates (Hungarian, Indonesian, Bosnian, Ukrainian, Vietnamese, Spanish)
- Prevent data wipeout when calling GET directly on the save endpoint
- Cleanup `.htaccess`
- Docker compose usability improvements
- Cookie secure flag fix for Chrome (you can enable CSRF protection again now)
- Use LONGBLOB for session storage. This should fix issues preventing a user from adding a large number of items to register
- Cash rounding bugfixes
- Fix daily overview cash sale totals
- Show sale count in the transaction report
- Button disable to prevent double submission
- Add barcode field to item kits
- Fix discount register parsing in some specific locales

## [3.3.3] - 2020-12-31

- PHP `7.4` support
- Set PHP `7.2` to be the minimum level due to older version deprecations
- Added email CC and BCC (see `config/email.php`)
- Cash rounding to nearest 5 cents
- Updated composer packages and JS plugins
- Improved security (CSRF protection)
- Various small improvements and bug fixes

## [3.3.2] - 2020-09-02

- Fixed `only_full_group_by` issue with MySQL/MariaDB
- Fixed POS transaction return failure if items were deleted
- Various bug fixes

## [3.3.1] - 2019-12-04

- Various bug fixes (please disable `only_full_group_by` option from MySQL/MariaDB to avoid issues)

## [3.3.0] - 2019-09-29

- New logo
- Upgrade CodeIgniter to version `3.1.11`
- PHP `7.3` support
- Attributes feature (allows extensibility of items replacing old custom fields)
- India GST tax support + various tax support improvements
- Cash up feature
- Temporary items feature
- Fixed sales discount
- Supplier category feature
- Improved items import and CSV file generation (to contain additional attributes)
- Improved Docker installation with NGINX reverse proxy using Let's Encrypt TLS certificate
- Database performance improvements
- Added and udated translations
- Fixed various reports issues
- Fixed rounding issues
- Fixed CSRF issues
- Fixed database upgrade script issues
- Various bug fixes

## [3.2.3] - 2018-06-13

- Upgrade CodeIgniter to version `3.1.9`
- Further revert of CSRF change causing regression

## [3.2.2] - 2018-06-06

- Revert CSRF change causing regression

## [3.2.1] - 2018-06-04

- Support for GDPR
- CSRF simplifications
- Translation upgrades
- Various bug fixes

## [3.2.0] - 2018-04-14

- Upgrade CodeIgniter to version `3.1.8`
- PHP `7.2` support (use OpenSSL and not MCrypt)
- Automatic database upgrades from `3.0.0` at first login (no more SQL scripts)
- Home and (back)office menu switch (top menu can be organized in two views)
- Expenses feature
- Quote and work order features
- Improved invoice support
- Sale suspend, soft delete, complete as the state not as different tables or hard delete
- Restore deleted sales
- Improved item kits
- Export tables all records and export to PDF
- Table sticky header (headers visible during scrolling)
- Allow duplicate barcodes (config option)
- Search suggestion formatting (config option)
- Define print and email checkboxes behavior (config option)
- Edit customer from sales register
- Added and updated translations
- Various jQuery plugins upgrade
- Fixed permission issues (e.g. password change)
- Fixed various reports issues and renamed Sales to Transactions
- Various bug fixes (e.g. tax, rounding, library circular dependency)

## [3.1.1] - 2017-09-09

- Updated en-US and en-GB translations, better grammar, and consistency
- Fixed database migration issue with VAT tax included
- Fixed database backup bug
- Fixed gift card error
- Fixed database `upgrade to 3.1.x` script (now it's to `3.1.1` and there is no `3.1.0` anymore)
- Fixed old database upgrade scripts for people upgrading from `2.x` versions
- Fixed `.htaccess` file in OSPOS root dir (it was not forwarding to `public` subdir)
- Fixed few jQuery `2.0` upgrade issues

## [3.1.0] - 2017-09-02

- MySQL `5.7` and PHP `7.x` support
- Advanced tax support with customer tax categories and more
- Better horeca use case support with dinner table sale tagging
- Customer rewards support
- Added quote support and better invoice support
- Added integration with Mailchimp to connect customer list with Mailchimp list
- Prevent inserting two customers with the same email address
- Customer total spending and stats
- Added Google reCAPTCHA option for the login page to increase protection from brute force attacks
- Added due payment for credit sale support
- Gift card numbering with two options: series and random
- Extended item kits functionality
- Employees are allowed to change their own password by clicking their name in the top bar
- Cash rounding support, extended decimals
- Reworked item pictures, file names, and storing
- Financial year start date and selection from date range pickers
- Date time range filters can be date and time or date only
- Added two new Bootswatch themes
- Receipts font size support
- Fix automatically people's name first capital letter, emails in lower case only
- Fixes to Receiving
- Various amendments to database script updates from older versions
- Added dotenv support
- Updates to language translations (split English to American English and British English)
- Various Dockers support improvements
- Minor bugfixes

## [3.0.2] - 2016-12-31

- Fixed error when performing scans multiple times in a row
- Fixed summary reports
- Protect employee privacy by printing just the first letter of the family name
- Updates to language translations
- Various Dockers support improvements
- Minor bugfixes

## [3.0.1] - 2016-11-27

- Upgrade CodeIgniter to version `3.1.2`
- Substantial database performance improvements
- Improved security: email and SMS passwords encryption, removed `phpinfo.php`
- Set code to be production and not development in `index.php`
- Reports improvements, fixed table sorting, tax calculation and made profit to be net profit
- Better Apache `2.4` support in `.htaccess`
- Updates to language translations
- Fixed excel template download links
- Fixed employee name in sale receipt and invoice reprinting
- Fixed `2.3.2_to_2.3.3.sql` database upgrade script mistake
- Fixed `phppos to ospos` database migration script
- Minor bug fixes and some general code clean up

## [3.0.0] 2016-10-21

- Upgrade CodeIgniter to version `3.1.0`
- Major UI overhaul based on Bootstrap `3.0` and Bootswatch Themes
- New tabular views with advanced filtering using Bootstrap Tables
- New graphical reports with no more Adobe Flash dependency
- Redesign of all modal dialogs
- Updated Sales register with simplified payment flow
- Improved security: MySQL injection, XSS, CSFR, BCrypt password encryption, safer project layout
- Support for text messaging (interfacing to specific support required)
- Email configuration
- Improved Localisation support
- Improved Store Config page
- Docker container ready for cloud installation
- Composer PHP support
- More languages and integration with Weblate for continuous translation
- About 280 closed issues under `3.0.0` release label, too many to produce a meaningful list
- Various code cleanup, refactoring, optimization and etc.

## [2.4.0] - 2016-04-02

- Upgrade CodeIgniter to version `3.0.5`
- Fix for spurious logouts
- Apache `.htaccess` `mod_expiry` caching and security optimizations
- Bulk item edit fixes (category, tax, and supplier fields)
- Remove f-key shortcuts used for module navigation
- Allow using custom invoice numbers when suspending a sale
- PHP `7` fixes
- Specific warnings to distinguish between reorder level and out of stock situation in sales
- Fix malware detection issues due to usage of `base64` encoding for storing session variables
- Improve language generation scripts (use PHP builtin functionality)
- Add extra buttons for navigation and printing to receipt and invoice
- Improve print layout for invoices
- Make layout consistent for items between receipt and invoice templates
- Minor bugfixes

## [2.3.4] - 2016-02-08

- Migration script fixes
- Improved continuous integration setup
- More integration tests
- Virtualized container setup (`docker install`)
- Live clock functionality and favicon
- Improved PHP `7` compatibility
- Added de_CH (German) as language
- Minor code cleanup
- Removal of annoying backup prompt on logout

## [2.3.3] - 2016-01-05

- Item kit fixes (search, list, ...)
- Add date picker widgets in sale/receiving edit forms
- Add date filter in items module
- Add barcode generation logic for EAN8, EAN13
- Add barcode validation and fallback logic for EAN8, EAN13
- New config option to generate barcodes if `item_number` is empty
- Add cost and count to inventory reports
- Gift card fixes
- Refactor sales overview (added date filtering + search options)
- Better locale config support
- Improve PHP compatibility
- Fix invoice numbering bug on suspending a sale
- Add configurable locale-dependent date format
- Add grunt-cache-breaker plugin
- Suspend button appears before adding a payment
- Searching of deleted items, filtering part is removed
- Remove infamous `0` after leaving sale or receiving comments empty
- Add SQL script to clean zeroes in sales/receivings comments
- Numerous other bug fixes

## [2.3.2] - 2015-07-15

- Nominatim (OpenStreetMap) customer address autocompletion
- Sale invoice templating
- Configurable barcode generation for items
- Stock location filtering in detailed sales and receivings reports
- Gift cards fixes
- Proper pagination support for most modules
- Language updates
- Fix for decimal tax rates
- Add gender and company name attributes to customer
- Stock location config screen refactor
- Basic Travis CI and PhantomJS setup
- Database backup on admin logout
- Modifiable item thumbnails
- Email invoice PDF generation using DomPDF
- Modifiable company logo
- jQuery upgrade (`1.2` -> `1.8.3`)
- JavaScript minification (using Grunt)
- Numerous bugfixes

## [2.3.1] - 2015-02-11

- Extra report permissions (this includes a refactoring of the database model - new grants table)
- Tax inclusive/exclusive pricing
- Receivings amount multiplication (can be configured in items section)
- Customizable sale and receiving numbering
- Gift card improvements
- Fix item import through CSV
- Bug fixes for reports

## [2.3.0] - 2014-08-19

- Support for multiple stock locations

## 2.2.2 - 2014-08-19

- French language added
- Thai language added
- Upgrade CodeIgniter to version `2.2.0`
- Database types for amounts all changed to decimal types (this will fix rounding errors in the sales and receivings reports)
- Fix duplicated session cookies in HTTP headers (this broke the application when running on Nginx)

## 2.1.1

- Barcodes on the order receipt were not generated correctly
- Sales edit screen for detailed sales reports is now available with ThickBox as in the rest of the application
- Indonesian language files updated (Oktafianus)
- Default language set to `en` in `config.php`
- Fixed some CSS bugs in the suspended sales section
- Default cookie `sess_time_expire` set to `86400` (24h)

## 2.1.0

- Various upgrades, too numerous to list here
- Removed dependency on ofc upload library due to vulnerability found

## 2.0.2

- Fixed multiple gift cards issue per Bug #4 reported on Sourceforge where a second gift card added would have its balance set to `0` even if the sale did not require the total of the second gift card to pay the remaining amount due
- Small code cleanup

## 2.1.0

- Upgrade CodeIgniter to version `2.1.0`
- Various small improvements
