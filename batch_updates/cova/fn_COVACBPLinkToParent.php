<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 3) {
   print("Usage: fn_COVACBPlinkToParent.php scenarioid riverseg \n");
   die;
}
$scenid = $argv[1];
$riverseg = $argv[2];
$debug = 1;
$elid = getCOVACBPContainer($listobject, $scenid, $riverseg);


COVACBPlinkToParent($scenid, $riverseg, $elid);

?>