Server Requirements
-------------------

* PHP version 5.6 to 7.2 is recommended. Please note that PHP needs to have `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring` and `php-curl` installed and enabled.

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

A quick option would be to install directly to [Digitalocean](https://m.do.co/c/ac38c262507b) using their preconfigured LAMP stack. 
Create a DO account first, add a droplet with preconfigured LAMP and follow the instructions for Local Install below. You will be running a provisioned VPS within minutes.


Cloud install using Docker 
--------------------------
This installation is NOT Recommended anymore and will soon be replaced by a one click DO installation procedure.
Existing setups will keep working until the 21th of May but will need to be migrated in time in order to ensure user's safety.


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
