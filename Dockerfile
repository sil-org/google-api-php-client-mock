FROM php:7.4-apache
LABEL maintainer="Mark Tompsett <mark_tompsett@sil.org>"

ENV REFRESHED_AT 2021-06-10

# Make sure apt has current list/updates
RUN apt-get update -y \
# Fix timezone stuff from hanging.
    && echo "America/New_York" > /etc/timezone \
    && apt-get install -y tzdata \
    && apt-get upgrade -y \
# Install some basics
    && apt-get install -y zip unzip wget \
# Needed for GoogleMock objects
        sqlite sqlite3 \
# Clean up to reduce docker image size
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /data
WORKDIR /data
COPY ./ /data

RUN cd /data && ./composer-install.sh
RUN mv /data/composer.phar /usr/bin/composer
