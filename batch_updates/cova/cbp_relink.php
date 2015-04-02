<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);

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
   $segs = array($riverseg);
}
//print_r($segs);

foreach ($segs as $riverseg) {

   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   print("Looking for CBP parent of $riverseg <br>\n");
   $parentinfo = getCOVACBPParent($scenid, $riverseg);
   $parentid = $parentinfo['elementid'];
   $linktype = $parentinfo['linktype'];
   if ($parentid <> -1) {
      switch ($linktype) {
         case 'upstream':
            // now link to the "Upstream Tributaries" component in the parent container
            $us_container = getCOVAUpstream($listobject, $parentid);
            $elementname = getElementName($listobject, $parentid);
            $cbpname = getCustom2($listobject, $parentid);
            print("Linked $riverseg into $elementname ($cbpname) <br>\n");
            createObjectLink($projectid, $scenid, $elid, $us_container, 1);
         break;

         case 'tidal':
            // now link to the "Upstream Tributaries" component in the parent container
            $us_container = getCOVATribs($listobject, $parentid);
            $elementname = getElementName($listobject, $parentid);
            $cbpname = getCustom2($listobject, $parentid);
            print("Linked $riverseg into $elementname ($cbpname) <br>\n");
            createObjectLink($projectid, $scenid, $elid, $us_container, 1);
         break;
      }
   } else {
      print("No upstream container for $riverseg exists in model domain $scenid. <br>\n");
   }
}

?>
