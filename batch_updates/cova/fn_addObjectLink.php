<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 3) {
   print("Usage: fn_addObjectLink.php srcid destid [srcpropname] [destpropname] [linktype=1 (1=contain,2=local prop,3=remote prop)] \n");
   die;
}
$srcid = $argv[1];
$destid = $argv[2];
$linktype = 1;
if (isset($argv[3])) {
   $srcprop = $argv[3];
   $linktype = 2;
} 

if (isset($argv[4])) {
   $destprop = $argv[4];
} else {
   $destprop = $srcprop;
}

if (isset($argv[5])) {
   $linktype = $argv[5];
}
if ($srcprop == '') {
   // is a parent child link only - regardless of what they requested
   $linktype = 1;
}


$debug = 1;
$info = addObjectLink($projectid, $scenarioid, $srcid, $destid, $linktype, $srcprop, $destprop);

print("Result: " . print_r($info,1) . "\n");

?>