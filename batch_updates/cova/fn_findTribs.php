<?php

$noajax = 1;
include('./xajax_modeling.element.php');

if (count($argv) < 2) {
   print("Usage: php fn_findTribs.php operation(1=comid,2=lat/lon) comid/lat [lon] [debug=0]\n");
   die;
}

$debug = 1;
$op = $argv[1];

switch ($op) {
   case 2:
      $outlet_info = findNHDSegment($usgsdb, $argv[2], $argv[3]);
      $comid = $outlet_info['comid'];
      $ocopy = $outlet_info;
      $ocopy['wktgeom'] = substr($ocopy['wktgeom'],0,64) . " ... ";
      print("Outlet Information: " . print_r($ocopy,1) . "\n");
   break;
   
   default:
      $comid = $argv[2];
   break;
}
   
$tribs = findTribs($usgsdb, $comid, 0);

print("Tribs: \n" . print_r($tribs['segment_list'],1) . "\n");

$cinfo = getNHDChannelInfo($usgsdb, $comid, $tribs['segment_list'], 'ft', $debug);

print("Segment Info: \n" . print_r($cinfo,1) . "\n");
?>
