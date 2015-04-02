<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

$startdate = '1984-01-01';
$enddate = '2005-12-31';
$cache_date = '2010-11-01';

if (isset($_GET['elementid'])) {
   $elid = $_GET['elementid'];
   $format = 'table';
   $runid = -1;
} else {
   $elid = $argv[1];
   $format = $argv[2];
   $runid = $argv[3];
}
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}
if (isset($_GET['format'])) {
   $format = $_GET['format'];
}
if (isset($_GET['host'])) {
   $host = $_GET['host'];
} else {
   $host = $serverip;
}

$container_tree = getStatusTree($listobject, $elid, $runid, $host);
switch ($format) {
   case 'table':
      $formatted = formatPrintContainer($container_tree);
      print($formatted);
   break;

   default:
      echo "Number of elements in tree = " . count($container_tree) . "\n";
      echo "Container Tree " . print_r($container_tree, 1) . "\n";
   break;
   
}
$tc = checkTreeRunDate($listobject, $elid, $runid, $startdate, $enddate, $cache_date);

print("\nTime Check - $tc \n");
?>
