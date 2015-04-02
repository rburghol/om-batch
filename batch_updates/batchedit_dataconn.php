<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./config.php');
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

$listobject->querystring = " select elementid, elemname from scen_model_element where scenarioid = $scid and objectclass in ( 'dataConnectionObject') ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();

$elrecs = $listobject->queryrecords;
$debug = 0;
$i = 0;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];
   print("Updating $elid <br>\n");
   $prop_array = array('host'=>'deq1.bse.vt.edu');
   updateObjectProps($projectid, $elid, $prop_array);
   
   $i++;
   //break;
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
