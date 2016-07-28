#!/bin/bash

# Install Apache and PHP (and any needed extensions).
sudo yum install -y git php php-mcrypt php-pdo php-xml php-mbstring

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
