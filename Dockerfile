FROM ubuntu:utopic
MAINTAINER jekkos
RUN sed -i -e 's/archive.ubuntu.com\|security.ubuntu.com/old-releases.ubuntu.com/g' /etc/apt/sources.list
RUN apt-get update
RUN apt-get -y upgrade
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install mysql-client mysql-server apache2 libapache2-mod-php5 pwgen python-setuptools vim-tiny php5-mysql php5-gd php5-apcu nodejs npm curl
RUN easy_install supervisor
ADD ./docker/foreground.sh /etc/apache2/foreground.sh
ADD ./docker/supervisord.conf /etc/supervisord.conf
RUN chmod 755 /etc/apache2/foreground.sh
# Install dependencies 
RUN apt-get install -y --no-install-recommends software-properties-common
RUN apt-get install -y python git

# Get latest Ospos source from Git
RUN git clone https://github.com/jekkos/opensourcepos.git /app
RUN cd app && git checkout origin/master
RUN ln -s /usr/bin/nodejs /usr/bin/node
RUN cd app && npm install
RUN npm install -g grunt-cli
RUN ln -s /usr/local/bin/grunt /usr/bin/grunt

RUN ln -fs /app/* /var/www/html
ADD ./docker/start_container.sh /start_container.sh
RUN chmod 755 /start_container.sh
EXPOSE 80 3306
CMD ["/bin/bash", "/start_container.sh"]
