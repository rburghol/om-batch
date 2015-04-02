<?php

$noajax = 1;
$projectid = 3;
include_once('config.php');
//error_reporting(E_ALL);


# CBP model framework
$tablename = 'sc_p52icprb';
$colname = 'catcode2';
$acolname = 'contrib_area_sqmi';
$gcolname = 'the_geom';
$srid = 26918; # srid for area units
$cfact = 1.0 / 2589998.1; # conversion factor for units

$listobject->querystring = "  select split_part($colname, '_', 2) as segid, split_part($colname, '_', 3) as dsegid ";
$listobject->querystring .= " from $tablename  ";
$listobject->querystring .= " where $acolname is null ";
#$listobject->querystring .= " where $colname ilike '%_0000' ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();

$finalrecs = $listobject->queryrecords;

foreach ($finalrecs as $thisrec) {
   $segid = $thisrec['segid'];
#$segid = '6710';
$debug = 0;
   $area = getCBPTotalContribArea($listobject, $tablename, $colname, $acolname, $gcolname, $srid, $cfact, $segid, $debug);

   print("<hr><b>Total Contributing Area for Segment $segid</b> = $area <hr>");
}
   
?>