FROM silintl/php7:7.2
MAINTAINER Mark Tompsett <mark_tompsett@sil.org>

ENV REFRESHED_AT 2020-03-16

RUN mkdir -p /data
COPY ./ /data/

# Make sure apt has current list/updates
# Install necessary PHP building blocks
# Install Apache and PHP (and any needed extensions).
# Install mock DB stuff
RUN apt-get update -y && \
    apt-get upgrade -y && \
    apt-get install -y zip unzip make php php-pdo php-xml php-mbstring sqlite php-sqlite3

WORKDIR /data
