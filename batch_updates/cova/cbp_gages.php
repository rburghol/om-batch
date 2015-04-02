<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

$scenid = $argv[1];
$riverseg = $argv[2];

if (count($argv) < 3) {
   print("Usage: php cbp_gages.php scenarioid riverseg [overwrite=0]\n");
   die;
}

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
   $segs = array($riverseg);
}

function guessFlowGages($scenid, $segment, $areapref = 2) {
   global $listobject, $usgsdb;
// get best guess for flow gages and set them up
// look for gage that is either 1) closest to the TOTAL drainage area, or 2) closest to the LOCAL area
//$areapref = 2;
   //$seginfo = getCBPInfoByRiverseg($listobject, $segment);
   $parentinfo = getComponentCustom($listobject, $scenid, 'cova_ws_container', $segment, 1);
   $parentid = $parentinfo[0]['elementid'];
   $reachid = getCOVAMainstem($listobject, $parentid);
   $obres = unSerializeSingleModelObject($reachid);
   $thisobject = $obres['object'];
   $seginfo = array('local_area_sqmi'=>$thisobject->area, 'contrib_area_sqmi' => $thisobject->drainage_area);
   //$seginfo = getCBPInfoByRiverseg($listobject, $segment);
   print_r($seginfo);
   print("<br> \n");
   $usgs_recs = getUSGSGages($usgsdb, $listobject, $parentid, 0);
   $gdel = '';
   $text = 'Model segment area: ' . $seginfo['contrib_area_sqmi'] . "<br>\n Local Area: " . $seginfo['local_area_sqmi'] . ', <br>\n';
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
   $bestdiff = -1; // start out less than zero so the first gage will automatically get selected
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
   return array('gages'=>$usgs_recs, 'best_gage'=>$bestgage);
}

foreach ($segs as $thisseg) {
   $gages = guessFlowGages($scenid, $thisseg, 1);
   $bestgage = $gages['best_gage'];
   print("Best Gage is $bestgage on $thisseg / $parentid \n");
   if ($overwrite) {
      $parentid = getCOVACBPContainer($listobject, $scenid, $riverseg);
      $gageobjectid = getCOVAFlowGage($listobject, $parentid);
      $params = array('description'=>$text, 'staid'=>$bestgage);
      updateObjectProps($projectid, $gageobjectid, $params);
      print("Setting $bestgage as gage on $thisseg / $parentid \n");
      print(print_r($params,1) . " \n");
   }
   
   // 
   //print_r($usgs_recs);
   // get USGS gage object by custom1 query
   // set description of USGS gage object to $text describing the options
   // select the gage whose drainage area is closest to this model segments area to be the gage
   //die;
}

?>
