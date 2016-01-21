FROM ubuntu:trusty
MAINTAINER jekkos
RUN apt-get update
RUN apt-get -y upgrade
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install mysql-client mysql-server apache2 libapache2-mod-php5 pwgen python-setuptools vim-tiny php5-mysql
RUN easy_install supervisor
ADD ./foreground.sh /etc/apache2/foreground.sh
ADD ./supervisord.conf /etc/supervisord.conf
RUN chmod 755 /etc/apache2/foreground.sh
# Install dependencies 
RUN apt-get install -y --no-install-recommends software-properties-common
RUN apt-get install -y python git
# RUN add-apt-repository ppa:chris-lea/node.js
# RUN apt-get update && apt-get dist-upgrade

# Get latest Ospos source from Git
RUN git clone https://github.com/jekkos/opensourcepos.git /app
RUN cd app && git checkout develop/2.4
# RUN cd app && npm install

RUN ln -fs /app/* /var/www/html
ADD ./start_container.sh /start_container.sh
RUN chmod 755 /start_container.sh
EXPOSE 80 3306
CMD ["/bin/bash", "/start_container.sh"]
