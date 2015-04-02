<?php
$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
//include_once("./lib_batchmodel.php");

if (count($argv) < 1) {
   print("Usage: add_proposed_wd.php scenarioid vwp_proj_elementid \n");
   die;
}

$scenid = $argv[1];
$elid = $argv[2];

# instantiate the element
print("Loading Template Object $elid \n");
$res = unserializeSingleModelObject($elid);
$proj_info = $res['object'];
//print("Error: " . $res['error'] . "\n");
//print("Debug: " . $res['debug'] . "\n");
print("Object Class: " . get_class($proj_info) . "\n");
# get the prospective container 
$containerid = intval($number = preg_replace("/[^0-9]/", '', $proj_info->getProp('locid','equation')));
print("Found Model Container $containerid \n");
# get the proposed sub-container
$destparent = getChildComponentCustom($listobject, 'cova_proposed', '', 1, $containerid);
$destid = $destparent[0]['elementid'];
print("Found element $destid for proposed withdrawals \n");
# clone the basic proposed project container object
$props = array('name' => "Project: " . $proj_info->name 
);
print("Creating project: " . print_r($props,1) . "\n");
//$newproj = cloneModelElement($scenid, $cova_pp_template_id, $destid,0);
//$newid = $newproj['elementid'];
//updateObjectProps(3, $newid,$props);

// now, copy a withdrawal template into the new project container
//$newwd = cloneModelElement($scenid, $sw_tid,$newid,0);
$props = array('name' => $proj_info->name,
   'max_wd_annual' => $proj_info->getProp('annual_mg','equation'),
   'max_wd_monthly' => $proj_info->getProp('maxmonth_mg','equation'),
   'max_wd_daily' => $proj_info->getProp('max_mgd','equation'),
);
?>
