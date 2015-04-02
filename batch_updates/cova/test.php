<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

$scenarioid = $argv[1];
$latdd = $argv[2];
$londd = $argv[3];


$listobject->querystring = "select elementid, elemname from scen_model_element where custom1 = 'cova_ws_container' ";
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

?>