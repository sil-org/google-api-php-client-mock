FROM php:8.2-apache
LABEL maintainer="Mark Tompsett <mark_tompsett@sil.org>"

ENV REFRESHED_AT 2023-07-12

# Make sure apt has current list/updates
USER root
RUN apt-get update -y \
# Fix timezone stuff from hanging.
    && echo "America/New_York" > /etc/timezone \
    && apt-get install -y --no-install-recommends tzdata \
    && apt-get upgrade -y \
# Install
    && apt-get install -y --no-install-recommends \
# things needed for GoogleMock objects
        sqlite3 \
# some basics
        unzip wget zip \
# Clean up to reduce docker image size
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /data
WORKDIR /data
COPY --chown=1000:www-data ./ /data

RUN cd /data && ./composer-install.sh
RUN mv /data/composer.phar /usr/bin/composer
RUN /usr/bin/composer install
# User 1000 is the first user on a *nix system
USER 1000
