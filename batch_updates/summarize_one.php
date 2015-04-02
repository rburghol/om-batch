<?php

$noajax = 1;
$projectid = 3;
$scid = 28;
include("./xajax_modeling.element.php");
//include("./lib_verify.php");

error_reporting(E_ALL);

// patch ICPRB watershed linkages and names
// two modes: 
   // 1) check for dropped linkages (upstream), missing to_node (downstream) - check for unintended links to new records
   // 2) process changes

// types of changes that may take place:
// watershed object name
// downstream linkages
// upstream linkages
// land use update/insert
if ( ($argv[1] == '--help') or (count($argv) < 5)) {
   print("Usage: php summarize_one.php elementid runid rundate startdate enddate \n");
   die;
}
$been_run = checkRunDate($listobject, $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], 1);

print("Has Been Run: $been_run \n");
?>
