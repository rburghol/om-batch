<?php

$noajax = 1;
include('./xajax_modeling.element.php');
if (count($argv) < 2) {
   print("USAGE: php fn_getNHDChannelInfo.php lat lon [debug=0]\n");
   die;
}
$lat_dd = $argv[1];
$lon_dd = $argv[2];
if (isset($argv[3])) {
   $debug = $argv[3];
} else {
   $debug = 0;
}

$nhd = new nhdPlusDataSource;
$nhd->init();
if ( is_numeric($lat_dd) and is_numeric($lon_dd)) {
   $nhd->debug = $debug;
   $nhd->getPointInfo($lat_dd, $lon_dd);
   error_log("Searching for coords: $lat_dd, $lon_dd");
   error_log("NLCD Land Use: " . print_r($nhd->nlcd_landuse,1));
   //error_log("NHD+ Reaches: " . print_r($nhd->nhd_segments,1));
}

?>
