<?php

error_reporting(E_ALL);
include('./xajax_modeling.element.php');
include("./lib_verify.php");

$scid = 28;
$run_ids = array(1,2);
$startdate = '1984-01-01';
$enddate = '1984-12-31';
// cache_date tells us how far back to accept data as being valid and no need to re-run
$cache_date = '2010-10-05'; // set to -1 to do no caching
$start_year = intval(date('Y', strtotime($startdate)));
$end_year = intval(date('Y', strtotime($enddate)));
$years = $end_year - $start_year;
// specify max models to run at a time
$max_simultaneous = 6;
$sleep_factor = $years * 1; // give it some time to accumulate  a cache
$abort_zombie = 1; // whether or not to abort a run if a child process dies during execution

if (isset($argv[1])) {
   $elname = $argv[1];
} else {
   print ("You must include an outlet element name or ID for this to run");
   die;
}
if (isset($argv[2])) {
   $intype = $argv[2];
} else {
   $intype = 1; // 1 - elementid, 2 - name
}
if (isset($argv[3])) {
   $run_ids = split(",", $argv[3]);
}

$listobject->querystring = "  select elementid from scen_model_element ";
$listobject->querystring .= " where scenarioid = $scid ";
$listobject->querystring .= "    and objectclass = 'modelContainer' ";
// this gets us only ICPRB segments, we can do the CBP ones later when we need to
switch ($intype) {
   case 1:
   $listobject->querystring .= "    and elementid  = $elname ";
   break;

   case 2:
   $listobject->querystring .= "    and elemname = '$elname' ";
   break;
}
print("$listobject->querystring \n");
$listobject->performQuery();
$outlets = $listobject->queryrecords;

foreach ($outlets as $thisoutlet) {
   $elementid = $thisoutlet['elementid'];
   print("Running tree based at outlet $elementid \n");
   foreach ($run_ids as $thisid) {
      print("Beginning runid $thisid for $elementid \n");
      $prop_array = array('run_mode' => $thisid, 'debug' => 0);
      runTree($listobject, $elementid, $sleep_factor, $max_simultaneous, $startdate, $enddate, $thisid, $cache_date, $prop_array);
   }
}


?>