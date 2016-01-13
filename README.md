Open Source Point of Sale is a web based point of sale system written in the PHP language. It uses MySQL as the data storage back-end and has a simple user interface.

[![Build Status](https://travis-ci.org/jekkos/opensourcepos.svg?branch=master)](https://travis-ci.org/jekkos/opensourcepos)

How to Install
--------------
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

13/01/2016: Intall using Docker
-------------------------------
From now on ospos can be deployed using Docker on Linux, Mac or Windows. This setup dramatically reduces the number of possible issues as all setup is now done in a Dockerfile. Docker runs natively on mac and linux, but will require more overhead on windows. Please refer to the docker documentation for instructions on how to set it up on your platform.

To build and run the image, issue following commands in a terminal with docker installed

    docker build -t me/ospos https://github.com/jekkos/opensourcepos.git
    docker run -d -p 80:80 me/ospos

Docker will clone the latest master into the image and start a LAMP stack with the application configured.

A more extensive setup guide can be found at [this site](http://www.opensourceposguide.com/guide/gettingstarted/installation)

If you like the project, and you are making money out of it on a daily basis, then consider to buy me a coffee so I can keep adding features.


[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8)

