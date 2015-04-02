<?php

$userid = 1;
include_once('/var/www/html/wooommdev/xajax_modeling.element.php');
//include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here


error_reporting(E_ERROR);

if (count($argv) < 2) {
   print("Usage: php callCreate.php elementid \n");
   die;
}

$elementid = $argv[1]; 

$unser = unserializeSingleModelObject($elementid);
$thisobject = $unser['object'];
if (method_exists($thisobject, 'reCreate')) {
   //error_log("reCreate() method exists");
   $thisobject->reCreate();
   $innerHTML .= saveObjectSubComponents($listobject, $thisobject, $elementid );
   updateObjectPropList($elementid, $thisobject, $debug);
   //error_log("Finished reCreate() ");
}


?>
