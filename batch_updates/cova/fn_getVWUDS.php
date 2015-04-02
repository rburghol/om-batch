<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getVWUDS.php scenarioid riversegment \n");
   die;
}

$scenarioid = $argv[1];
$riverseg = $argv[2];

$debug = 1;
$elid = getCOVACBPContainer($listobject, $scenarioid, $riverseg);
$vwuds = getVWUDSWithdrawals($vwudsdb, $listobject, $elid, $debug);

foreach ($vwuds as $thiswd) {
   print(print_r($thiswd,1) . "\n");
}

?>
