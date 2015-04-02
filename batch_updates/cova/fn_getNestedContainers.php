<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ALL);
##include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getNestedContainers.php elementid [ignore] \n");
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

print("Requested for Element $elementid: \n");
$tree = getNestedContainers($listobject, $elementid, $debug, $ignore);
print_r($tree);
print("\n");
$elist = array();
foreach ($tree as $thisbranch) {
   $elist[] = $thisbranch['elementid'];
}
print("Element Ids: " . implode(",", $elist) . "\n");


?>