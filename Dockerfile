FROM php:8.2-apache
RUN useradd nonroot
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
    && rm -rf /var/lib/apt/lists/* \
# Make sure the directory we'll mount and reference is there
    && mkdir -p /data

USER nonroot
WORKDIR /data
COPY actions-services.yml /data
COPY composer-install.sh /data
COPY composer.json /data
COPY composer.lock /data
COPY docker-compose.yml /data
COPY Dockerfile /data
COPY LICENSE /data
COPY Makefile /data
COPY README.md /data
COPY run-tests.sh /data
COPY .travis.yml /data
COPY SilMock/ /data/SilMock

USER root
# Make sure the development test files exist and have writable permissions
RUN touch /data/SilMock/DataStore/Sqlite/Test1_Google_Service_Data.db && \
    touch /data/SilMock/DataStore/Sqlite/Test2_Google_Service_Data.db && \
    touch /data/SilMock/DataStore/Sqlite/Test3_Google_Service_Data.db && \
    touch /data/SilMock/DataStore/Sqlite/Test4_Google_Service_Data.db && \
    touch /data/SilMock/DataStore/Sqlite/Test5_Google_Service_Data.db && \
    touch /data/SilMock/tests/.phpunit.result.cache && \
    chown -R nonroot:root /data && \
    chmod 664 /data/SilMock/DataStore/Sqlite/Test1_Google_Service_Data.db && \
    chmod 664 /data/SilMock/DataStore/Sqlite/Test2_Google_Service_Data.db && \
    chmod 664 /data/SilMock/DataStore/Sqlite/Test3_Google_Service_Data.db && \
    chmod 664 /data/SilMock/DataStore/Sqlite/Test4_Google_Service_Data.db && \
    chmod 664 /data/SilMock/DataStore/Sqlite/Test5_Google_Service_Data.db && \
    chmod 664 /data/SilMock/tests/.phpunit.result.cache

WORKDIR /data
RUN ./composer-install.sh \
    && mv /data/composer.phar /usr/bin/composer \
    && /usr/bin/composer install
USER nonroot
