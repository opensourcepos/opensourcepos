FROM jbfink/docker-lampstack
MAINTAINER jekkos
# Install dependencies 
RUN apt-get install -y --no-install-recommends software-properties-common
RUN apt-get install -y python git
RUN apt-get update
# RUN add-apt-repository ppa:chris-lea/node.js

# Get latest Ospos source from Git
RUN git clone https://github.com/jekkos/opensourcepos.git /app
# RUN cd app && npm install

RUN ln -fs /app/* /var/www
ADD ./start_container.sh /start_container.sh
RUN chmod 755 /start_container.sh
EXPOSE 80 3306
CMD ["/bin/bash", "/start_container.sh"]
