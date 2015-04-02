<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 20;

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

$listobject->querystring = " select elementid, elemname from scen_model_element where scenarioid = $scid and objectclass = 'hydroImpoundment' ";
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
   print("Copying impoundment info from $name \n");
   // get parent container
   $listobject->querystring = " select elementid from scen_model_element where scenarioid = 28 and elemname = '$cbp_seg' and objectClass = 'modelContainer' ";
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   $pid = $listobject->getRecordValue(1,'elementid');
   $child_rec = getChildComponentType($listobject, $pid, 'hydroImpoundment', 1);
   $thischild = $child_rec[0];
   $destid = $thischild['elementid'];
   $prop_array = array('name'=>$name, 'description' => $cbp_seg, 'initstorage' => $cbp_initstorage, 'unusable_storage' => $cbp_unusable_storage, 'maxcapacity' => $cbp_maxcapacity);
   print("Setting properties on $destid -- 'name' => $name, 'description' => $cbp_seg, 'initstorage' => $cbp_initstorage, 'unusable_storage' => $cbp_unusable_storage, 'maxcapacity' => $cbp_maxcapacity \n");
   updateObjectProps($projectid, $destid, $prop_array);
   $subcomps = array('storage_stage_area','flowby','imp_off');
   
   foreach ($subcomps as $thiscomp) {
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
