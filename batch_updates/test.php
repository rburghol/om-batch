<?php
$noajax = 1;
$projectid = 3;
$scid = 28;
include("./config.php");
include("./lib_verify.php");
error_reporting(E_ALL);

$startdate = '1984-01-01';
$enddate = '2005-12-31';

$recid = $argv[1];
$runid = $argv[2];

summarizeRun($listobject, $recid, $runid, $startdate, $enddate, 1);
$rundata = retrieveRunSummary($listobject, $recid, $runid);
$rundata['message'] = $rundata['run_summary'];

print_r($rundata);

?>
