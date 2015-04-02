<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getSpatiallyContainedObjects.php elementid [param1=val1 param2=val2 param3=val3 ...] \n");
   die;
}

$debug = 1;
$elementid = $argv[1];
// get 
for ($i = 2; $i < count($argv); $i++) {
   list($p,$v) = explode('=', $argv[$i]);
   $criteria[$p] = $v;
}

$res = getSpatiallyContainedObjects($elementid, $criteria, 1);
$obs = $res['elements'];

print("Spatial Children of $elementid = \n" . print_r($res,1) . "\n");

?>
