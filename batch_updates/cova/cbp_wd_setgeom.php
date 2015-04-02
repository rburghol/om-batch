<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

if (count($argv) < 3) {
   print("Usage: php cbp_wd_setgeom.php scenarioid riverseg [overwrite=0] \n");
   die;
}

$scenid = $argv[1];
$riverseg = $argv[2];
if (isset($argv[3])) {
   $overwrite = $argv[3];
} else {
   $overwrite = 0;
}
if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
} else {
   $segs = split(',',$riverseg);
}
//print_r($segs);

foreach ($segs as $riverseg) {
   print("Looking for CBP parent of $riverseg <br>\n");
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   
   $wds = getCOVAWithdrawals($listobject, $elid);
   foreach ($wds as $thiswd) {
      $recid = $thiswd['elementid'];
      $wdname = $thiswd['elemname'];
      $obres = unSerializeSingleModelObject($recid);
      $thisobject = $obres['object'];
      print("Initializing $wdname \n");

      if (method_exists($thisobject, 'getWithdrawalLocation')) {
         print("calling object getWithdrawalLocation() method ");
         $wkt_geom = $thisobject->getWithdrawalLocation();
         $prop_array = array('the_geom'=>$wkt_geom);
         $res = updateObjectProps($projectid, $recid, $prop_array, $debug);
         $innerHTML = $res['innerHTML'];
         print("$innerHTML \n");
      } else {
         print("...failed.  Object has not getWithdrawalLocation() method \n");
      }
   }
}

?>
