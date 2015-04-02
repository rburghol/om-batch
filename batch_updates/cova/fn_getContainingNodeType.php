<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getSessionTableNames.php elementid [runid=-1] \n");
   die;
}

$elementid = $argv[1];
if (isset($argv[2])) {
   $runid = $argv[2];
} else {
   $runid = -1;
}

$debug = 1;

//print("Requested container for Element $elementid: \n");
$sinfo = getSessionTableNames($thisobject, $elementid, $runid);
print("Requested container for Element $elementid: \n");
print_r($sinfo);
print("\n");


?>