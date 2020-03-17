#!/bin/sh

cd /data
curl -sS https://getcomposer.org/installer | php
php composer.phar self-update
php composer.phar update
