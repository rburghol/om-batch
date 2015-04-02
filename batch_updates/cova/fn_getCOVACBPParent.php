<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getCOVACBPParent.php scenarioid riverseg \n");
   die;
}

$scenid = $argv[1];
$riverseg = $argv[2];
$debug = 1;

$tree_check = getCOVACBPParent($scenid, $riverseg, $debug);

print("Output of getCOVACBPParent($scenid, $riverseg, $debug): " . print_r($tree_check,1) . "<br>\n");


?>