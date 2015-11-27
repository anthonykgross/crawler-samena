FROM debian:jessie

MAINTAINER Anthony K GROSS

RUN apt-get update -y && \
	apt-get upgrade -y && \
	apt-get install -y npm curl git

RUN apt-get install -y php5-common php5-cli php5-fpm php5-mcrypt php5-mysql php5-apcu php5-gd php5-imagick php5-curl php5-intl --fix-missing 

ENV CRAWLER_START_DATE "FALSE"
ENV CRAWLER_END_DATE "FALSE"

# Installation de Node.js à partir du site officiel
RUN curl -LO "https://nodejs.org/dist/v0.12.5/node-v0.12.5-linux-x64.tar.gz" 
RUN tar -xzf node-v0.12.5-linux-x64.tar.gz -C /usr/local --strip-components=1
RUN rm node-v0.12.5-linux-x64.tar.gz

RUN rm -rf /var/lib/apt/lists/* && apt-get autoremove -y --purge

RUN usermod -u 1000 www-data

WORKDIR /home/crawlersamena/

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
