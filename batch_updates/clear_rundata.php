<?php


// checks for files/runs fidelity - clears them if they fail vertain tests

// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include('/var/www/html/om/xajax_modeling.element.php');
   
if (isset($argv[1])) {
   $elementid = $argv[1];
} else { 
   print("You must enter an elementid \n");
}
if (isset($argv[2])) {
   $runid = $argv[2];
} else {
   print("You must enter a run id \n");
   die;
}
error_reporting(E_ALL);
print("Clearing elementid $elementid, Run - $runid \n");
clearStatus($listobject, $elementid, $runid);
removeRunCache($listobject, $elementid, $runid);
print("Done.\n");


?>
