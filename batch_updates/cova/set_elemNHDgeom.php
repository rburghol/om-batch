<?php

$userid = 1;
include_once('xajax_modeling.element.php');
//include_once('lib_batchmodel.php');

if (count($argv) < 3) {
   print("Usage: php set_elemNHDgeom.php elementid latdd or comid [londd] [# of extra NHD+ basins] [local_shape_only=0]\n");
   die;
}
$elid = $argv[1];
$latdd = $argv[2];
if (isset($argv[3]) and ($argv[3] <> '')) {
   $londd = $argv[3];
   $op = 'latlon';
} else {
   $op = 'comid';
   $comid = $latdd;
}
if (isset($argv[4])) {
   $extra_basins = $argv[4];
} else {
   $extra_basins = 0;
}
if (isset($argv[5])) {
   $local_shape_only = $argv[5];
} else {
   $local_shape_only = 0;
}

switch ($op) {
   case 'latlon':
   $basininfo = getMergedNHDBasin($usgsdb, $latdd, $londd, $extra_basins);
   $comid = $basininfo['outlet_comid'];
   if ($local_shape_only) {
      error_log("Getting local watershed shape only - comid $comid.");
      $wkt_geom = getSingleNHDShape($usgsdb, explode(',',$comid), $debug);
   } else {
      $wkt_geom = $basininfo['the_geom'];
   }
   print(" NHD+ Shape Retrieved \n");
   break;
   
   case 'comid':
   if ($local_shape_only) {
      error_log("Getting local watershed shape only - comid $comid.");
      $wkt_geom = getSingleNHDShape($usgsdb, explode(',',$comid), 1);
   } else {
      $wkt_geom = findNHDBasinShape($usgsdb, $comid);
   }
   print(" NHD+ Shape Retrieved \n");
   break;
   
}
print("NHD+ Shape for $elid = " . substr($wkt_geom,0,64) . " \n");
print("COMID = $comid \n");
// set the shape
print("Setting Shape for $elid \n");
setElementGeometry($elid, 3, $wkt_geom);
updateObjectProps($projectid, $elementid, array('geomtype'=>3),0);
print("Finished \n");


?>
