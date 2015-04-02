<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
//#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getNestedContainersCriteria.php elementid [types] [custom1] [custom2] [ignore] \n");
   die;
}

$elementid = $argv[1];
if (isset($argv[2])) {
   if ($argv[2] <> '') {
      $types = split(',', $argv[2]);
   } else {
      $types = array();
   }
} else {
   $types = array();
}
if (isset($argv[3])) {
   if ($argv[3] <> '') {
      $custom1 = split(',', $argv[3]);
   } else {
      $custom1 = array();
   }
} else {
   $custom1 = array();
}
if (isset($argv[4])) {
   if ($argv[4] <> '') {
      $custom2 = split(',', $argv[4]);
   } else {
      $custom2 = array();
   }
} else {
   $custom2 = array();
}
if (isset($argv[5])) {
   if ($argv[5] <> '') {
      $ignore = split(',', $argv[5]);
   } else {
      $ignore = array();
   }
} else {
   $ignore = array();
}

$debug = 1;

$tree = getNestedContainersCriteria ($listobject, $elementid, $types, $custom1, $custom2, $ignore);
print("Requested for Element $elementid: \n");
print_r($tree);
print("\n");
$elist = array();
foreach ($tree as $thisbranch) {
   $elist[] = $thisbranch['elementid'];
}
print("Element Ids: " . implode(",", $elist) . "\n");

/* 
// clear withdrawals
$listobject->querystring = "select elementid, elemname from scen_model_element where custom1 = 'cova_ws_container' and scenarioid = 37 ";
$listobject->performQuery();
$recs = $listobject->queryrecords;
foreach ($recs as $thisrec) {
   $elementid = $thisrec['elementid'];
   $elemname = $thisrec['elemname'];
   print("Found $elemname ($elementid) <br>");
   $wds = getAtLargeWithdrawals($listobject, $elementid, array(), 0);
   foreach ($wds as $thiswd) {
      print("Deleting " . $thiswd['elementid'] . " " . $thiswd['elemname'] . " <br>\n");
      deleteModelElement($thiswd['elementid']);
   }

}
*/

?>