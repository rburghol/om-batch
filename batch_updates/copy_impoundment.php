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
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");
if (isset($argv[1])) {
   $singleid = $argv[1];
} else {
   $singleid = '';
}

$listobject->querystring = "  select elementid, elemname ";
$listobject->querystring .= " from scen_model_element ";
$listobject->querystring .= " where scenarioid = $scid ";
$listobject->querystring .= " and objectclass = 'hydroImpoundment' ";
if ($singleid <> '') {
   $listobject->querystring .= " and elementid = $singleid ";
}
print("$listobject->querystring ; <br>");
$listobject->performQuery();

$elrecs = $listobject->queryrecords;
$debug = 0;
$i = 0;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];
   $obres = unserializeSingleModelObject($elid);
   $srcob = $obres['object'];
   $name = $srcob->name;
   $cbp_seg = $srcob->description;
   $cbp_initstorage = $srcob->initstorage;
   $cbp_unusable_storage = $srcob->unusable_storage;
   $cbp_maxcapacity = $srcob->maxcapacity;
   $sub_comps = array_keys($srcob->processors);
   print("sub-components on $name: \n");
   print_r($sub_comps);
   print(" \n");
   print("Copying impoundment info from $name \n");
   
   // get parent container
   $listobject->querystring = " select elementid from scen_model_element where scenarioid = $dest_scen and elemname = '$cbp_seg' and objectClass = 'modelContainer' ";
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   $pid = $listobject->getRecordValue(1,'elementid');
   $child_rec = getChildComponentType($listobject, $pid, 'hydroImpoundment', 1);
   $thischild = $child_rec[0];
   $destid = $thischild['elementid'];
   $prop_array = array('name'=>$name, 'description' => $cbp_seg, 'initstorage' => $cbp_initstorage, 'unusable_storage' => $cbp_unusable_storage, 'maxcapacity' => $cbp_maxcapacity);
   print("Setting properties on $destid -- 'name' => $name, 'description' => $cbp_seg, 'initstorage' => $cbp_initstorage, 'unusable_storage' => $cbp_unusable_storage, 'maxcapacity' => $cbp_maxcapacity \n");
   updateObjectProps($projectid, $destid, $prop_array);
   
   foreach ($sub_comps as $thiscomp) {
      print("Trying to add Sub-comp $thiscomp to Element $destid <br>");
      $cr = copySubComponent($elid, $thiscomp, $destid, $thiscomp);
      //print("$cr<br>");
      print("Sub-comp $thiscomp added to Element $destid <br>");
   }
   
   $i++;
   //break;
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
