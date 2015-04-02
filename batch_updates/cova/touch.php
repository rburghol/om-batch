<?php

$userid = 1;
include_once('/var/www/html/wooommdev/xajax_modeling.element.php');
//include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here


error_reporting(E_ERROR);

if (count($argv) < 2) {
   print("Usage: php touch.php elementid \n");
   die;
}

$elementid = $argv[1];

if ($destination > 0) {
   // get scenarioid from destination parent
   $info = getElementInfo($listobject, $elementid);
   $scenid = $info['scenarioid'];
   print("Touching: " . print_r($info,1) . "\n");
}
//die;

$loadres = loadModelElement($elementid, array(), 0);
$thisobject = $loadres['object'];
if (is_object($thisobject)) {
   print("Found object of class " . get_class($thisobject) . "\n");
}
saveObjectSubComponents($listobject, $thisobject, $elementid, 1);
print("Geometry: " . $thisobject->the_geom . "\n");
$res = updateObjectProps($projectid, $elementid, $pparms, 0);
print("Result of save: " . print_r($res,1) . "\n");

?>
