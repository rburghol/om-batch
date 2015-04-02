<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here
$segs = array('YP4_6720_6750','YP3_6690_6720','YP3_6470_6690','YP3_6670_6720','YP3_6700_6670','YP3_6330_6700','YP2_6390_6330');

$templateid = 176615;
$t_reachid = 207667;
$pswd_group_tid = 207671;
$lstemplateid = 207683;
$sw_tid = 207673;
$gw_tid = 207961;
$ps_contid = 209021;
$ps_tid = 209088;
$scenid = 37;
$projectid = 3;
$cbp_scen = 4;

error_reporting(E_NONE);
// sets up the basic basins
//createCOVABasin2($listobject, $cbp_listobject, $templateid, $t_reachid, $scenid, $projectd, $segs)

// sets withdrawal objects on the basins (give a full list of segs)
$segs = array('YP2_6390_6330');

createCOVAPSWDObjects($vwudsdb, $vpdesdb, $listobject, $cbp_listobject, $segs, $pswd_group_tid, $ps_contid, $sw_tid, $gw_tid, $ps_tid, $scenid, $projectid);
die;

// Set up CBP River segment land use objects
// first, get the container of these objects, so that we have the ID for later
// $pid = getCOVACBPRunoffContainer($listobject, $parentid); // the CBP model parent containerin the runoff object
// then, 
// 1.  get the land segments that are in this river segment
$delete_extra = 1;
$replace_all = TRUE;

foreach ($segs as $riverseg) {
   print("Handling $riverseg \n");
   // get the model element
   $segments = getCBPLandSegments($cbp_listobject, $cbp_scen, $riverseg, $debug);
   //print_r($segments);
   $landsegs = array();
   // populate array $landsegs with "$landseg-$riverseg" combined name
   foreach ($segments['local_landsegs'] as $landseg) {
       $landsegs["$landseg-$riverseg"] = array('landseg'=>$landseg, 'riverseg'=>$riverseg);
       print("Adding $landseg-$riverseg to queue \n");
   }
   // 2. get existing landuse/landsegment objects from model
   // array $exists is keyed with "$landseg-$riverseg" combined name
   $parentid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   $exists = getCOVACBPLanduseObjects($listobject, $parentid); // get all existing landuse/runoff objects
   // 3. delete all or non-matching land-segment objects 
   print("Existing LR Objects <br>\n");
   print_r($exists);
   foreach ($exists as $thisprops) {
      $thisone = $thisprops['lsrs'];
      if ( (!in_array($thisone, array_keys($landsegs)) and $delete_extra) or $replace_all ) { 
          if ($delete_extra or $replace_all) {
             print("Deleting $thisone (" . $thisprops['elementid'] . ")\n");
             deleteModelElement($thisprops['elementid']);
          }
      } else {
        $keepers[] = $thisone;
      }
   }
   print("Land Segments on this model segment " . print_r($landsegs,1) . "\n");
   print("Existing objects to be kept " . print_r($keepers,1) . "\n");
   
   // 4. add missing segments 
   foreach ($landsegs as $thisseg=>$thisdata) {
      print("Checking to add $thisseg \n");
      $lseg = $thisdata['landseg'];
      if (!in_array($thisseg, $keepers)) {
         addCBPLandRiverSegment($listobject, $projectid, $scenid, $lstemplateid, $lseg, $riverseg, $parentid, 1);
         
      }
   }
   


}


/*

function guessFlowGages(
// get best guess for flow gages and set them up
// look for gage that is either 1) closest to the TOTAL drainage area, or 2) closest to the LOCAL area
$areapref = 2;

foreach ($segments as $thisseg) {
   $seginfo = getCBPInfoByRiverseg($listobject, $thisseg);
   print_r($seginfo);
   $parentinfo = getComponentCustom($listobject, $scenid, 'cova_ws_container', $thisseg, 1);
   $parentid = $parentinfo[0]['elementid'];
   $vpdes_recs = getVPDESDischarges($vpdesdb, $listobject, $parentid, 1);
   // 
   //print_r($vpdes_recs);
   print("<br> \n");
   $usgs_recs = getUSGSGages($usgsdb, $listobject, $parentid, 0);
   $gdel = '';
   $text = 'Model segment area: ' . $seginfo['local_area_sqmi'] . ', ';
   $text .= 'Candidate Gages: ';
   
   // look for gage that is either 1) closest to the TOTAL drainage area, or 2) closest to the LOCAL area
   switch ($areapref) {
      case 1:
         $targetarea = $seginfo['contrib_area_sqmi'];
      break;
      
      case 2:
         $targetarea = $seginfo['local_area_sqmi'];
      break;
   }
   $bestdiff = -1; // start out less than zero so the first gage wil automatically get selected
   $bestgage = '';
   foreach ($usgs_recs as $thisgage) {
      $text .= $gdel . $thisgage['station_nu'] . " (" . number_format($thisgage[drainage_a],2,'.',',') . ")";
      $gdel = ', ';
      $targdiff = abs($thisgage[drainage_a] - $targetarea);
      if ( ($targdiff < $bestdiff) or ($bestdiff < 0) ) {
         $bestdiff = $targdiff;
         $bestgage = $thisgage['station_nu'];
      }
   }
   $text .= " Best Gage: " . $bestgage;
   print($text . "\n");
   $gageobjectid = getCOVAFlowGage($listobject, $parentid);
   $params = array('description'=>$text, 'staid'=>$bestgage);
   updateObjectProps($projectid, $gageobjectid, $params);
   // 
   //print_r($usgs_recs);
   // get USGS gage object by custom1 query
   // set description of USGS gage object to $text describing the options
   // select the gage whose drainage area is closest to this model segments area to be the gage
   //die;
}

*/

?>
