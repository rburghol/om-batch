<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

if (count($argv) < 3) {
   print("Usage: fn_getStreamSection.php startseg endseg [debug=0] \n");
   die;
}
$startseg = $argv[1];
$endseg = $argv[2];
if (isset($argv[3])) {
   $debug = $arrgv[3];
} else {
   $debug = 1;
}
$riverseg_col = 'riverseg';
$tablename = 'sc_cbp53';

$list = getStreamSection($listobject, $startseg, $endseg, $debug);

print("Segment info: " . print_r($list,1) . " <br>\n");


?>
