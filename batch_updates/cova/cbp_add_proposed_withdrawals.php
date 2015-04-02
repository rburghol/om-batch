<?php

// get the desired river segs to add the meteorology to 
// find the child "cova_meteorology" 
// copy the sub-comps from the template for cova_meteorology
// add a child for the CBP segment - set the id2 param to the riverseg, and the scid param to scenarioid
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

$scenid = $argv[1];
$riverseg = $argv[2];
if (count($argv) < 3) {
   print("Adds a container for '1.1 Proposed Withdrawals & Discharges' to the given river segment\n");
   print("Usage: php cbp_add_proposed_withdrawals.php scenarioid riverseg (or abbrev) [overwrite def=0] \n");
   die;
}
if (isset($argv[3])) {
   $overwrite = $argv[3];
} else {
   $overwrite = 0;
}
if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
} else {
   $segs = array($riverseg);
}
//print_r($segs);

// get the list of all sub-comps from the source 
$subcomps = listObjectSubComps($cova_pp_container);

foreach ($segs as $riverseg) {
   print("Updating Proposed Withdrawals group on $riverseg \n");
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   print("Found ElementID $elid for Riverseg $riverseg \n");
   $destparent = getChildComponentCustom($listobject, 'cova_proposed', '', 1, $elid);
   $destparentid = $destparent[0]['elementid'];
   if (!$destparentid or $overwrite) {
      if ($overwrite and $destparentid) {
         deleteModelElement($destparentid);
      }
      print("Adding $cova_pp_container \n");
      // create a container for proposed withdrawals
      $res = cloneModelElement($scenid, $cova_pp_container, $elid, 0); 
      $destparentid = $res['elementid'];
   }
}

?>
