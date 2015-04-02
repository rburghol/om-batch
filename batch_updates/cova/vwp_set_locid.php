<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

if (count($argv) < 2) {
   print("Usage: php vwp_set_locid.php elementid (-1=all) \n");
   die;
}

$scenid = $argv[1];
$riverseg = $argv[2];
if ( $argv[1] > 0 ) {
   $elements = array(0=> array('elementid'=>$argv[1]));
} else {
   $segs = array();
   $listobject->querystring = " select elementid from scen_model_element where custom1 = 'cova_vwp_projinfo' ";
   print("Looking for custom1 = cova_vwp_projinfo match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   $elements = $listobject->queryrecords;
}
//print_r($segs);

foreach ($elements as $thisone) {
   print("Looking for CBP parent of $riverseg <br>\n");
   $elid = $thisone['elementid'];
   
   $obres = unSerializeSingleModelObject($elid);
   $thisobject = $obres['object'];
   // create a new container link subobject for locid
   $subres = createObjectType('cova_watershedContainerLink', array() );
   // get the location id value and name from the existing sub-object
   $newsub = $subres['object'];
   if (isset($thisobject->processors['locid'])) {
      $newsub->name = 'locid';
      $newsub->value = $thisobject->processors['locid']->value;
      // set the new subobject on the parent
      $thisobject->processors['locid'] = $newsub;
   } else {
      $newsub->name = 'locid';
      // set the new subobject on the parent
      $thisobject->addSubComp('locid', $newsub, '');
   }
   $res = saveObjectSubComponents($listobject, $thisobject, $elid, 1, 0);
}

?>
