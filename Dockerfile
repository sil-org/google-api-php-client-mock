FROM silintl/data-volume:latest
MAINTAINER Mark Tompsett <mark_tompsett@sil.org>

ENV REFRESHED_AT 2020-03-16

# Fix timezone stuff from hanging.
RUN apt-get update -y && echo "America/New_York" > /etc/timezone; \
    apt-get install -y tzdata

# Make sure apt has current list/updates
# Install necessary PHP building blocks
# Install Apache and PHP (and any needed extensions).
# Install mock DB stuff
RUN apt-get install -y zip unzip make curl wget \
    php php-pdo php-xml php-mbstring sqlite php-sqlite3

RUN mkdir -p /data
WORKDIR /data
COPY ./ /data

RUN cd /data && ./composer-install.sh
RUN mv /data/composer.phar /usr/bin/composer
