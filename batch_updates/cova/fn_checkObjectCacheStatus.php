<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

if (count($argv) < 5) {
   print("Usage: fn_checkObjectCacheStatus.php elementid runid startdate enddate [cache_level=-1 (can be a date)] [order=1] [debug=0]\n");
   die;
}
$recid = $argv[1];
$runid = $argv[2];
$startdate = $argv[3];
$enddate = $argv[4];
$cache_level = $argv[5];
if (isset($argv[6])) {
   $order = $argv[6];
} else {
   $order = 1;
}
if (isset($argv[7])) {
   $debug = $argv[7];
} else {
   $debug = 0;
}

$cs = checkObjectCacheStatus($listobject, $recid, $order, $cache_level, $runid, 1, $startdate, $enddate, $debug);

print("Cache Check: " . print_r($cs,1) . " \n");

?>
