<?php

$noajax = 1;
include('./xajax_modeling.element.php');
if (count($argv) < 3) {
   print("USAGE: php fn_getMergedNHDBasin.php pointname lat lon [store_shape=0] [debug=0]\n");
   die;
}
if (isset($argv[4])) {
   $store_shape = $argv[4];
} else {
   $store_shape = 0;
}
list($pointname, $latdd, $londd, $debug) = array($argv[1], $argv[2], $argv[3], $argv[5]);
$outlet_info = findNHDSegment($usgsdb, $latdd, $londd);
$outlet = $outlet_info['comid'];
$area = $outlet_info['areasqkm'];
$carea = $outlet_info['cumdrainag'];
print("Outlet COMID : $outlet \n");
print("Finding Tribs $outlet \n");
$result = findTribs($usgsdb,$outlet, $debug);
print("Individual Tribs : \n");
$seglist = $result['segment_list'];
print_r($result['segment_list']);
$result = findMergedTribs($usgsdb,$outlet, $debug);
print("\\nMerged Tribs : \n");
print_r($result['merged_segments']);
print("\n");

print("Outlet COMID : $outlet \n");
print("Outlet Cumulative Area : $carea \n");
print("Outlet Local Area : $area \n");

if ($store_shape) {
   print("Storing shape in database\n");
   $wktgeom = getMergedNHDShape($usgsdb, $seglist,$result['merged_segments'], 1);
   storeNHDMergedShape($usgsdb, $outlet, $wktgeom, 1, $debug);
}
print("Finished.\n");

?>
