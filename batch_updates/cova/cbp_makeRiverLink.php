<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

if (count($argv) < 4) {
   print("Usage: php cbp_makeRiverLink.php scenarioid upstream_riverseg downstream_riverseg [linktype=upstream (or tributary)]\n");
   die;
}

$scenid = $argv[1];
$us_seg = $argv[2];
$ds_seg = $argv[3];
if (isset($argv[4])) {
   $linktype=$argv[4];
} else {
   $linktype = 'upstream';
}

$us_id = getCOVACBPContainer($listobject, $scenid, $us_seg);
print("River Segment $us_seg found as elementid $us_id \n");
$ds_id = getCOVACBPContainer($listobject, $scenid, $ds_seg);
print("River Segment $ds_seg found as elementid $ds_id \n");

$parentinfo = getCOVACBPParent($scenid, $riverseg);
$parentid = $parentinfo['elementid'];

if ( ($ds_id <> -1) and ($us_id <> -1) ) {
   switch ($linktype) {
      case 'upstream':
         // now link to the "Upstream Tributaries" component in the parent container
         $us_container = getCOVAUpstream($listobject, $ds_id);
         $elementname = getElementName($listobject, $ds_id);
         print("Linking $us_seg as upstream trib to $elementname ($ds_seg) <br>\n");
         print(" createObjectLink($projectid, $scenid, $us_id, $us_container, 1) \n");
         createObjectLink($projectid, $scenid, $us_id, $us_container, 1);
      break;

      case 'tributary':
         // now link to the "Upstream Tributaries" component in the parent container
         $us_container = getCOVATribs($listobject, $ds_id);
         $elementname = getElementName($listobject, $ds_id);
         $cbpname = getCustom2($listobject, $parentid);
         print("Linked $us_seg into $elementname ($ds_seg) <br>\n");
         print(" createObjectLink($projectid, $scenid, $us_id, $us_container, 1) \n");
         createObjectLink($projectid, $scenid, $us_id, $us_container, 1);
         break;
   }
} else {
   print("No upstream container for $riverseg exists in model domain $scenid. <br>\n");
}


?>
