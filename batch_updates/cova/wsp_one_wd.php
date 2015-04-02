<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('./xajax_modeling.element.php');
//include_once('lib_batchmodel.php');
global $unserobjects;
$projectid = 3;

//error_reporting(E_ALL);

if (count($argv) < 2) {
   print("Usage: php wsp_one_wd.php elementid function (1 = recreate) \n");
   die;
}

$recid = $argv[1];
$function = $argv[2];

$obres = unSerializeSingleModelObject($recid);
$thisobject = $obres['object'];
print("Initializing $wdname ($elementid) \n");
error_log("Pre-Update Processor Names: " . print_r(array_keys($thisobject->processors),1));
if (method_exists($thisobject, 'getWithdrawalLocation')) {
	print("calling object getWithdrawalLocation() method ");
	$wkt_geom = $thisobject->getWithdrawalLocation();
	print("Setting Geometry \n");
	$prop_array = array('the_geom'=>$wkt_geom);
	$res = updateObjectProps($projectid, $recid, $prop_array, 1);
} else {
	error_log(" Method getWithdrawalLocation is not defined on $wdname ($elementid) \n");
}
//error_log("Post-Update Processor Names: " . print_r(array_keys($thisobject->processors),1));
// re-retrieve
$obres = unSerializeSingleModelObject($recid);
$thisobject = $obres['object'];
error_log("Pre-Update Processor Names: " . print_r(array_keys($thisobject->processors),1));
switch ($function) {
   case 1:
	if (method_exists($thisobject, 'reCreate')) {
		print("calling object reCreate() method ");
		$thisobject->vwuds_db = $vwudsdb;
		//$thisobject->wake();
		$thisobject->reCreate();
		$innerHTML = saveObjectSubComponents($listobject, $thisobject, $recid );
		//updateObjectPropList($recid, $thisobject, $debug );
		//print("$innerHTML \n");
		error_log("Post-reCreate Error Log: " . $thisobject->debugstring);
		//error_log("Post-reCreate Processor Names: " . print_r(array_keys($thisobject->processors),1));
	} else {
		print("Recreation failed.  $wdname ($elementid)  Object has no reCreate() method \n");
	}
	break;
}

?>
