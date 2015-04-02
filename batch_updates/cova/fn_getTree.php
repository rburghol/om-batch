<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getTree.php elementid [ignore] \n");
   die;
}

$elementid = $argv[1];
if (isset($argv[2])) {
   if ($argv[2] <> '') {
      $ignore = split(',', $argv[2]);
   } else {
      $ignore = array();
   }
} else {
   $ignore = array();
}

$debug = 1;

$tree = getTree($listobject, $elementid, $ignore);
print("Requested for Element $elementid: \n");
print_r($tree);
print("\n");
$elist = array();
foreach ($tree as $thisbranch) {
   $elist[] = $thisbranch['elementid'];
}
print("Element Ids: " . implode(",", $elist) . "\n");


?>