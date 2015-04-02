<?php
$noajax = 1;
$projectid = 3;
$scid = 28;
include("./config.php");
include("./lib_verify.php");
include("./cova/lib_batchmodel.php");
error_reporting(E_ALL);

$startdate = '1984-01-01';
$enddate = '2005-12-31';

$scenid = $argv[1];
$riverseg = $argv[2];
$runid = $argv[2];
$debug = 1;
$recid = getCOVACBPContainer($listobject, $scenid, $riverseg);


print("Summarizing $recid \n");
summarizeRun($listobject, $recid, $runid, $startdate, $enddate, 1);
$rundata = retrieveRunSummary($listobject, $recid, $runid);
print($rundata['run_summary'] . "\n");

//print_r($rundata);

?>
