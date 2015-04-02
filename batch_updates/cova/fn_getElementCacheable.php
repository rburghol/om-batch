<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 1) {
   print("Usage: fn_getElementCacheable.php elementid [param2,param3,...] \n");
   die;
}
$param1 = $argv[1];
$param2 = $argv[2];
$param3 = $argv[3];
$param4 = $argv[4];
$debug = 1;

// get submatrix from vwp project template and add it to a subcomponent hydoImpSmall
$out = getElementCacheable($listobject, $param1);

print("getElementCacheable(listobject, $param1) Output \n $out \n");
