<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 20;
$dest_scen = 28;

include_once('xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
error_reporting(E_ERROR);
$singleid = '176635';

if (count($argv) < 3) {
   print("Usage: php copy_subcomps.php src_id dest_id [sub1[|newname],sub2,...] \n");
   die;
}

$srcid = $argv[1];
$destid = $argv[2];

// get the list of all sub-comps from the source
$obres = unserializeSingleModelObject($srcid);
$srcob = $obres['object'];
$name = $srcob->name;
// if we passed in a 3rd argument, copy JUST those in the 3rd argument (can be a csv)
// otherwise, we copy ALL sub-processors on that object
if (isset($argv[3])) {
   $subcomps = split(",", $argv[3]); 
} else {
   $subcomps = array_keys($srcob->processors);
}
print("sub-components on $name: \n");
print_r($subcomps);
print(" \n");
print("Copying info from $name \n");
$i = 0;
$props = array();
foreach ($subcomps as $thiscomp) {
   $parms = split("\|",$thiscomp);
   print("PArms: " . print_r($parms,1) . "\n");
   list($srcname,$destname) = split("\|",$thiscomp);
   if ($destname == '') {
      $destname = $srcname;
   }
   print("Trying to add Sub-comp $srcname to Element $destid as $destname <br>");
   $cr = copySubComponent($srcid, $srcname, $destid, $destname);
   print("$cr<br>");
   print("Sub-comp $thiscomp added to Element $destid <br>\n");
   $i++;
   if (isset($srcob->$srcname)) {
      $props[$destname] = $srcob->$srcname;
   }
}
if (count($props) > 0) {
   updateObjectProps($projectid, $destid, $props);
}

print("Finished.  Saved $i sub-components.<br>");

?>
</body>

</html>
