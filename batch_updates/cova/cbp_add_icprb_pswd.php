<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

if (count($argv) < 3) {
   print("Usage: php cbp_add_icprb_pswd.php scenarioid riverseg [overwrite=0] \n");
   die;
}

$scenid = $argv[1];
$riverseg = $argv[2];
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
   $segs = split(',',$riverseg);
}
//print_r($segs);

foreach ($segs as $riverseg) {
   // get the CBP node container
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   print("Located model container - $riverseg ($elementid) <br>\n");
   $contid = getCOVAPS($listobject, $elid);
   $elementname = getElementName($listobject, $contid);
   print("Located PS/WD container - $elementname ($contid) <br>\n");
   // fetch and delete the existing versions if we requested an overwrite
   $ps = getChildCustomType($listobject, $contid, 'cova_icprb_ps', '');
   $wd = getChildCustomType($listobject, $contid, 'cova_icprb_wd', '');
   if ( ($overwrite) ) {
      print("Deleting point source element $ps \n");
      deleteModelElement($ps);
      print("Deleting withdrawal element $wd \n");
      deleteModelElement($wd);
   }
   if ($overwrite or ($ps == -1) ) {
      // create ps and wd query objects
      $output = cloneModelElement($scenid, $icprb_pstid, $contid, 0);
      $psid = $output['elementid'];
      // set basic properrties on ps and wd
      print("Setting properties on ICPRB Point Source $psid \n");
      $params = array('name'=>"ICPRB Point Source", 'custom1'=>'cova_icprb_ps');
      updateObjectProps($projectid, $psid, $params);
      // remote link to the_geom from parent object
      $pslink = createObjectLink($projectid, $scenid, $elid, $psid, 3, 'the_geom', 'the_geom');
   }
   if ($overwrite or ($wd == -1) ) {
      // create ps and wd query objects
      $output = cloneModelElement($scenid, $icprb_wdtid, $contid, 0);
      $wdid = $output['elementid'];
      print("Setting properties on ICPRB Withdrawal $wdid \n");
      $params = array('name'=>"ICPRB Withdrawal", 'custom1'=>'cova_icprb_wd');
      updateObjectProps($projectid, $wdid, $params);
      // remote link to the_geom from parent object
      $wdlink = createObjectLink($projectid, $scenid, $elid, $wdid, 3, 'the_geom', 'the_geom');
   }
}

?>
