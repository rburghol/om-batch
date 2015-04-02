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
   print("Usage: php basic_landuse.php scenarioid riverseg \n");
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
      $thistab = array();
      $thistab['landseg'] = $landsegid;
      $thistab['variable_name'] = 'landuse_historic';
      $luseg = getCBPLandSegmentLanduse($cbp_listobject, $cbp_scenario, $landsegid, $debug, $riverseg);
      $thistab['landuses'] = $luseg['local_annual'];

      // assemble base land use for "current" scenario
      $current = extractYearLU($luseg['local_annual'],$curyear, $minyear);
      foreach (array_keys($current) as $thislu) {
         print(print_r($current[$thislu],1) . "\n");
         $luarea = $current[$thislu][$minyear];
         $current[$thislu][$maxyear] = $luarea;
         print("$luname - Getting $curyear landuse -- Adding $thislu $minyear = $luarea and $maxyear = $luarea <br>\n"); 
      }
      //die;
      //$current['landseg'] = $landsegid;
      //$current['variable_name'] = 'landuse_current';
      
      // creating baseline land use
      $hist_lu = $luseg['local_annual'];
      $baseline = array();
      // make minimum amount of forest
      $hist_base = extractYearLU($luseg['local_annual'],1985, $baseyear);      
      $for_adjusted = adjustLandUses($hist_base, array('for'), 0.78, 'min', array());
      //print("Setting minimum forest " . print_r($for_adjusted,1) . "  \n");
      // enforce maximum amount of impervious
      $imp_adjusted = adjustLandUses($for_adjusted, array('imh','iml'), 0.0035, 'max', array('for'), $decimal_places);
      //print("Setting maximum impervious " . print_r($imp_adjusted,1) . "  \n");
      foreach (array_keys($imp_adjusted) as $thislu) {
         $luname = $imp_adjusted[$thislu]['luname'];
         $luarea = $imp_adjusted[$thislu][$baseyear];
         $baseline[] = array('luname'=>$luname, $baseyear => $luarea, $minyear => $luarea, $maxyear => $luarea);
      }
      //$baseline['landseg'] = $landsegid;
      //$baseline['variable_name'] = 'landuse_baseline';
      $lutabs = $thistab;
      // $current has current, $baseline has baseline
      
      $lrseg_id = getCOVACBPLRsegObject($listobject, $parentid, $landsegid);
      print("Seting landuse_baseline on $landsegid($lrseg_id)\n");
      setLUMatrix ($lrseg_id, 'landuse_baseline', $baseline);
      //print("Added " . print_r($baseline,1) . "  \n");
      print("Seting landuse_current on $landsegid($lrseg_id)\n");
      setLUMatrix ($lrseg_id, 'landuse_current', $current);
      //die;
   }
   print("Found " . count($lutabs) . " segments \n");
   //print("Found " . print_r($lutabs,1) . "  \n");
   //die;

   //
   print("Applying land use to $riverseg ($parentid)\n");
   //setCOVACBPLandUse($listobject, $target_scenarioid, $parentid, $lutemplateid, $lutabs, 1);
}

?>