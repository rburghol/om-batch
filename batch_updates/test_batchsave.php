<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ALL);
print("Un-serializing Model Object <br>");

$listobject->querystring = " select elementid from scen_model_element";
$listobject->performQuery();

$elrecs = $listobject->queryrecords;
$debug = 0;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];

   $props = getElementPropertyList($elid);
   #print_r($props);
   $props_xml = '';
   # need to get the names of the operators on this object to add to public variables, 
   # since we have not-reconstituted the operators yet
   
   $result = $serializer->serialize($props);
   if($result === true) {
      $props_xml = $serializer->getSerializedData();
   }

   $listobject->querystring = " update scen_model_element set elemprops = '$props_xml' where elementid = $elid ";
   $listobject->performQuery();

   #print($ih);
   print("Element $elid Saved <br>");
}

?>
</body>

</html>