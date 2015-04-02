<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_verifyRunStatus.php elementid runid \n");
   die;
}

$recid = $argv[1];
$run_id = $argv[2];
$debug = 1;

$status_vars = verifyRunStatus($listobject, $recid, $run_id);

print("verifyRunStatus(listobject, $recid, $run_id, $sip)  = \n" . print_r($status_vars,1));

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