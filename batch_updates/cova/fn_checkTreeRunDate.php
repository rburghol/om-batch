<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 5) {
   print("Usage: fn_checkTreeRunDate.php elementid runid startdate enddate cache_date [remove_outdated=0] [require_verification=1] [debug=0]\n");
   die;
}

$recid = $argv[1];
$run_id = $argv[2];
$startdate = $argv[3];
$enddate = $argv[4];
$cache_date = $argv[5];
if (isset($argv[6])) {
   $remove_outdated = $argv[6];
} else {
   $remove_outdated = 0;
}
if (isset($argv[7])) {
   $require_verification = $argv[7];
} else {
   $require_verification = 0;
}
if (isset($argv[8])) {
   $debug = $argv[8];
} else {
   $debug = 0;
}

$tree_check = checkTreeRunDate($listobject, $recid, $run_id, $startdate, $enddate, $cache_date, $debug, $remove_outdated, $require_verification);

print("$tree_check = checkTreeRunDate(listobject, $recid, $run_id, $startdate, $enddate, $cache_date, $debug, $remove_outdated, $require_verification ) \n");

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
