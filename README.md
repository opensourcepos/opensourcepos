[![Download](https://api.bintray.com/packages/jekkos/opensourcepos/opensourcepos/images/download.svg?version=3.3.2) ](https://bintray.com/jekkos/opensourcepos/opensourcepos/3.3.2/link)
[![Build Status](https://travis-ci.org/opensourcepos/opensourcepos.svg?branch=master)](https://travis-ci.org/opensourcepos/opensourcepos)
[![Join the chat at https://gitter.im/opensourcepos](https://badges.gitter.im/jekkos/opensourcepos.svg)](https://gitter.im/opensourcepos?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![devDependency Status](https://david-dm.org/jekkos/opensourcepos/dev-status.svg)](https://david-dm.org/jekkos/opensourcepos#info=dev)
[![GitHub version](https://badge.fury.io/gh/jekkos%2Fopensourcepos.svg)](https://badge.fury.io/gh/jekkos%2Fopensourcepos)
[![Translation status](http://weblate.jpeelaer.net/widgets/ospos/-/svg-badge.svg)](http://weblate.jpeelaer.net/engage/ospos/?utm_source=widget)


Introduction
------------

Open Source Point of Sale is a web based point of sale system.
The main features are:
* Stock management (Items and Kits with extensible list of Attributes)
* VAT, GST, customer and multi tiers taxation
* Sale register with transactions logging
* Quotation and invoicing
* Expenses logging
* Cashup
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
* GDPR ready

The software is written in PHP language, it uses MySQL (or MariaDB) as data storage back-end and has a simple but intuitive user interface.

The latest 3.x version is a complete overhaul of the original software.
It is now based on Bootstrap 3 using Bootswatch themes, and still uses CodeIgniter 3 as framework.
It also has improved functionality and security.

Deployed to a Cloud it's a SaaS (Software as a Service) solution.

DEMO
----

A demo version of the latest master version can be found on our [Demo server](https://demo.opensourcepos.org). This is a containerized install which will be reinitialized when new functionality is added to the code repository.

LOGIN using
* username: admin
* password: pointofsale


Installation
------------

Please **refrain from creating issues** about installation problems **before having read the FAQ and went through existing github issues**. We have a build pipeline that checks the sanity of our latest repository commit and in case the application itself is broken then our build will be as well.

This application **can be setup in many different ways** and we only **support the ones described in the INSTALL file linked below**.

Read the [INSTALL.md](https://github.com/opensourcepos/opensourcepos/blob/master/INSTALL.md) in our repository.


License
-------

Open Source Point of Sale is licensed under MIT terms with an important addition:

_The footer signature "You are using Open Source Point Of Sale" with version, 
hash and link to the original distribution of the code MUST BE RETAINED, 
MUST BE VISIBLE IN EVERY PAGE and CANNOT BE MODIFIED._

Also worth noting:

_The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software._

For more details please read the file [LICENSE](https://github.com/opensourcepos/opensourcepos/blob/master/LICENSE).

It's important to understand that althought you are free to use the software the copyright stays and the license agreement applies in all cases.
Therefore any actions like:

- Removing LICENSE and any license files is prohibited
- Authoring the footer notice replacing it with your own or even worse claiming the copyright is absolutely prohibited
- Claiming full ownership of the code is prohibited

In short you are free to use the software but you cannot claim any property on it.

Any person or company found breaching the license agreement will have a bunch of monkeys at the door ready to destroy their servers.


Keep the Machine Running
------------------------

If you like the project, and you are making money out of it in some form, then consider buying us a coffee so we can keep adding features.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8)


Language Translations
---------------------

To help us with OSPOS translations please use [Weblate website here](http://translate.opensourcepos.org) and sign up. After registering you can subscribe to different languages and you will be notified once a new translation is added.

Please also read the [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Adding-translations) to find our Translations Guideline.

Only with the help of the community we can keep language translations up to date.


Reporting Bugs
--------------

If you are taking a release candidate code please make sure you always run the latest database upgrade script and you took the latest code from master.
Please DO NOT post issues if you have not completed this step.

- Versions **≥ 3.3.0**:

Please **Copy** the info under **System Info tab in configuration section** in order to give us the required details.

- Versions **< 3.2.3**:

Bug reports must follow the below schema:

1. Ospos **version string with git commit hash** (see ospos footer)
2. OS name and version running your Web Server (e.g. CentOS 6.9, Ubuntu 16.4, Windows 10)
3. Web Server name and version (e.g. Apache 2.2, Apache 2.4, Nginx 1.12, Nginx 1.13)
4. Database name and version (e.g. MySQL 5.5, MySQL 5.6, MySQL 5.7, MariaDB 10.0, MariaDB 10.1, MariaDB 10.2, MariaDB 10.3)
5. PHP version (e.g. 5.6, 7.0, 7.1, 7.2, 7.3)
6. Language selected in OSPOS (e.g. English, Spanish)
7. Any configuration of OSPOS that you changed
8. Exact steps to reproduce the issue (test case)
9. Optionally some screenshots to illustrate each step

If above information is not provided in full, your issue will be tagged as pending.
If missing information is not provided within a week we will close your issue.


FAQ
---

* If you are seeing the message **system folder missing**, then you have cloned the source using git and you need to run a build *first*. Check [INSTALL.md](https://github.com/opensourcepos/opensourcepos/blob/master/INSTALL.md) for instructions or download latest zip file from [bintray](https://bintray.com/jekkos/opensourcepos/opensourcepos/view/files?sort=updated&order=desc#files) instead.

* If at login time you read "The installation is not correct, check your php.ini file.", please check the error_log in public folder to understand what's wrong and make sure you read the [INSTALL.md](https://github.com/opensourcepos/opensourcepos/blob/master/INSTALL.md). To know how to enable error_log, please read the comment in [issue 1770](https://github.com/opensourcepos/opensourcepos/issues/1770#issuecomment-355177943).

* If you installed your OSPOS under a web server subdir, please edit public/.htaccess and go to the lines with comment `if in web root` and `if in subdir comment above line, uncomment below one and replace <OSPOS path> with your path` and follow the instruction on the second comment line. If you face more issues please read [issue #920](https://github.com/opensourcepos/opensourcepos/issues/920) for more help.

* Apache server configurations are SysAdmin issues and not strictly related to OSPOS. Please make sure you first can show a "hello world" html page before pointing to OSPOS public directory. Make sure .htaccess is correctly configured.

* If the avatar pictures are not shown in Items or at Item save time you get an error, please make sure your public and subdirs are assigned to the correct owner and the access permission is set to 755.

* If you install ospos in docker behind a proxy that performs ssloffloading, you can enable the url generated to be https instead of http, by activating the environment variable FORCE_HTTPS = 1.

* If you have suhosin installed and face an issue with CSRF, please make sure you read [issue #1492](https://github.com/opensourcepos/opensourcepos/issues/1492).

* If you see the item edit dialog box empty starting with version 3.3.0, please disable `only_full_group_by` option from MySQL/MariaDB. See issue [#2538](https://github.com/opensourcepos/opensourcepos/issues/2538).

Credits
-------
|JetBrains|Travis CI|
|:-:|:-:|
|![IntelliJ IDEA](https://raw.githubusercontent.com/wiki/j-easy/easy-batch/images/logo/intellijidea-logo.png)|[Travis CI](https://travis-ci.com/images/logos/TravisCI-Full-Color.png)|
|Many thanks to [JetBrains](https://www.jetbrains.com/) for providing a free license of [IntelliJ IDEA](https://www.jetbrains.com/idea/) to kindly support the development of OSPOS|Many thanks to [Travis CI](https://travis-ci.org) for providing a free continuous integration service for open source projects.|
