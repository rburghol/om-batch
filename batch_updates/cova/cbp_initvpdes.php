<?php

error_reporting(E_ERROR);
/*
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');
*/

$userid = 1;
include_once('/var/www/html/wooommdev/xajax_modeling.element.php');

error_reporting(E_ERROR);

$scenid = $argv[1];
$riverseg = $argv[2];
if (count($argv) < 3) {
   print("Usage: php cbp_initvpdes.php scenarioid riverseg (or abbrev) [recreate def=1] [overwrite def=0] [nonzero_only = 1] [vpdes_permitno = ''] \n");
   die;
}
if (isset($argv[3])) {
   $recreate = $argv[3];
} else {
   $recreate = 1;
}
if (isset($argv[4])) {
   $overwrite = $argv[4];
} else {
   $overwrite = 0;
}
if (isset($argv[5])) {
   $nonzero_only = $argv[5];
} else {
   $nonzero_only = 1;
}
if (isset($argv[6])) {
   $single_permit = $argv[6];
   $overwrite = 0; // can't overwrite them all if we are askign for only one
} else {
   $single_permit = '';
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
   $segs = array($riverseg);
}
//print_r($segs);
global $unserobjects;
print("Using object $ps_tid as template.\n");
foreach ($segs as $riverseg) {

   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   print("Looking for CBP parent of $riverseg <br>\n");
   $contid = getCOVAPointSourceContainer($scenid, $listobject, $elid, $ps_contid, 1);
   $elementname = getElementName($listobject, $contid);
   print("Located point source container - $elementname ($contid) <br>\n");
   
   if ($overwrite) {
      $result = deleteCOVAPointSources($listobject, $contid, array(), 0);
   }
   // now, get all VPDES points
   //print("VPDES records added as part of VWUDS records " . print_r($vpdes_ids,1) . "\n");
   $ps = getVPDESDischarges($vpdesdb, $listobject, $elid, 0);
   print(count($ps) . " VPDES records found \n");
   $vpdes_ids = array();
   foreach ($ps as $thisps) {
      $vpdesno = trim($thisps['vpdes_permit_no']);
      $vpdes_name = trim($thisps['facility_name']);
      if (in_array($vpdesno, $vpdes_ids)) {
         print("VPDES Permit $vpdesno already added\n");
      } else {
         if ($single_permit <> '') {
            print("Checking $vpdesno against $single_permit \n");
         }
         if ( ($single_permit == '') or ($single_permit == $vpdesno) ) {
            $vpdes_ids[] = $vpdesno;
            print_r($thisps);
            $props = array('name'=>'VPDES ' . $vpdesno, 'id1'=>'', 'id2'=>'', 'custom1'=>'cova_pointsource', 'custom2'=>$vpdesno, 'description'=>$vpdes_name);
            $props['vpdes_permitno'] = $vpdesno;
            $props['discharge_enabled'] = 1;
            //print_r($props);
            // get info about this discharge - does it have records?
            $vpdesdb->querystring = " select count(*) from vpdes_discharge_no_ms4_cache where vpdes_permit_no = '$vpdesno' ";
            print($vpdesdb->querystring . "\n");
            $vpdesdb->performQuery();
            $numrecs = $vpdesdb->getRecordValue(1,'count');
            $valid_obj = 0;
            $recid = -1;
            if (!$overwrite) {
               print("Retrieving object for $vpdesno\n");
               //error_reporting(E_ALL);
               $vprec = getChildComponentCustom($listobject, 'cova_pointsource', $vpdesno, -1, $contid);
               if (count($vprecs) > 0) {
                  $recid = $vprec[0]['elementid'];
                  $valid_obj = 1;
               }
            }
            if ($overwrite or !$valid_obj) {
               // if we tried to update but it didn;t existe (valid_obj == 0) then try to create it
               if ( ($numrecs == 0) and $nonzero_only) {
                  print("There are no records for permit $vpdesno ");
               } else {
                  print("$numrecs VPDES records found for $vpdesno\n");
                  print("Cloning object $ps_tid for $vpdesno\n");
                  $output = cloneModelElement($scenid, $ps_tid, $contid, 0);
                  //$cloneresult = cloneModelElement($scenid, $elementid, $destination, 1, 1);
                  $recid = $output['elementid'];
                  $valid_obj = 1;
               }
            }
            if ($valid_obj == 0) {
               print("No Object created for $recid : $vpdesno - $vpdes_name \n" );
            } else {
               print("Setting properties on $recid : $vpdesno - $vpdes_name \n" );
               $ures = updateObjectProps($projectid, $recid, $props, 1);
               //print($ures['innerHTML'] . "\n");
               //print($ures['debugHTML'] . "\n");
               $obres = unSerializeSingleModelObject($recid);
               $thisobject = $obres['object'];
               if (method_exists($thisobject, 'reCreate')) {
                  if ($recreate) {
                     print("calling object reCreate() method ");
                     $thisobject->reCreate();
                     if ($thisobject->vpdes_db->numrows == 0) {
                        print("VPDES Current Discharge Query returned 0 recrods \n");
                        print("Query:" . $thisobject->vpdes_db->querystring . "\n");
                     }
                     $innerHTML = saveObjectSubComponents($listobject, $thisobject, $recid );
                     print("$innerHTML \n");
                  }
               }
               //die;
            }
         }
      }
   }
   print("\n");
}

?>
