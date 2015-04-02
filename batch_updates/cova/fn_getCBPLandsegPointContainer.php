<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

global $userid;
// get outlet for terminal CBP segment
// OR, set them up manually as in here
$segs = array('YP4_6720_6750','YP3_6690_6720','YP3_6470_6690','YP3_6670_6720','YP3_6700_6670','YP3_6330_6700','YP2_6390_6330');

$templateid = 176615;
$t_reachid = 207667;
$pswd_group_tid = 207671;
$lstemplateid = 207683;
$sw_tid = 207673;
$gw_tid = 207961;
$ps_contid = 209021;
$ps_tid = 209088;
$projectid = 3;
$cbp_scen = 4;

error_reporting(E_NONE);
if (count($argv) < 3) {
   print("Usage: php fn_getCBPLandsegPointContainer.php latdd londd [debug=0]\n");
   die;
}

$latdd = $argv[1];
$londd = $argv[2];
if (isset($argv[3])) {
   $debug = $argv[3];
}

$landseg = getCOVACBPLandsegPointContainer($latdd, $londd, $debug);
print("Land seg = " . $landseg . "\n");


?>
