<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

global $userid;

error_reporting(E_NONE);

$latdd = $argv[1];
$londd = $argv[2];

$landseg = getCOVACBPLandsegPointContainer($latdd, $londd);
print("Land seg = " . $landseg . "\n"); 

$riverseg = getCOVACBPPointContainer($listobject, $latdd, $londd);
print("River seg = " . $riverseg . "\n");


?>
