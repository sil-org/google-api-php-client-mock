FROM php:7.3-apache-buster
LABEL maintainer="Mark Tompsett <mark_tompsett@sil.org>"

ENV REFRESHED_AT 2021-03-23

# Fix timezone stuff from hanging.
RUN apt-get update -y && echo "America/New_York" > /etc/timezone; \
    apt-get install -y tzdata

# Make sure apt has current list/updates
# Install necessary PHP building blocks
# Install Apache and PHP (and any needed extensions).
# Install mock DB stuff
RUN apt-get install -y \
        zip \
        unzip \
        make \
        curl \
        wget \
# Needed for GoogleMock objects
        sqlite \
        sqlite3 \
# Needed to build php extensions
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
# Clean up to reduce docker image size
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install and enable, see the README on the docker hub for the image
RUN docker-php-ext-install pdo pdo_mysql mbstring xml curl && \
    docker-php-ext-enable pdo pdo_mysql mbstring xml curl

RUN mkdir -p /data
WORKDIR /data
COPY ./ /data

RUN cd /data && ./composer-install.sh
RUN mv /data/composer.phar /usr/bin/composer
