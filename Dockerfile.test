FROM digitallyseamless/nodejs-bower-grunt:5
MAINTAINER jekkos

# apt-get install curl

COPY Gruntfile.js .
COPY package.json .
COPY test .
RUN npm install

CMD ['while ! curl web/index.php | grep username; do sleep 1; done; grunt mochaWebdriver:test']
