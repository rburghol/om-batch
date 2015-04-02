<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here


error_reporting(E_NONE);
if (count($argv) < 5) {
   print("Usage: php cbpseg_copysubcomps.php scenarioid srcid riverseg object_class (landsegs,upstream,channel) [subcomps] \n");
   die;
}
$scenid = $argv[1];
$srcid = $argv[2];
$riverseg = $argv[3];
$destobs = $argv[4];
// get the list of all sub-comps from the source
$obres = unserializeSingleModelObject($srcid);
$srcob = $obres['object'];
$name = $srcob->name;
if (isset($argv[5])) {
   $subcomps = split(',', $argv[5]); 
} else {
   $subcomps = array_keys($srcob->processors);
}

print("sub-components on source object - $name: \n");
print_r($subcomps);
print(" \n");
print("Copying info from $name \n");

if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' group by riverseg ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
} else {
   $segs = array($riverseg);
}


foreach ($segs as $riverseg) {
   // Locate any existing model segments with this name
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);

   if ($elid > 0) {
      print("Copying vars to land use objects for river segment $riverseg \n");
      switch ($destobs) {
         case 'landsegs':
            $targets = getCOVACBPLanduseObjects($listobject, $elid);
         break;
         
         case 'upstream':
            $targ = getCOVAUpstream($listobject, $elid);
            $targets = array(0=>array('elementid'=>$targ));
         break;
         
         case 'channel':
            $targ = getCOVAMainstem($listobject, $elid);
            $targets = array(0=>array('elementid'=>$targ));
         break;
         
         
      }
      foreach ($targets as $thisseg) {
         $i = 0;
         $destid = $thisseg['elementid'];
         foreach ($subcomps as $thiscomp) {
            print("Trying to add Sub-comp $thiscomp to Element $destid <br>\n");
            print("copySubComponent($srcid, $thiscomp, $destid, $thiscomp)<br>\n");
            $cr = copySubComponent($srcid, $thiscomp, $destid, $thiscomp);
            print("$cr<br>\n");
            print("Sub-comp $thiscomp added to Element $destid <br>\n");
            $i++;
         }
      }
   }
}

?>
