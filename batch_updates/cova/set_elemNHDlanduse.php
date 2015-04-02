<?php

$userid = 1;
include_once('xajax_modeling.element.php');
//include_once('lib_batchmodel.php');

if (count($argv) < 3) {
   print("Usage: php set_elemNHDlanduse.php elementid landuse_name [mode=element,cova_parent] [units=sqmi,acres,sqkm] [translate=0(nlcd),1(cbp)]\n");
   die;
}
$elid = $argv[1];
$landuse_name = $argv[2];
if (isset($argv[3])) {
   $mode = $argv[3];
} else {
   $mode = 'element';
}
if (isset($argv[4])) {
   $units = $argv[4];
} else {
   $units = 'acres';
}
if (isset($argv[5])) {
   $translate = $argv[5];
} else {
   $translate = 0;
}
$pid = -1;
switch ($mode) {
   case 'cova_parent':
   $pid = getContainingNodeType($elid, 0, array('custom1'=>array('cova_ws_subnodal','cova_ws_container')));
   break;
}
   
// actually should get the existing shape on the model element, intersect it with NHDPlus basins, calaculating overlap %s, and then weight the resulting land use query accordingly

setNLCDLanduse($elid, $landuse_name, 1880, 2050, $pid, $translate);


?>
