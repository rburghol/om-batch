<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 37;
$cbp_scenario = 4;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   print("Usage: php cbp_copy_group_subcomp.php scenarioid riverseg objectgroup src_objectid [comp1,comp2...] \n");
   $listobject->querystring = " select custom1 from scen_model_element where scenarioid = $target_scenarioid group by custom1 order by custom1 ";
   $listobject->performQuery();
   print("Object Groups: \n");
   foreach($listobject->queryrecords as $thisrec) {
      print($thisrec['custom1'] . ", ");
   }
   print("Object Groups: \n");
   die;
}

$scenid = $argv[1];
$riverseg = $argv[2];
$objectgroup = $argv[3];
$srcobject = $argv[4];

$obres = unserializeSingleModelObject($srcobject);
$srcob = $obres['object'];
$name = $srcob->name;
if (isset($argv[5])) {
   $subcomps = split(',', $argv[5]); 
} else {
   $subcomps = array_keys($srcob->processors);
}

if (strlen($riverseg) <= 3) {
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   print("Looking for river abbreviation match <br>\n");
} else {
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg = '$riverseg' ";
   print("Looking for river full-name match <br>\n");
}
//print_r($segs);

print("$listobject->querystring ; <br>");
$listobject->performQuery();
//$listobject->showList();
//die;
$recs = $listobject->queryrecords;

foreach ($recs as $thisrec) {
   $riverseg = $thisrec['riverseg'];
   $parentid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   switch ($objectgroup) {
      case 'cova_water_usetype_group':
      $obs = getCOVAWithdrawals($listobject, $parentid, array(), $debug);
      break;
      
      case 'cova_pointsource':
      $obs = getCOVADischarges($listobject, $scenid, $parentid, array(), $debug);
      break;
      
   }
   
   foreach ($obs as $thisob) {
      $i = 0;
      $destid = $thisob['elementid'];
      foreach ($subcomps as $thiscomp) {
         print("Trying to add Sub-comp $thiscomp to Element $destid <br>\n");
         print("copySubComponent($srcid, $thiscomp, $destid, $thiscomp)<br>\n");
         $cr = copySubComponent($srcobject, $thiscomp, $destid, $thiscomp);
         print("$cr<br>\n");
         print("Sub-comp $thiscomp added to Element $destid <br>\n");
         $i++;
      }
   }
}

?>