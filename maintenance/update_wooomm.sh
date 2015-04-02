#!/bin/sh

# $1 - url of updated code package, $2 package file name
#wget http://deq1.bse.vt.edu/misc/wooomm_code-data.tar.gz
wget $1
cp /var/www/html/wooomm/config.local.php ./config.local.php.bak
gunzip wooomm_code-data.tar.gz
tar -xvf wooomm_code-data.tar
cp ./var/www/html/wooomm/*.php /var/www/html/wooomm
cp ./var/www/html/lib/*.php /var/www/html/lib
cp /var/www/html/wooomm/config.local.php ./config.local.php.new.bak
cp ./config.local.php.bak /var/www/html/wooomm/config.local.php 
echo "old config.local.php file preserved, new config.local.php file saved here as config.local.php.new.bak"
echo "Please verify that the config.local.php file in the directory /var/www/html/wooomm reflects any new items"
