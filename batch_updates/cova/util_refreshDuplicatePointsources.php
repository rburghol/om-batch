<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: util_refreshDuplicatePointsources.php scenarioid [overwrite=0] [object name]\n");
   die;
}
if (isset($argv[2])) {
   $overwrite = $argv[2];
} else {
   $overwrite = 0;
}
if (isset($argv[3])) {
   $objectname = $argv[3];
} else {
   $objectname = '';
}
$levels = 20;
$scenarioid = $argv[1];

$debug = 0;
$listobject->querystring = "  select elementid, elemname ";
$listobject->querystring .= " from scen_model_element ";
$listobject->querystring .= " where scenarioid = $scenarioid ";
$listobject->querystring .= "    and elemname in (";
$listobject->querystring .= "       select elemname ";
$listobject->querystring .= "       from (";
$listobject->querystring .= "          select elemname, count(*) as numpts ";
$listobject->querystring .= "          from scen_model_element ";
$listobject->querystring .= "          where scenarioid = 37 ";
if ($objectname == '') {
   $listobject->querystring .= "           and elemname like 'VPDES%' ";
} else {
   $listobject->querystring .= "           and elemname = '$objectname' ";
}
$listobject->querystring .= "          group by elemname";
$listobject->querystring .= "       ) as foo ";
if ($objectname == '') {
   $listobject->querystring .= "       where numpts > 1 ";
}
$listobject->querystring .= "    ) ";
$listobject->querystring .= " order by elemname ";
$listobject->performQuery();
$recs = $listobject->queryrecords;
foreach ($recs as $thisrec) {
   $elementid = $thisrec['elementid'];
   $elemname = $thisrec['elemname'];
   print("Requested container for Element $elemname ($elementid): \n");
   $parent = getContainingNodeType($elementid, 0, array('custom1'=>array('cova_ws_container')), $levels, $debug);
   $pinfo = getElementInfo($listobject, $parent);
   $pname = $pinfo['elemname'];
   $custom2 = $pinfo['custom2'];
   print("Parent is $pname ($custom2)\n");
   if ($overwrite) {
      print("Calling: php cbp_initvpdes.php $scenarioid $custom2\n");
      shell_exec("php cbp_initvpdes.php $scenarioid $custom2");
   }
}

?>