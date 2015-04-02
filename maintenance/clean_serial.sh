#!/bin/sh
# not yet part of automated, clears ".serial" version of run log, which is un-used and just takes extra space
for i in `ls  /var/www/html/wooomm/dirs/proj3/out/ | grep serial`; do 
   echo "Clearing $i"
   sudo rm -f /var/www/html/wooomm/dirs/proj3/out/$i 
done
