<?php

$userid = 1;
include_once('xajax_modeling.element.php');
//include_once('lib_batchmodel.php');
error_reporting(E_ERROR);

if (count($argv) < 2) {
   print("Usage: php cbp_calcNLCDLanduse.php elementid [lu_matrix_name=landuse_nlcd] [mode=direct, cova]\n");
   print("mode: cbp means search COVA framework from the elementid as root, direct = operate on this elementid directly. \n");
   die;
}
$elid = $argv[1];
if (isset ($argv[2])) {
   $lu_matrix_name = $argv[2];
} else {
   $lu_matrix_name = 'landuse_nlcd';
}
if (isset ($argv[3])) {
   $mode = $argv[3];
} else {
   $mode = 'direct';
}

$nlcd_cbp_lumap = array(
   'nlcd_11' => array('wat'=>1.0),
   'nlcd_12' => array(),
   'nlcd_21' => array('pul'=>0.45, 'iml'=>0.55),
   'nlcd_22' => array('puh'=>0.1, 'imh'=>0.9),
   'nlcd_23' => array('imh'=>1.0),
   'nlcd_23' => array('imh'=>1.0),
   'nlcd_31' => array('bar'=>1.0),
   'nlcd_32' => array('ext'=>1.0),
   'nlcd_33' => array('hvf'=>1.0),
   'nlcd_41' => array('for'=>1.0),
   'nlcd_42' => array('for'=>1.0),
   'nlcd_43' => array('for'=>1.0),
   'nlcd_51' => array('hyw'=>1.0),
   'nlcd_61' => array('hyo'=>1.0),
   'nlcd_71' => array('hyo'=>1.0),
   'nlcd_81' => array('pas'=>1.0),
   'nlcd_82' => array('hom'=>1.0),
   'nlcd_84' => array('hyo'=>1.0),
   'nlcd_85' => array('puh'=>1.0),
   'nlcd_91' => array('for'=>1.0),
   'nlcd_92' => array('for'=>1.0)
);

$minyear = 1980;
$maxyear = 2050;
// get the parent shape
// actually should get the existing shape on the model element, intersect it with NHDPlus basins, calaculating overlap %s, and then weight the resulting land use query accordingly
$wktgeom = getElementShape($elid);

// now, if the parent shape is a COVA container, we need to actually locate the landseg object underneath it
// if not, we assume that the $elid we have been given is the destination of the new landuse matrix
switch ($mode) {
   case 'direct':
   // do nothing
   break;
   
   case 'cova':
   // assume that you have been given the parent container
   // find the land seg that contains the point
   // currently does nothing, but should later get the land seg object(s) that are needed and populate them with acreage info
   $lsobj = getCOVACBPLanduseObjects($listobject, $elid);
   if (count($lsobj) == 0) {
      print("Failed to locate a land use object.  Terminating.\n");
      die;
   }
   $elid = $lsobj[0]['elementid'];
   break;
   
}
// get the land segs that overlap it, and the intersected shape
// apply to the land segs if they exist


$overlaps = checkOverlap($usgsdb, $wktgeom);
foreach ($overlaps as $thisnhd) {
   print("COM ID: " . $thisnhd['comid'] . " Original Area: " . $thisnhd['areasqkm_orig'] . " overlap %: " . $thisnhd['ratio'] . "\n");
   $total_area += $thisnhd['areasqkm_orig'] * $thisnhd['ratio'];
}
print("Total overlapping area " . number_format($total_area, 2) . " sq. km\n");

$lu = getNHDLandUseWKT($usgsdb, $wktgeom, 'acres');
print(print_r($lu,1) . "\n");
$lr = array();
$maplu = array();
foreach ( $lu as $thislu => $thisarea ) {
   // check for entry in mapping array
   // if found, perform the map,
   // then check to see if we already have an entry for it, if so, add it to the total, if not create new entry for this lu class
   if (substr($thislu,0,4) == 'nlcd') {
      if (isset($nlcd_cbp_lumap[$thislu])) {
         foreach ($nlcd_cbp_lumap[$thislu] as $luname => $lupct) {
            if (!isset($maplu[$luname])) {
               $maplu[$luname] = 0.0;
            }
            $maplu[$luname] += $thisarea * $lupct;
         }
      }
   }

}
foreach ( $maplu as $thislu => $thisarea ) {
   $lr[] = array('luname'=>$thislu, $minyear => round($thisarea,3), $maxyear => round($thisarea,3));

}

switch ($mode) {
   case 'direct':
   // do nothing
   break;
   
   case 'cova':
   // assume that you have been given the parent container
   // currently does nothing, but should later get the land seg object(s) that are needed and populate them with acreage info
   break;
   
}

setLUMatrix ($elid, $lu_matrix_name, $lr);

?>
