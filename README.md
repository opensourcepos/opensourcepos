Introduction
------------

Open Source Point of Sale is a web based point of sale system written in the PHP language.
It uses MySQL as the data storage back-end and has a simple user interface.

This is the latest version 3.0.0 and it's based on Bootstrap 3 using Bootswatch theme Flatly as default, and CodeIgniter 3.0.6.

Badges
------
[![Build Status](https://travis-ci.org/jekkos/opensourcepos.svg?branch=master)](https://travis-ci.org/jekkos/opensourcepos)
[![Join the chat at https://gitter.im/jekkos/opensourcepos](https://badges.gitter.im/jekkos/opensourcepos.svg)](https://gitter.im/jekkos/opensourcepos?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![devDependency Status](https://david-dm.org/jekkos/opensourcepos/dev-status.svg)](https://david-dm.org/jekkos/opensourcepos#info=devDependencie)
[![Dependency Status](https://gemnasium.com/badges/github.com/jekkos/opensourcepos.svg)](https://gemnasium.com/github.com/jekkos/opensourcepos)
[![GitHub version](https://badge.fury.io/gh/jekkos%2Fopensourcepos.svg)](https://badge.fury.io/gh/jekkos%2Fopensourcepos)

Keep the Machine Running
------------------------
If you like the project, and you are making money out of it on a daily basis, then consider buying me a coffee so I can keep adding features.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8)

Server Requirements
-------------------
PHP version 5.5 or newer is recommended but PHP 7.x is not fully supported yet.

Reporting Bugs
--------------
Since OSPOS 3.0.0 is a version under development, please make sure you always run the latest 2.4_to_3.0.sql database upgrade script.
Please DO NOT post issues if you have not done that before running OSPOS 3.0.
Please also make sure you have updated all the files from latest master.

Bug reports must follow this schema:

1. Ospos **version string with git commit hash** (see ospos footer)
2. OS name and version running your Web Server (e.g. Linux Ubuntu 15.0)
3. Web Server name and version (e.g. Apache 2.4)
4. Database name and version (e.g. =< MySQL 5.6)
5. PHP version (e.g. PHP 5.5)
6. Language selected in OSPOS (e.g. English, Spanish)
7. Any configuration of OSPOS that you changed
8. Exact steps to reproduce the issue (test case)
9. Optionally some screenshots to illustrate each step

If above information is not provided in full, your issue will be tagged as pending.
If missing information is not provided within a week we will close your issue.

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
5. Create a new node on DigitalOcean through the `Infrastructure > Nodes` tab. Fill in a name (ospos) and choose a region near to you
6. Click [![Deploy to Docker Cloud](https://files.cloud.docker.com/images/deploy-to-dockercloud.svg)](https://cloud.docker.com/stack/deploy/?repo=https://github.com/jekkos/opensourcepos) 
7. Othewise create a new stack under `Applications > Stacks` and paste the [contents of docker-cloud.yml](https://github.com/jekkos/opensourcepos/blob/master/docker-cloud.yml) from the source repository in the text field and hit `Create and deploy` 
8. Find your website url under `Infrastructure > Nodes > <yournode> > Endpoints > web`
9. Login with default username/password admin/pointofsale
10. DNS name for this server can be easily configured in the DigitalOcean control panel

Local install
-------------
1. Create/locate a new mysql database to install open source point of sale into
2. Execute the file database/database.sql to create the tables needed
3. unzip and upload Open Source Point of Sale files to web server
4. Copy application/config/database.php.tmpl to application/config/database.php
5. Modify application/config/database.php to connect to your database
6. Modify application/config/config.php encryption key with your own
7. Go to your point of sale install via the browser
8. LOGIN using
username: admin 
password:pointofsale
9. Enjoy

Local install using Docker
--------------------------
From now on ospos can be deployed using Docker on Linux, Mac or Windows. This setup dramatically reduces the number of possible issues as all setup is now done in a Dockerfile. Docker runs natively on mac and linux, but will require more overhead on windows. Please refer to the docker documentation for instructions on how to set it up on your platform.

To build and run the image, issue following commands in a terminal with docker installed

    docker-compose build
    docker-compose up 

FAQ
---
* If a blank page (HTTP status 500) shows after search completion or receipt generation, then double check php5-gd presence in your php installation. On windows check in php.ini whether the lib is installed. On Ubuntu issue `sudo apt-get install php5-gd`. Also have a look at the Dockerfile for a complete list of recommended packages.

* If the following error is seen in sales module `Message: Class 'NumberFormatter' not found` then you don't have `php5-intl` extension installed. Please check the [wiki](https://github.com/jekkos/opensourcepos/wiki/Localisation-support#php5-intl-extension-installation) to resolve this issue on your platform.

* You are getting following error `Message: Can't use method return value in write context` saying that you are probably using PHP7 which is not completely supported yet. Check your hosting configuration to verify whether you have a supported PHP version installed

