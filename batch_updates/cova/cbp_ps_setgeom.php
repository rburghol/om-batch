<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

if (count($argv) < 3) {
   print("Usage: php cbp_ps_setgeom.php scenarioid riverseg [overwrite=0] \n");
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
   
   $pss = getCOVAPointSources($listobject, $elid);
   foreach ($pss as $thisps) {
      $recid = $thisps['elementid'];
      $psname = $thisps['elemname'];
      $obres = unSerializeSingleModelObject($recid);
      $thisobject = $obres['object'];
      print("Initializing $psname \n");

      if (method_exists($thisobject, 'getWithdrawalLocation')) {
         print("calling object getVPDESInfo()() method ");
         $thisobject->debugmode = 1;
         $thisobject->getVPDESInfo();
         if (isset($thisobject->ps_info['wkt_geom'])) {
            $wkt_geom = $thisobject->ps_info['wkt_geom'];
            $prop_array = array('the_geom'=>$wkt_geom, 'name'=>$thisobject->ps_info['facility_name'] . " (VPDES " . $thisobject->ps_info['vpdes_permit_no'] . ")");
            $res = updateObjectProps($projectid, $recid, $prop_array, $debug);
            $innerHTML = $res['innerHTML'];
            print("$innerHTML \n");
         } else {
            print("Geometry aquisition failed for $psname \n");
         }
      } else {
         print("...failed.  Object has not getWithdrawalLocation() method \n");
      }
   }
}

?>
