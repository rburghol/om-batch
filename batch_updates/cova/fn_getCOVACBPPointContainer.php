<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 3) {
   print("Usage: fn_COVACBPPointContainer.php scenarioid latdd londd \n");
   die;
}
$scenid = $argv[1];
$latdd = $argv[2];
$londd = $argv[3];
$debug = 1;
$elid = getCOVACBPPointContainer($listobject, $latdd, $londd);

print("contained by element $elid <br>\n");


?>
