<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

if (count($argv) < 3) {
   print("Usage: php delete_subcomp.php elementid opname \n");
   die;
}

$elementid = $argv[1];
$opname = $argv[2];
echo "Calling deleteSubComponent($elementid, $opname) \n";
$output = deleteSubComponent($elementid, $opname);
echo "Done: " . $output . "\n";
?>
