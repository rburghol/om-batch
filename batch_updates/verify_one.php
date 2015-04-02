<?php
$noajax = 1;
$projectid = 3;
$scid = 28;
include('/var/www/html/wooommdev/xajax_modeling.element.php');

error_reporting(E_ALL);

if ( ($argv[1] == '--help') or (count($argv) < 2)) {
   print("Usage: php verify_one.php elementid runid [strict q/area=1] [startdate] [enddate] \n");
   print("** Note: Use strict = -1 to override verification \n");
   die;
}

$recids = split(",",$argv[1]);
$runid = $argv[2];
if (isset($argv[3])) {
   $strict = $argv[3];
} else {
   $strict = 1;
}
if (isset($argv[4])) {
   $startdate = $argv[4];
} else {
   $startdate = '1984-01-01';
}
if (isset($argv[5])) {
   $enddate = $argv[5];
} else {
   $enddate = '2005-12-31';
}

foreach ($recids as $recid) {
   print("Summarizing $recid \n");
   summarizeRun($listobject, $recid, $runid, $startdate, $enddate, 1, $strict);
   $rundata = retrieveRunSummary($listobject, $recid, $runid);
   print($rundata['run_summary'] . "\n");
}
print_r($rundata);

?>
