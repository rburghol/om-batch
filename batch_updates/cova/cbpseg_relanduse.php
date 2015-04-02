<?php

$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here


error_reporting(E_NONE);

$scenid = $argv[1];
$riverseg = $argv[2];
if (isset($argv[3])) {
   $overwrite = $argv[3];
} else {
   $overwrite = 0;
}
if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' group by riverseg ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobbject->querystring ; <br>\n");
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

   if ( ($elid > 0) and !$overwrite ) {
      print("Element $riverseg already exists as $elid, nothing to do.<br>\n");
   } else {
      if ($elid > 0) {
         $replace_all = TRUE;
         print("Initializing land use objects for river segment $riverseg \n");
         initCOVACBPLandUse($scenid, array($riverseg), $delete_extra, $replace_all, $debug);
      }
   }
}

?>
