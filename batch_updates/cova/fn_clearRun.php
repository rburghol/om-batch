<?php


// checks for files/runs fidelity - clears them if they fail vertain tests

// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include("./xajax_modeling.element.php");
include("../lib_verify.php");
include("./lib_batchmodel.php");

if (isset($argv[1])) {
   $elementid = $argv[1];
} else { 
   print("Usage: php fn_clearRun.pgp elementid runid \n");
   die;
}
if (isset($argv[2])) {
   $runid = $argv[2];
} else {
   print("Usage: php fn_clearRun.pgp elementid runid \n");
   die;
}
error_reporting(E_ALL);
print("Clearing elementid $elementid, Run - $runid \n");
deleteRunRecord($listobject, $elementid, $runid);
print("Done.\n");


?>
