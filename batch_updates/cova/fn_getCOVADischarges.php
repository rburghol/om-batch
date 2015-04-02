<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getCOVADischarges.php scenarioid riversegment \n");
   die;
}

$scenarioid = $argv[1];
$riverseg = $argv[2];

$debug = 1;
$elid = getCOVACBPContainer($listobject, $scenarioid, $riverseg);
//$ps = getCOVADischarges($listobject, $scenarioid, $elid);
$ps = getVPDESDischarges($vpdesdb, $listobject, $elid, $debug);

print("getCOVADischarges(listobject, $scenarioid, $elid) \n");
foreach ($ps as $thisps) {
   print($thisps['vpdes_permit_no'] . "\n");
}

?>