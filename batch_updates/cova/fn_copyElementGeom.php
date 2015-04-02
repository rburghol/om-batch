<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 3) {
   print("Usage: fn_copyElementGeom.php src_elid dest_elid \n");
   die;
}

$src_elid = $argv[1];
$dest_elid = $argv[2];

$debug = 1;

//print("Requested container for Element $elementid: \n");
$sinfo = copyElementGeom($src_elid, $dest_elid);
print("Done\n");


?>