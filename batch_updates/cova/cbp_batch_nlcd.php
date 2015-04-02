<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 37;
$cbp_scenario = 4;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

$lutemplateid = 176278;

if (count($argv) < 2) {
   print("Usage: php cbp_batch_nlcd.php scenarioid riverseg [lu_matrix_name=landuse_nlcd]\n");
   die;
}


$scenid = $argv[1];
$riverseg = $argv[2];

if (strlen($riverseg) <= 3) {
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   print("Looking for river abbreviation match <br>\n");
} else {
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg = '$riverseg' ";
   print("Looking for river full-name match <br>\n");
}
if (isset($argv[3])) {
   $lu_matrix_name = $argv[3];
} else {
   $lu_matrix_name = 'landuse_nlcd';
}
//print_r($segs);

print("$listobject->querystring ; <br>");
$listobject->performQuery();
//$listobject->showList();
//die;
$recs = $listobject->queryrecords;
$rec_nos = array();
$minyear = 1980; // low year for baseline and current landuses
$datayear = 1985;
$maxyear = 2050; // hi year for baseline and current landuses
$curyear = 2005;
$baseyear = 1850;
$decimal_places = 2;

foreach ($recs as $thisrec) {
   $riverseg = $thisrec['riverseg'];

   $segid = substr($riverseg,5,4);
   $lutabs = array();

   print("getting land use for $riverseg \n");
   $lseginfo = getCBPLandSegments($cbp_listobject, $cbp_scenario, $riverseg, $debug);
   $lsegs = $lseginfo['local_landsegs'];
   $parentid = getCOVACBPContainer($listobject, $target_scenarioid, $riverseg);
   foreach ($lsegs as $landsegid) {
      $lrseg_id = getCOVACBPLRsegObject($listobject, $parentid, $landsegid);
      print("Seting landuse_current on $landsegid($lrseg_id)\n");
      setNLCDLanduse($lrseg_id, $lu_matrix_name, $minyear, $maxyear);
      //die;
   }
   //
   print("Applying land use to $riverseg -> $landsegid ($lrseg_id)\n");
}

?>
