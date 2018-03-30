[![Build Status](https://travis-ci.org/opensourcepos/opensourcepos.svg?branch=master)](https://travis-ci.org/opensourcepos/opensourcepos)
[![Join the chat at https://gitter.im/jekkos/opensourcepos](https://badges.gitter.im/jekkos/opensourcepos.svg)](https://gitter.im/jekkos/opensourcepos?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![devDependency Status](https://david-dm.org/jekkos/opensourcepos/dev-status.svg)](https://david-dm.org/jekkos/opensourcepos#info=dev)
[![Dependency Status](https://gemnasium.com/badges/github.com/jekkos/opensourcepos.svg)](https://gemnasium.com/github.com/jekkos/opensourcepos)
[![GitHub version](https://badge.fury.io/gh/jekkos%2Fopensourcepos.svg)](https://badge.fury.io/gh/jekkos%2Fopensourcepos)
[![Translation status](http://weblate.jpeelaer.net/widgets/ospos/-/svg-badge.svg)](http://weblate.jpeelaer.net/engage/ospos/?utm_source=widget)


Introduction
------------

A web-base POS application and requiring just a web browser at your point of sale point, that allows you to manage your stock, sales, issuing receipts or invoices and providing you reports of sales and stock.

The main features are:
* Stock management (Items and Kits)
* VAT, customer and multi tiers taxation
* Sale register with transactions logging
* Quotation and invoicing
* Expenses logging
* Receipt and invoice printing and/or emailing
* Barcode generation and printing
* Suppliers and Customers database
* Multiuser with permission control
* Reporting on sales, orders, expenses, inventory status
* Receivings
* Giftcard
* Rewards
* Restaurant tables
* Messaging (SMS)
* Multilanguage
* Selectable Boostrap (Bootswatch) based UI theme
* Mailchimp integration
* reCAPTCHA to protect login page from brute force attacks

OSPOS features evolved in a robust application thanks to active contributors, There's a complete list of features, please visit the OSPOS complete feature data sheet page for more information at https://github.com/opensourcepos/opensourcepos/wiki/OSPOS-complete-feature-datasheet


Documentation and Installation
-------------------

Please refers to the wiki page [DOCS-USERS-Getting-Started-installations](https://github.com/opensourcepos/opensourcepos/wiki/DOCS-USERS-Getting-Started-installations) at https://github.com/opensourcepos/opensourcepos/wiki/DOCS-USERS-Getting-Started-installations OSPOS wiki.

Exact requirements and other flavours like differents operating systems (MAc, Linux, windows, etc) and software (Apache2, Nginx, php5, php7, etc) please refers to the [More advance and customized installations](https://github.com/opensourcepos/opensourcepos/wiki/OSPOS-development-index#requirements) at the OSPOS development index page in https://github.com/opensourcepos/opensourcepos/wiki/OSPOS-development-index#requirement 



PLease take in consideration there two places to download:
1. the stable release at https://github.com/opensourcepos/opensourcepos/releases (first it's lasted)
2. the unstable build at https://bintray.com/package/files/jekkos/opensourcepos/opensourcepos?order=desc&sort=fileLastModified&basePath=&tab=files

The bintray option are correcponding to each commit in the master repository, its a "build" from, to understand this please read the [Development how to start documetation wiki page](https://github.com/opensourcepos/opensourcepos/wiki/OSPOS-development-index#2---how-to-start-develop) at https://github.com/opensourcepos/opensourcepos/wiki/OSPOS-development-index#2---how-to-start-develop.

Theres a special wiki page for docker installations, please be sure to read [DOCS-USER-Extras-for-Docker-cloud-maintenance](https://github.com/opensourcepos/opensourcepos/wiki/DOCS-USER-Extras-for-Docker-cloud-maintenance) at the https://github.com/opensourcepos/opensourcepos/wiki/DOCS-USER-Extras-for-Docker-cloud-maintenance wiki page.

Support and reporting bugs
-------

If you need assistance you can create a Support Ticket Issue by browsing at https://github.com/opensourcepos/opensourcepos/issues/new just few words ahead to your solution! 

It's very important that be carefully to fill all the screen template information first by these steps:

1. specify what installation procedure you was used **assumed there's no modifications** to ospos contents
2. specify place, server, database, php and ospos version you perform install
3. Make sure you read the FAQ, also the wiki pages if your issue was previously resolved or reported

**IF those steps are not property followed, issue will automatically closed and ignored** of course [![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8) may help!


Keep the Machine Running
------------------------

Of course **support has cost** and if you like the project, and you are making money out of it on a daily basis, then **consider buying us a coffee** so we can keep adding features.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8)


License
-------

Open Source Point of Sale is licensed under MIT terms with an important additions:

1. _The footer signature "You are using Open Source Point Of Sale" with version, 
hash and link to the original distribution of the code MUST BE RETAINED, 
MUST BE VISIBLE IN EVERY PAGE and CANNOT BE MODIFIED._

2. _The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software._

For more details please read the file [LICENSE](LICENSE).

So you are free to use the software but the copyright stays and the license agreement applies in all cases; 
any actions like:

- Removing LICENSE and any license files is unnaceptable and break the license as topic 2 points!
- Authoring with your own or even worse claiming the copyright is absolutely prohibited as topic 1 points!
- Claiming full ownership of the code is prohibited as both topics points and LICENSE said!

Language Translations
---------------------

To help us with OSPOS translations please use [Weblate website here](http://translate.opensourcepos.org) and sign up. After registering you can subscribe to different languages and you will be notified once a new translation is added.

Please also read the [OSPOS-development-index / Translations guide](https://github.com/opensourcepos/opensourcepos/wiki/OSPOS-development-index#always-use-translations-event-hardcoded-strings) at the https://github.com/opensourcepos/opensourcepos/wiki/OSPOS-development-index#always-use-translations-event-hardcoded-strings wiki page to find our Translations Guidelines.



FAQ
---

* If at login time you read "The installation is not correct, check your php.ini file.", please check the error_log in public folder to understand what's wrong. Any PHP extension related issue is due to one of the point below.

* If a blank page (HTTP status 500) shows after search completion or receipt generation, then double check `php-gd` presence in your php installation. On windows check in php.ini whether the lib is installed. On Ubuntu issue `sudo apt-get install php5-gd`. Also have a look at the Dockerfile for a complete list of recommended packages.

* If sales and receiving views don't show properly, please make sure BCMath lib (`php-bcmath`) is installed. On windows check php.ini and make sure php_bcmath extension is not commented out.

* If the following error is seen in sales module `Message: Class 'NumberFormatter' not found` then you don't have `php-intl` extension installed. Please check the [wiki](https://github.com/opensourcepos/opensourcepos/wiki/Localisation-support#php5-intl-extension-installation) to resolve this issue on your platform. If you use WAMP, please read [issue #949](https://github.com/opensourcepos/opensourcepos/issues/949).

* If you installed your OSPOS under a web server subdir, please edit public/.htaccess and go to the lines with comment `if in web root` and `if in subdir comment above line, uncomment below one and replace <OSPOS path> with your path` and follow the instruction on the second comment line. If you face more issues please read [issue #920](https://github.com/opensourcepos/opensourcepos/issues/920) for more help.

* If the avatar pictures are not shown in Items or at Item save time you get an error, please make sure your public and subdirs are assigned to the correct owner and the access permission is set to 755.

* If you have problems with the encryption support or you get an error please make sure `php-openssl` is installed. With PHP 7 MCrypt is deprecated so you must use OpenSSL.

* If you install ospos in docker behind a proxy that performs ssloffloading, you can enable the url generated to be https instead of http, by activating the environment variable FORCE_HTTPS = 1.

* If you have suhosin installed and face an issue with CSRF, please make sure you read [issue #1492](https://github.com/opensourcepos/opensourcepos/issues/1492).

* If new customer or supplier fails please make sure `php-mbstring` is installed see [issue #1673](https://github.com/opensourcepos/opensourcepos/issues/1673) for more details.

* Apache server configurations are SysAdmin issues and not strictly related to OSPOS. Please make sure you first can show a "hello world" html page before pointing to OSPOS public directory. Make sure .htaccess is correctly configured.
