<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

global $userid;
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
$projectid = 3;
$cbp_scen = 4;

error_reporting(E_NONE);
if (count($argv) < 5) {
   print("Usage: php add_localtrib.php scenarioid latdd londd trib_name [# of extra NHD+ basins] [add below elementid] [withdrawal category] [withdrawal name] \n");
   die;
}
$scenid = $argv[1];
$latdd = $argv[2];
$londd = $argv[3];
$tribname = $argv[4];
if (isset($argv[5])) {
   $extra_basins = $argv[5];
} else {
   $extra_basins = 0;
}
if (isset($argv[6])) {
   $parentid = $argv[6];
} else {
   $parentid = -1;
}
if (isset($argv[7])) {
   // adding a withdrawal category
   $w_cat = $argv[7];
} else {
   $w_cat = '';
}
if (isset($argv[8])) {
   // adding a withdrawal
   $w_name = $argv[8];
} else {
   $w_name = '';
}
$landseg = getCOVACBPLandsegPointContainer($latdd, $londd);
print("Land seg = " . $landseg . "\n");
//die;
// Locate any existing model segments that contain the tributary outlet point - if multiple containing segments are found, assume that there is a trib within a trib situation, and use the containing segment with the smallest drainage area. 
if ($parentid == -1) {
   $els = findExistingCOVAContainers($listobject, $scenid, $latdd, $londd);
} else {
   $els = array(0=>array('elementid'=>$parentid));
}
$overwrite = 1;
if (count($els) > 0) {
   $parentid = $els[0]['elementid'];
   $tribcontainer = getCOVATribs($listobject, $parentid);
   print("Tributary container = $tribcontainer \n");
   // next, create the clone that is needed
   $elid = createCOVALocalTrib($listobject, $cbp_listobject, $tribname, $templateid, $scenid, $projectid, $tribcontainer, $overwrite); 
   print("Model created with ID $elid \n");
   // get the shape for this trib
   print("Retrieving NHD+ Shape for $elid \n");
   $basininfo = getMergedNHDBasin($usgsdb, $latdd, $londd, $extra_basins);
   print(" NHD+ Shape Retrieved \n");
   $wkt_geom = $basininfo['the_geom'];
   $comid = $basininfo['outlet_comid'];
   $seglist = $basininfo['flow_segments'];
   print("NHD+ Shape for $elid = " . substr($wkt_geom,0,64) . " \n");
   // set the shape
   print("Setting Shape for $elid \n");
   setElementGeometry($elid, 3, $wkt_geom);
   // retrieve area rfrom this new shape
   $info = getElementInfo($listobject, $elid, $debug);
   $local_area = $info['area_sqmi'];
   $nhdinfo = findNHDSegInfo($usgsdb, $comid, $debug, 'sqmi');
   $total_area = $nhdinfo['cumdrainag'];
   // find VWUDS withdrawals/VPDES Discharges in this trib
   createCOVAPSWDSingle($elid, $scenid, $projectid);
   // add custom withdrawal type if need be
   // set river segment properties
   $channel_props = getNHDChannelInfo($usgsdb, $comid, $seglist, 'ft');
   $cid = getCOVAMainstem($listobject, $elid);
   $params = array('area'=>$local_area, 'drainage_area' => $total_area, 'slope' => $channel_props['c_slope'], 'length' => round($channel_props['reachlen']));
   print("updateObjectProps($projectid, $cid, " . print_r($params,1) . " ); <br>\n");
   updateObjectProps($projectid, $cid, $params);
   
   if ($w_cat <> '') {
      //error_reporting(E_ALL);
      addGenericWaterUser($scenid, $elid, $w_cat, $w_name, 'SW', 1);
   }
   
   $landseg = getCOVACBPLandsegPointContainer($listobject, $latdd, $londd);
   // get the default landseg object created with this
   $lsobj = getCOVACBPLanduseObjects($listobject, $elid);
   if (count($lsobj) == 0) {
      print("Failed to locate a landseg object.\n");
   } else {
      $ls_elid = $lsobj[0]['elementid'];
      // set the landseg id on this object
      $props = array('name'=>$landseg, 'id2'=>$landseg, 'custom2'=>$landseg);   
      updateObjectProps($projectid, $ls_elid, $props);
   }
   // remove VWUDS withdrawals/VPDES Discharges in this trib from parent container
   // get NHD Land Use
   // set land use on new object
   // create baseline version of land use
} else {
   print("Looking for CBP Model containing river segment\n");
   $riverseg = getCOVACBPPointContainer($listobject, $latdd, $londd);
   print("River Segment: $riverseg \n");
   $segs = array($riverseg);
   createCOVABasin2($scenid, $projectid, $segs);
   createCOVAPSWDObjects($segs, $scenid, $projectid);
   $delete_extra = 1;
   $replace_all = TRUE;
   initCOVACBPLandUse($scenid, $segs, $delete_extra, $replace_all, $debug);

}


?>
