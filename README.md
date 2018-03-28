[![Build Status](https://travis-ci.org/opensourcepos/opensourcepos.svg?branch=master)](https://travis-ci.org/opensourcepos/opensourcepos)
[![Join the chat at https://gitter.im/jekkos/opensourcepos](https://badges.gitter.im/jekkos/opensourcepos.svg)](https://gitter.im/jekkos/opensourcepos?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![devDependency Status](https://david-dm.org/jekkos/opensourcepos/dev-status.svg)](https://david-dm.org/jekkos/opensourcepos#info=dev)
[![Dependency Status](https://gemnasium.com/badges/github.com/jekkos/opensourcepos.svg)](https://gemnasium.com/github.com/jekkos/opensourcepos)
[![GitHub version](https://badge.fury.io/gh/jekkos%2Fopensourcepos.svg)](https://badge.fury.io/gh/jekkos%2Fopensourcepos)
[![Translation status](http://weblate.jpeelaer.net/widgets/ospos/-/svg-badge.svg)](http://weblate.jpeelaer.net/engage/ospos/?utm_source=widget)


Introduction
------------

Open Source Point of Sale is a web based point of sale system.
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

The software is written in PHP language, it uses MySQL (or MariaDB) as data storage back-end and has a simple but intuitive user interface.

The latest 3.x version is a complete overhaul of the original software.
It is now based on Bootstrap 3 using Bootswatch themes, and still uses CodeIgniter 3 as framework.
It also has improved functionality and security.

Deployed to a Cloud it's a SaaS (Software as a Service) solution.


License
-------

Open Source Point of Sale is licensed under MIT terms with an important addition:

_The footer signature "You are using Open Source Point Of Sale" with version, 
hash and link to the original distribution of the code MUST BE RETAINED, 
MUST BE VISIBLE IN EVERY PAGE and CANNOT BE MODIFIED._

Also worth noting:

_The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software._

For more details please read the file __LICENSE__.

It's important to understand that althought you are free to use the software the copyright stays and the license agreement applies in all cases.
Therefore any actions like:

- Removing LICENSE and any license files is prohibited
- Authoring the footer notice replacing it with your own or even worse claiming the copyright is absolutely prohibited
- Claiming full ownership of the code is prohibited

In short you are free to use the software but you cannot claim any property on it.

Any person or company found breaching the license agreement will be chased up.


Keep the Machine Running
------------------------

If you like the project, and you are making money out of it on a daily basis, then consider buying me a coffee so I can keep adding features.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8)


Server Requirements
-------------------

PHP version 5.6 to 7.2 is recommended. Please note that PHP needs to have `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring` and `php-curl` installed and enabled.

MySQL 5.5, 5.6 and 5.7 are supported, also MariaDB replacement is supported and apparently offering better performance.

Apache 2.2 and 2.4 are supported. Also Nginx has been proven to work fine, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Local-Deployment-using-LEMP)

Raspberry PI based installations proved to work, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Installing-on-Raspberry-PI---Orange-PI-(Headless-OSPOS))

For Windows based installations please read [the wiki](https://github.com/opensourcepos/opensourcepos/wiki) and also existing closed issues as this topic has been covered well in all the variants and issues.


Local install
-------------

1. Dowload the latest [stable release](https://github.com/opensourcepos/opensourcepos/releases) from github or [unstable build](https://bintray.com/jekkos/opensourcepos/opensourcepos/view/files?sort=updated&order=asc#files) from bintray
2. Create/locate a new mysql database to install open source point of sale into
3. Execute the file database/database.sql to create the tables needed
4. unzip and upload Open Source Point of Sale files to web server
5. Modify application/config/database.php and modify credentials if needed to connect to your database
6. Modify application/config/config.php encryption key with your own
7. Go to your point of sale install public dir via the browser
8. LOGIN using
  * username: admin 
  * password: pointofsale
9. Enjoy
10. Oops an issue? Please make sure you read the FAQ, wiki page and you checked open and closed issue on GitHub. PHP display_errors is disabled by default. Create an application/config/.env file from the .env.example to enable it in a development environment. 


Local install using Docker
--------------------------

From now on ospos can be deployed using Docker on Linux, Mac or Windows. This setup dramatically reduces the number of possible issues as all setup is now done in a Dockerfile. Docker runs natively on mac and linux, but will require more overhead on windows. Please refer to the docker documentation for instructions on how to set it up on your platform.

To build and run the image, issue following commands in a terminal with docker installed

    docker-compose build
    docker-compose up 


Cloud install
-------------

A quick option would be to install directly to [Digitalocean](https://m.do.co/c/ac38c262507b) using their preconfigured LAMP stack. 
Create a DO account first, add a droplet with preconfigured LAMP and follow the instructions for Local Install below. You will be running a provisioned VPS within minutes.


Cloud install using Docker
--------------------------

If you want to run a quick demo of ospos or run it permanently in the cloud, then we
suggest using Docker cloud together with the DigitalOcean hosting platform. This way all the
configuration is done automatically and the install will just work. 

If you choose *DigitalOcean* [through this link](https://m.do.co/c/ac38c262507b), you will get a *$10 credit* for a first
month of uptime on the platform. A full setup will only take about 2 minutes by following steps below.

1. Create a [Digitalocean account](https://m.do.co/c/ac38c262507b)
2. Create a [docker cloud account](https://cloud.docker.com)
3. Login to docker cloud
4. Associate your docker cloud account with your previously created digital ocean account under settings
5. Create a new node on DigitalOcean through the `Infrastructure > Nodes` tab. Fill in a name (ospos) and choose a region near to you. We recommend to choose a node with minimum 1G RAM for the whole stack
6. Click [![Deploy to Docker Cloud](https://files.cloud.docker.com/images/deploy-to-dockercloud.svg)](https://cloud.docker.com/stack/deploy/?repo=https://github.com/opensourcepos/opensourcepos) 
7. Othewise create a new stack under `Applications > Stacks` and paste the [contents of docker-cloud.yml](https://github.com/opensourcepos/opensourcepos/blob/master/docker-cloud.yml) from the source repository in the text field and hit `Create and deploy` 
8. Find your website url under `Infrastructure > Nodes > <yournode> > Endpoints > web`
9. Login with default username/password admin/pointofsale
10. DNS name for this server can be easily configured in the DigitalOcean control panel

More info [on maintaining a docker](https://github.com/opensourcepos/opensourcepos/wiki/Docker-cloud-maintenance) install can be found on the wiki


Language Translations
---------------------

To help us with OSPOS translations please use [Weblate website here](http://translate.opensourcepos.org) and sign up. After registering you can subscribe to different languages and you will be notified once a new translation is added.

Please also read the [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Adding-translations) to find our Translations Guideline.

Only with the help of the community we can keep language translations up to date.


Reporting Bugs
--------------

If you are taking a release candidate code please make sure you always run the latest database upgrade script and you took the latest code from master.
Please DO NOT post issues if you have not done those step.

Bug reports must follow this schema:

1. Ospos **version string with git commit hash** (see ospos footer)
2. OS name and version running your Web Server (e.g. Linux Ubuntu 16.04)
3. Web Server name and version (e.g. Apache 2.4)
4. Database name and version (e.g. MySQL 5.6)
5. PHP version (e.g. PHP 5.6)
6. Language selected in OSPOS (e.g. English, Spanish)
7. Any configuration of OSPOS that you changed
8. Exact steps to reproduce the issue (test case)
9. Optionally some screenshots to illustrate each step

If above information is not provided in full, your issue will be tagged as pending.
If missing information is not provided within a week we will close your issue.


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
