<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getCBPSegList.php segid [scenarioid=37] [debug=0] \n");
   die;
}
$segid = $argv[1];
if (isset($argv[2])) {
   $scenarioid = $argv[2];
} else {
   $scenarioid = 0;
}
if (isset($argv[3])) {
   $debug = $argv[3];
} else {
   $debug = 0;
}
$colname = 'riverseg';
$tablename = 'sc_cbp53';

$list = getCBPSegList($listobject, $tablename, $colname, $segid, $debug);

print("Segment info: " . print_r($list,1) . " <br>\n");
foreach ($list['segnames'] as $thisone) {
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
