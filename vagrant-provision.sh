#!/bin/bash

# Make sure apt has current list/updates
sudo apt update
sudo apt upgrade -y

# Install necessary PHP building blocks
# Install Apache and PHP (and any needed extensions). 
# Install mock DB stuff
sudo apt install -y zip unzip php php-dev php-pear \
                    git php-pdo php-xml php-mbstring \
                    sqlite php-sqlite3

# Make sure the timezone is set in php.ini.
sudo sed -i".bak" "s/^\;date\.timezone.*$/date\.timezone = \"America\\/New_York\" /g" /etc/php.ini


# Retrieve the composer dependencies.
cd /var/lib/GA_mock/
if [ ! -e composer.phar ]; then
    sudo curl -sS https://getcomposer.org/installer | php
else
    sudo php composer.phar self-update
fi
sudo php composer.phar update
