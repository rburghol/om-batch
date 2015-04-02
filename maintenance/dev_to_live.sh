#!/bin/sh

# $1 - url of updated code package, $2 package file name
cp /var/www/html/wooomm/config.local.php ./config.local.php.bak
cp /var/www/html/wooommdev/*.php /var/www/html/wooomm
cp /var/www/html/vdeq-libs/trunk/*.php /var/www/html/lib
cp /var/www/html/wooomm/config.local.php ./config.local.php.new.bak
cp ./config.local.php.bak /var/www/html/wooomm/config.local.php 
echo "old config.local.php file preserved, new config.local.php file saved here as config.local.php.new.bak"
echo "Please verify that the config.local.php file in the directory /var/www/html/wooomm reflects any new items"
