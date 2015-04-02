#!/bin/sh

# $1 - url of updated code package, $2 package file name
#wget http://deq1.bse.vt.edu/misc/wooommdev_code-data.tar.gz
wget $1
cp /opt/model/apache/batch_updates/config.local.php ./config.local.php.bak
gunzip batchmodel.tar.gz
tar -xvf batchmodel.tar
cp ./opt/model/apache/batch_updates/*.php /opt/model/apache/batch_updates/
cp ./opt/model/apache/batch_updates/cova/*.php /opt/model/apache/batch_updates/cova/
echo "done."
cp /opt/model/apache/batch_updates/config.local.php ./config.local.php.new.bak
cp ./config.local.php.bak /opt/model/apache/batch_updates/config.local.php 
echo "old config.local.php file preserved, new config.local.php file saved here as config.local.php.new.bak"
echo "Please verify that the config.local.php file in the directory /var/www/html/wooommdev reflects any new items"
