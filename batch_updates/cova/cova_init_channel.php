<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);
if (count($argv) < 2) {
   print("This routine sets channel properties from the parent shape in the COVA Framework.  All queried attributes pertain to the parent container which is an object of type 'cova_ws_subnodal' or 'cova_ws_container' \n");
   print("Usage: php cova_init_channel.php scenarioid [elementid] [elemname] [custom2]  \n");
   die;
}

$scenid = $argv[1];

if (isset($argv[2])) {
   $elementid = $argv[2];
} else {
   $elementid = '';
}
if (isset($argv[3])) {
   $elemname = $argv[3];
} else {
   $elemname = '';
}
if (isset($argv[4])) {
   $custom2 = $argv[4];
} else {
   $custom2 = '';
}

$listobject->querystring = "  select elementid, elemname from scen_model_element where scenarioid = $scenid ";
$listobject->querystring .= " AND custom1 in ('cova_ws_subnodal', 'cova_ws_container') ";
if ($elementid <> '') {
   $listobject->querystring .= " AND elementid = $elementid ";
}
if ($elemname <> '') {
   $listobject->querystring .= " AND elemname = '$elemname' ";
}
if ($custom2 <> '') {
   $listobject->querystring .= " AND custom2 = '$custom2' ";
}
print("$listobject->querystring ; <br>");
$listobject->performQuery();
//$listobject->showList();(""

$recs = $listobject->queryrecords;

foreach ($recs as $thisrec) {

   $elid = $thisrec['elementid'];
   // get the child river channelobject
   $cid = getCOVAMainstem($listobject, $elid);
   // get the area characteristics of the parent object
   $listobject->querystring = " select (area(transform(poly_geom,26918)) * 0.386102159 / (1000.0 * 1000.0)) as area_sqmi from scen_model_element where elementid = $elid ";
   print("$listobject->querystring \n");
   $listobject->performQuery();
   $area_sqmi = $listobject->getRecordValue(1,'area_sqmi');
   $props = array('area'=>$area_sqmi);
   print("Setting props on river channel: " . print_r($props,1) . "\n");
}

?>
