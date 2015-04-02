<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here


error_reporting(E_NONE);

if (count($argv) < 2) {
   print("Usage: php get_element_area.php elementid \n");
   die;
}

$elementid = $argv[1];
$listobject->querystring = " select sum(area2d(transform(poly_geom,26918)) * 3.861021e-07) as area_sqmi from scen_model_element where elementid 
= 
$elementid ";
print("$listobject->querystring ; \n");
$listobject->performQuery();

print("Result = " . $listobject->getRecordValue(1,'area_sqmi') . " sq. miles \n");

?>
