Server Requirements
-------------------

* PHP version 5.6 to 7.2 are supported. Please note that PHP needs to have `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring` and `php-curl` installed and enabled.

* MySQL 5.5, 5.6 and 5.7 are supported, also MariaDB replacement is supported and apparently offering better performance.

* Apache 2.2 and 2.4 are supported. Also Nginx has been proven to work fine, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Local-Deployment-using-LEMP)

* Raspberry PI based installations proved to work, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Installing-on-Raspberry-PI---Orange-PI-(Headless-OSPOS))

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

From now on ospos can be deployed using Docker on Linux, Mac or Windows. This setup dramatically reduces the number of possible issues as all setup is now done in a Dockerfile. Docker runs natively on mac and linux, but will require more overhead on windows. Please refer to the docker documentation for instructions on how to set it up on your platform.

* To build and run the image, download the latest build from bintray and issue following commands in a terminal with docker installed

```
    docker-compose build
    docker-compose up 
```

* If you want to run from the latest git source, then use docker and composer to run the build

```
  docker run --rm -v $(pwd):/app composer/composer install
  docker run --rm -v $(pwd):/app -w /app lucor/php7-cli php bin/install.php translations develop
  docker run --rm -it -v $(pwd):/app -w /app digitallyseamless/nodejs-bower-grunt sh -c "npm install && bower install"
  docker-compose build
  docker-compose up
```

Cloud install
-------------

If you choose *DigitalOcean* [through this link](https://m.do.co/c/ac38c262507b), you will get a *$10 credit* for a first month. [Check the wiki](https://github.com/opensourcepos/opensourcepos/wiki/DOCS-USERS-Getting-Started-installations#cloud-deploy-installation) for further instructions on how to install the necessary components.
