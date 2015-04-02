<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   print("Usage: fn_getCBPSegmentContainers.php scenarioid startseg endseg [get_tribs=0] [debug=0] \n");
   die;
}
$scenarioid = $argv[1];
$startseg = $argv[2];
$endseg = $argv[3];
if (isset($argv[4])) {
   $get_tribs = $argv[4];
} else {
   $get_tribs = 0;
}
if (isset($argv[5])) {
   $debug = $argv[5];
} else {
   $debug = 1;
}
if (isset($argv[4])) {
   $debug = $argv[4];
} else {
   $debug = 1;
}
$riverseg_col = 'riverseg';
$tablename = 'sc_cbp53';

if ($get_tribs) {
   $list = getFullSection($listobject, $startseg, $endseg, array($startseg), $debug);
} else {
   $list = getStreamSection($listobject, $startseg, $endseg, $debug);
}

print("Segment info: " . print_r($list,1) . " <br>\n");

$elids = array();
$elements = array();
foreach ($list as $thisone) {
   if ($thisone <> '') {
      $elinfo = getUserObjectTypes($listobject, -1, $scenarioid, '', 'cova_ws_container', $thisone);
//print_r($elinfo);
      foreach ($elinfo as $rec) {
         $thisinfo = $rec[0];
         if ($thisinfo['elementid'] > 0) {
            $elements[] = array('elid' => $thisinfo['elementid'], 'segment' => $thisone);
            $elids[] = $thisinfo['elementid'];
         }
      }
   }
}

print("Segment List: \n" . print_r($elements,1) . "\n");
print("Element List: " . join(',', $elids) . "\n");

?>
