<?php

$userid = 1;
include_once('xajax_modeling.element.php');
//include_once('lib_batchmodel.php');
error_reporting(E_ALL);

if (count($argv) < 2) {
   print("Usage: php cbp_setShapeLSegs.php elementid \n");
   die;
}
$elid = $argv[1];
// if this is a riverseg object, it will set the shape on it
$props = getElementInfo($listobject, $elid);
$c2 = $props['custom2'];
$listobject->querystring = "select asText(transform(memgeomunion(the_geom),4326)) as wkt_geom from sc_cbp53 where riverseg = '$c2' ";
print($listobject->querystring . "\n");
$listobject->performQuery();
if ($listobject->numrows > 0) {
   $wktgeom = $listobject->getRecordValue(1,'wkt_geom');
   setElementGeometry($elid, 3, $wktgeom, 4326);
}

?>
