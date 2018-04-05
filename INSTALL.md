# Install instructions

This document are made for quick deploy/usage, for detailed insformation you can read:

* [Installation and requirements data sheet](OSPOS-development-index#tech-installation) 
* [OSPOS installations index list pages](OSPOS-development-index#1---officially-supported)

Here we will cover the following procedure cases:

* [Local Deploy installation](#local-deploy-install)
* [Local Docker installation](#local-docker-install)
* [Cloud Deploy installation](#cloud-deploy-installation)

# Local Deploy install
----------------------

Its the best option to most customized and controlled, as counterpart you are on your own.

1. **Requisites**: install apache2, php (with openssl/mcryp, curl and mysqln modules package also) and mysql-server, 
this can vary from OS, by example there's a XAMP package for MACOSX and most linux distributions used package names
2. **Download**: there's two options, the ready to use stable release or bintray daily build, 
the stable can be download at https://github.com/opensourcepos/opensourcepos/releases 
and the bintray can be download at https://bintray.com/jekkos/opensourcepos/opensourcepos/view/files 
3. **Moveit** the downloaded file to the htdocs root webserver document directory, this depends of the OS and apache2, 
by example in a linux distribution may be at `/var/www/html` please consult your Apache2 installation.
4. **Uncompress** depends of the file, if you downloaded the "zip" flavor, install `unzip` package 
and run `unzip opensourcepos*.zip`
5. **Open** terminal `Command` prompt (on M$) or from left corner Menu, as `Menu`->`System tools`->`Terminal` (if Linux) or as `Finder`->`Commands`->`Terminal` (if MAcOS), 
an a window will raised with black background for typing commands.
6. **Gain** access to administration my be need in linux, this generally are done by user `root` 
by executing in the `Terminal` that command: `sudo su`, it's strong recommended to maximize the terminal window 
to see more clear the messages.
7. **Create** the database and access to that database, in most commoncases by executing in that terminal 
this command: `mysql -u root -e "CREATE SCHEMA ospos;CREATE USER 'admin'@'%' IDENTIFIED BY 'pointofsale';GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' IDENTIFIED BY 'pointofsale' WITH GRANT OPTION;"`
8. **Populate** the database with the SQL scripts, using that other command in same terminal command black window running 
this command: `mysql -u root ospos < /var/www/html/application/database/database.sql`
9. **Browsing** using the web browser and run from `http://localhost/public` or better `http://127.0.0.1/public` 
10. **Login** by using username as **admin**  and the password are **pointofsale** and then enjoy the software.

Now next to [DOCS USERS: Getting Started Usage](DOCS-USERS-Getting-Started-usage)

**IMPORTANT** first login will have a delay due "send statics" are activated in pre 3.2.0 releases, can be partially `"Office->Store config->General->Send statics"`, deactivate when login if you not have enough network or internet.

# Local Docker install
-------------

Docker cloud instances can be directly maintained through the docker-cloud command tool. Most of the basic operations are available from the docker-cloud web frontend.

**NOTE** This its not end-user oriented task, need some level of knowledge specially in unix-linux like operating systems.

To build and run the image, download a prebuilt zip from bintray (as from https://bintray.com/package/files/jekkos/opensourcepos/opensourcepos?order=desc&sort=fileLastModified&basePath=&tab=files) and then launch docker-compose from that folder which should work right away by issue following commands in a terminal with docker installed

    docker-compose build
    docker-compose up 

If you want to build from source (as cloning the github repository), use docker first to minify javascript
 
 ``` bash
  docker run --rm -v $(pwd):/app composer/composer install
  docker run --rm -v $(pwd):/app -w /app lucor/php-cli php bin/install.php translations develop
  docker run --rm -it -v $(pwd):/app -w /app digitallyseamless/nodejs-bower-grunt "sh -c npm install && bower install"
```

# Cloud Deploy installation
-------------

If you want to run a quick demo of OSPOS the most quick way its using [`DigitalOcean` (click here)](https://m.do.co/c/ac38c262507b), remenber also read [Extras for Docker cloud maintenance](DOCS-USER-Extras-for-Docker-cloud-maintenance) and take in consideration that a docker maintenance need some level of linux related operating system usage.

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

Now next to [DOCS USERS: Getting Started Usage](DOCS-USERS-Getting-Started-usage)

**IMPORTANT** first login will have a delay due "send statics" are activated this only happened at the first login.

More info in the wiki page [Extras for Docker cloud maintenance](DOCS-USER-Extras-for-Docker-cloud-maintenance) must be read.

# More advance and customized installations

Please refers to the [OSPOS installation development requirements wiki page (click here)](OSPOS-development-index#requirements) for complete info about installations

## See also:

* [Getting Started with Open Source POS](home)
  * [OSPOS Feature datasheet and usage](OSPOS-complete-feature-datasheet)
  * [DOCS USERS: Getting Started Usage](DOCS-USERS-Getting-Started-usage)
  * [OSPOS Printing general info](DOCS-USERS-for-OSPOS-Printing)
* [Extras for Docker cloud maintenance](DOCS-USER-Extras-for-Docker-cloud-maintenance)
