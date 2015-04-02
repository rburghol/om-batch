<?php

error_reporting(E_ERROR);
$userid = 1;
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

error_reporting(E_ERROR);
if (count($argv) < 2) {
   print("Usage: php cbp_init_channel.php scenarioid riverseg [overwrite] \n");
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
   $segs = array($riverseg);
}
//print_r($segs);

foreach ($segs as $riverseg) {

   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   $cid = getCOVAMainstem($listobject, $elid);
   setCOVACBPReachProps($projectid, $cbp_listobject, $listobject, $riverseg, $cid, $debug);
   print("\n");
}

?>
