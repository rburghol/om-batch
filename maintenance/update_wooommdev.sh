#!/bin/sh

# $1 - url of updated code package, $2 package file name
#wget http://deq1.bse.vt.edu/misc/wooommdev_code-data.tar.gz
wget $1
cp /var/www/html/wooommdev/config.local.php ./config.local.php.bak
gunzip wooommdev_code-data.tar.gz
tar -xvf wooommdev_code-data.tar
cp ./var/www/html/wooommdev/*.php /var/www/html/wooommdev
cp ./var/www/html/devlib/*.php /var/www/html/devlib
cp /var/www/html/wooommdev/config.local.php ./config.local.php.new.bak
cp ./config.local.php.bak /var/www/html/wooommdev/config.local.php 
echo "old config.local.php file preserved, new config.local.php file saved here as config.local.php.new.bak"
echo "Please verify that the config.local.php file in the directory /var/www/html/wooommdev reflects any new items"
