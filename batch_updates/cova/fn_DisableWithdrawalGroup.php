<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   print("Usage: fn_DisableWithdrawalGroup.php scenarioid riversegment category(IRR,MAN,PH,PF,PN,...) \n");
   die;
}

$scenarioid = $argv[1];
$riverseg = $argv[2];
$groupcat = $argv[3];

if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' group by riverseg ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
} else {
   $segs = array($riverseg);
}


$debug = 0;
foreach ($segs as $riverseg) {
   $parentid = getCOVACBPContainer($listobject, $scenarioid, $riverseg);
   $grecs = getWithdrawalGroup($scenarioid, $listobject, $parentid, $groupcat, '', -1, $debug, 0);
   $elid = $grecs[$elid];
   if (!$elid) {
      print("Group $groupcat not found on element $parentid \n");
      die;
   }
   //print("getWithdrawalGroup($scenarioid, listobject, $parentid, $groupcat, '', -1, $debug, 0); \n");
   $elname = getElementName($listobject, $elid);
   $child_types = array('wsp_waterUser','wsp_vpdesvwuds');
   print("Looking at Segment: $riverseg \n");
   foreach ($child_types as $ct) {
      $childrecs = getChildComponentType($listobject, $elid, $ct, -1, $debug);
      foreach ($childrecs as $thischild) {
         //array_push($children, $thischild);
         $childid = $thischild['elementid'];
         $cname = $thischild['elemname'];
         print("... found $cname ($childid) in $riverseg ($elid) \n");
         // disable this withdrawal
         updateObjectProps($projectid, $childid, array('withdrawal_enabled'=>0));
      }
   }

}
?>
