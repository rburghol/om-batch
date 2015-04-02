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
   print("Usage: php cbp_add_meteorology.php scenarioid riverseg (or abbrev) [recreate def=1] [overwrite def=0] \n");
   die;
}
if (isset($argv[3])) {
   $recreate = $argv[3];
} else {
   $recreate = 1;
}
if (isset($argv[4])) {
   $overwrite = $argv[4];
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
$subcomps = listObjectSubComps($cova_met_container);

foreach ($segs as $riverseg) {
   print("Updating Meteorology group on $riverseg \n");
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   $destparent = getChildComponentCustom($listobject, 'cova_meteorology', '', 1, $elid);
   $destparentid = $destparent[0]['elementid'];

   // copy the sub-components over from the base meteorology object if requested:
   if ($recreate) {
      foreach ($subcomps as $thiscomp) {
         print("Trying to add Sub-comp $thiscomp to Element $destparentid <br>\n");
         print("copySubComponent($cova_met_container, $thiscomp, $destparentid, $thiscomp)<br>\n");
         $cr = copySubComponent($cova_met_container, $thiscomp, $destparentid, $thiscomp);
         print("$cr<br>\n");
         print("Sub-comp $thiscomp added to Element $destparentid <br>\n");
         $i++;
      }
   }
   // copy the CBP meteorology template child to this container
   // firsst chck to see if it already has one, if so, only overwrite if explicitly requested
   $metchilds = getChildComponentCustom($listobject, 'cbp_meteorology', '', 1, $destparentid);
   if ( (count($metchilds) == 0) or ($overwrite) ) {
      foreach ($metchilds as $oldmet) {
         print("Deleting Model Object " . $oldmet['elementid'] . "\n");
         deleteModelElement($oldmet['elementid']);
      }
      $cbp_copy_params = array(
         'projectid'=>$projectid,
         'dest_scenarioid'=>$scenid,
         'elements'=>array($cbp_met_template_id),
         'dest_parent'=>$destparentid
      );
      $output = copyModelGroupFull($cbp_copy_params);
      $cbpmetid = $output['elementid'];

      // set the id2 param to the riverseg, and the scid param to scenarioid
      $params = array('scid'=>4, 'id2'=>$riverseg);
      updateObjectProps(null, $cbpmetid, $params);
   }
}

?>
