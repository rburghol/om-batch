<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('./xajax_modeling.element.php');
//include_once('lib_batchmodel.php');
global $unserobjects;
$projectid = 3;

//error_reporting(E_ALL);

if (count($argv) < 3) {
   print("Usage: php cbp_init_wds.php scenarioid riverseg [overwrite=0] [do_withdrawals=1] [do_pointsources=1] \n");
   die;
}

$scenid = $argv[1];
$riverseg = $argv[2];
if (isset($argv[3])) {
   $overwrite = $argv[3];
} else {
   $overwrite = 0;
}
if (isset($argv[4])) {
   $do_withdrawals = $argv[4];
} else {
   $do_withdrawals = 1;
}
if (isset($argv[5])) {
   $do_pointsources = $argv[5];
} else {
   $do_pointsources = 1;
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
   if ($overwrite) {
      createCOVAPSWDObjects(array($riverseg), $scenid, $projectid, $do_withdrawals, $do_pointsources);
   }
   print("Looking for CBP parent of $riverseg <br>\n");
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   
   $wds = getCOVAWithdrawals($listobject, $elid);
   foreach ($wds as $thiswd) {
      $recid = $thiswd['elementid'];
      $wdname = $thiswd['elemname'];
      $obres = unSerializeSingleModelObject($recid);
      $thisobject = $obres['object'];
      print("Initializing $wdname ($elementid) \n");
		error_log("Pre-Update Processor Names: " . print_r(array_keys($thisobject->processors),1));
      if (method_exists($thisobject, 'getWithdrawalLocation')) {
         print("calling object getWithdrawalLocation() method ");
         $wkt_geom = $thisobject->getWithdrawalLocation();
         print("Setting Geometry \n");
         $prop_array = array('the_geom'=>$wkt_geom);
         $res = updateObjectProps($projectid, $recid, $prop_array, 1);
      } else {
         error_log(" Method getWithdrawalLocation is not defined on $wdname ($elementid) \n");
      }
		//error_log("Post-Update Processor Names: " . print_r(array_keys($thisobject->processors),1));
		// re-retrieve
      $obres = unSerializeSingleModelObject($recid);
      $thisobject = $obres['object'];
		error_log("Post-Update Processor Names: " . print_r(array_keys($thisobject->processors),1));
      if (method_exists($thisobject, 'reCreate')) {
         print("calling object reCreate() method ");
         $thisobject->vwuds_db = $vwudsdb;
         //$thisobject->wake();
         $thisobject->reCreate();
         $innerHTML = saveObjectSubComponents($listobject, $thisobject, $recid );
         updateObjectPropList($recid, $thisobject, $debug );
         //print("$innerHTML \n");
		   //error_log("Post-reCreate Processor Names: " . print_r(array_keys($thisobject->processors),1));
      } else {
         print("Recreation failed.  $wdname ($elementid)  Object has no reCreate() method \n");
      }
   }
}

?>
