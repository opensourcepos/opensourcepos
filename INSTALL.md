Server Requirements
-------------------

* PHP version 5.6 to 7.3 are supported. Please note that PHP needs to have `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring` and `php-curl` installed and enabled.

* MySQL 5.5, 5.6 and 5.7 are supported, also MariaDB replacement is supported and apparently offering better performance. However you need to disable `only_full_group_by` option, see issue [#2538](https://github.com/opensourcepos/opensourcepos/issues/2538).

* Apache 2.2 and 2.4 are supported. Also Nginx has been proven to work fine, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Local-Deployment-using-LEMP).

* Raspberry PI based installations proved to work, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Installing-on-Raspberry-PI---Orange-PI-(Headless-OSPOS)).

* For Windows based installations please read [the wiki](https://github.com/opensourcepos/opensourcepos/wiki) and also existing closed issues as this topic has been covered well in all the variants and issues.


Local install
-------------

First of all, if you're seeing the message **'system folder missing'** after launching your browser, then that means you have cloned the repository and have not built the project properly.

1. Dowload the latest [stable release](https://github.com/opensourcepos/opensourcepos/releases) from github or [unstable build](https://bintray.com/jekkos/opensourcepos/opensourcepos/view/files?sort=updated&order=asc#files) from bintray. A regular repository clone will not work unless you are brave enough to build the whole project!
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

From now onwards OSPOS can be deployed using Docker on Linux and Mac, locally or on a host (server).
This setup dramatically reduces the number of possible issues as all setup is now done in a Dockerfile.
Docker runs natively on Mac and Linux. Please refer to the docker documentation for instructions on how to set it up on your platform.

Since OSPOS version 3.3.0 the docker installation offers a reverse proxy based on nginx with a (if local) Self signed certificate termination (aka HTTPS connection).
Behind the reverse proxy you can access OSPOS using https (port 443) and myPhpAdmin using port 8000.
Port 80 (standard http) is not available for OSPOS, it's only available for a cert manager service in case of server installation.

* To build and run the image, download the latest build from bintray.
* Install envsubst from https://github.com/a8m/envsubst on your machine
* Issue the following commands in a terminal with docker installed:

```
    docker/install-local.sh
```

* When required to renew a certificate say (y)es.
* When the script has terminated to run, wait about a minute before connecting to https://127.0.0.1.
* The web browser will warn you of a self certificate exception, accept and continue
* If you do https://127.0.0.1:8000 (port 8000) instead, you would be able to access a phpMyAdmin service connected to OSPOS MariaDB

* To stop the docker issue the following command:

```
    docker/uninstall.sh
```


Host install using Docker
-------------------------

Since OSPOS version 3.3.0 the docker installation offers a reverse proxy based on nginx with a Letsencrypt TLS certificate termination (aka HTTPS connection).
Letsencrypt is a free certificate issuer, requiring a special installation that this docker installation would take care for you.
Any Letsencrypt TLS certificate renewal will be managed automatically for you, therefore there is no need to worry about those details.

Before starting your installation, you would need to edit docker/.env file and configure it to contain the correct MySQL/MariaDB and phpMyAdmin passwords (don't use the defaults!).
You will also need to register to Letsencrypt and configure your host domain name, Letsencrypt email address in docker/.env file.
The variable STAGING needs to be set to 0 when you are confident your configuration is correct so that Letsencrypt will issue a final proper TLS certificate.

Follow local install steps, but instead of 

```
    docker/install-local.sh
```

use

```
    docker/install-server.sh
```

Do not use 

```
    docker/uninstall.sh
```

on live deployments unless you want to tear down everything because all your disk content will be wiped out!


Cloud install
-------------

If you choose *DigitalOcean*:
[Through this link](https://m.do.co/c/ac38c262507b), you will get a *$50 credit* for a first month. [Check the wiki](https://github.com/opensourcepos/opensourcepos/wiki/DOCS-USERS-Getting-Started-installations#cloud-deploy-installation) for further instructions on how to install the necessary components.


cPanel & SSH Install
--------------------

If you own on a **VPS**, **Dedicated Server**, or **Shared Hosting** running on **cPanel** with **SSH** access:

You can run our Stand-alone [WS-OSPOS-Installer](https://github.com/WebShells/WS-OSPOS-Installer.git), it will handle:


. Database.php config files generation.

. Creation of db User & Password depending on user's input of Dbname, Username, Password, & Hostname ( No need for phpmyadmin )

. Imports default Db SQL files in order to run the project.

Usage in **(SSH)**:

git clone https://github.com/WebShells/WS-OSPOS-Installer.git

chmod +x WS-OSPOS-Installer/Get-POS 

./WS-OSPOS-Installer/Get-POS 

or

wget https://github.com/WebShells/WS-OSPOS-Installer/archive/master.zip

unzip -qq master.zip

chmod +x WS-OSPOS-Installer-master/Get-POS

./WS-OSPOS-Installer-master/Get-POS

Answer **DB required questions** and you are ready to run the project on http://localhost/OSPOS/public (localhost to be replaced by the hostname provided during setup).
