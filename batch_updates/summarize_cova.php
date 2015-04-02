<?php

include('./xajax_modeling.element.php');
print("$basedir/lib_verify.php \n");
include("$basedir/lib_verify.php");
include("./cova/lib_batchmodel.php");

// END - default values
if (count($argv) < 3) {
   print("Usage: summarize_cova.php scenarioid riverseg runid [force_overwrite 0/1] [startdate] [enddate] \n");
   die;
}
error_reporting(E_NONE);

$scenid = $argv[1];
$riverseg = $argv[2];
$runid = $argv[3];
if (isset($argv[4])) {
   $overwrite = $argv[4];
} else {
   $overwrite = 0;
}
if (isset($argv[5])) {
   $startdate = $argv[5];
} else {
   $startdate = '1984-01-01';
}
if (isset($argv[6])) {
   $enddate = $argv[6];
} else {
   $enddate = '2005-12-31';
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
   $elemname =  getElementName($listobject, $elid);
   print("Summarizing $elemname ($elid)\n");
   $output = summarizeRun($listobject, $elid, $runid, $startdate, $enddate, $overwrite);
   $verified = $output['run_verified'];
   if ( $verified ) {
      print("Verified \n");
   } else {
      print("Failed: \n " . print_r($output,1) . "\n");
   }
}


?>