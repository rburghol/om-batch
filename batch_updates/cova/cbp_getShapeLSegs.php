<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');
error_reporting(E_ALL);

if (count($argv) < 2) {
   print("Usage: php cbp_getShapeLSegs.php elementid [debug=0]\n");
   die;
}
$elid = $argv[1];
if (isset($argv[2])) {
   $debug = $argv[2];
} else {
   $debug = 0;
}
$minyear = 1980;
$maxyear = 2050;
// get the parent shape
// get the land segs that overlap it, and the intersected shape
// apply to the land segs if they exist

// actually should get the existing shape on the model element, intersect it with NHDPlus basins, calaculating overlap %s, and then weight the resulting land use query accordingly
$wktgeom = getElementShape($elid);
$lsegs = getCBPLandSegIntersection($cbp_listobject, $wktgeom, -1, $debug);
foreach ($lsegs as $seg) {
   $landseg = $seg['fipsab'];
   $lobj = getCOVACBPLRsegObject($listobject, $elid, $landseg);
   print($landseg .  " - $lobj" . "\n");
   if ($lobj == -1) {
      // could not find suitable child object, so set land use variable on parent
      error_log("Could not find child object for land use, so setting land use variable on $elid");
      $lobj = $elid;
   }
   setElementGeometry($lobj, 3, $seg['wktgeom'], 4326);
   $lu = getNHDLandUseWKT($usgsdb, $seg['wktgeom'], 'acres');
   print(print_r($lu,1) . "\n");
   $lr = array();
   foreach ($lu as $thislu => $thisarea) {
      if (substr($thislu,0,4) == 'nlcd') {
         $lr[] = array('luname'=>$thislu, $minyear => round($thisarea,3), $maxyear => round($thisarea,3));
      }
   }
   setLUMatrix ($lobj, 'landuse_nlcd', $lr);
}


?>
