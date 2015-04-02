<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

$scenarioid = $argv[1];
$riverseg = $argv[2];
if (isset($argv[3])) {
   $option = $argv[3];
} else {
   $option = 1;
}


$listobject->querystring = "select elementid, elemname from scen_model_element where custom2 ";
switch ($option) {
   case 1:
   $listobject->querystring .= " = '$riverseg' ";
   break;
   
   case 2: 
   $listobject->querystring .= " ilike '$riverseg%' ";
   break;
   
   default:
   print("Invalid search option '$option' \n");
   die;
   break;
}
$listobject->querystring .= " and scenarioid = $scenarioid ";
$listobject->performQuery();
$recs = $listobject->queryrecords;
foreach ($recs as $thisrec) {
   $elid = $thisrec['elementid'];
   print("Deleting $elid \n");
   deleteModelElement($elid);
}

?>