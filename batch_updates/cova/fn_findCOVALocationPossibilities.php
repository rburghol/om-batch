<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);

if (count($argv) < 3) {
   print("Usage: fn_findCOVALocationPossibilities.php scenarioid latdd londd \n");
   die;
}
$scenid = $argv[1];
$latdd = $argv[2];
$londd = $argv[3];
$debug = 1;
$options = findCOVALocationPossibilities($listobject, $scenid, $latdd, $londd);
foreach ($options as $key => $val) {
   $val['the_geom'] = substr($val['the_geom'], 0, 32) . " ... (truncated) ";
   $options[$key] = $val;
}
print("Options: " . print_r($options,1) . " <br>\n");


?>
