#!/bin/sh
yr=`date +%Y`
mo=`date +%m`
da=`date +%d`
lastmonth=$(expr $mo - 1)
formonth=`printf %02d $lastmonth`
echo $lastmonth
echo $formonth
if [ $lastmonth -lt 0 ]; then
   lastmonth=12
   yr=$(expr $yr - 1)
fi

lastdate="$yr$formonth$da"
echo "Cleaning debug files prior to $lastdate"

/usr/local/bin/findDateRange.ksh "/var/www/html/data/proj3/out/debug*" 20080101 $lastdate > debugfiles.txt
for i in `cat debugfiles.txt `;do echo "removing $i"; rm -f $i; done

##############
# remove model run debug files
rm -f /var/www/html/om/debug*.log
rm -f /var/www/html/om/tsvalue*
rm -f /var/www/html/wooommdev/debug*.log
rm -f /var/www/html/wooommdev/tsvalue*
rm -f /opt/model/apache/batch_updates/debug*.log
rm -f /opt/model/apache/batch_updates/tsvalue*
rm -f /opt/model/apache/batch_updates/cova/debug*.log
rm -f /opt/model/apache/batch_updates/cova/tsvalue*
rm -f /var/www/html/wooommdev/summary/debug*.log
rm -f /var/www/html/om/summary/debug*.log
