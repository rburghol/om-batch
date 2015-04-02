<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');

if (count($argv) < 2) {
   print("Usage: fn_getWDgeom.php elementid \n");
   die;
}

$recid = $argv[1];

$debug = 1;

error_reporting(E_ERROR);
$obres = unSerializeSingleModelObject($recid);
$thisobject = $obres['object'];
if (method_exists($thisobject, 'getWithdrawalLocation')) {
   print("calling object getWithdrawalLocation() method ");
   $wkt_geom = $thisobject->getWithdrawalLocation();
   print("$wkt_geom \n");
   $prop_array = array('the_geom'=>$wkt_geom);
   $res = updateObjectProps($projectid, $recid, $prop_array, 1);
   $innerHTML = $res['innerHTML'];
   //print(print_r($res,1) . "\n");
   print($innerHTML . "\n");
} else {
   print("...failed.  Object has not getWithdrawalLocation() method \n");
}
?>
