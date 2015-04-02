<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

$src_elementid = 52270;
$dest_elementid = 52272;
$copy_ops[] = array(
   'src_opname'=>'read_run_mode',
   'dest_opname' => 'read_run_mode'
);
$copy_ops[] = array(
   'src_opname'=>'send_run_mode',
   'dest_opname' => 'send_run_mode'
);

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

foreach ($copy_ops as $thiscopy) {
   $src_opname = $thiscopy['src_opname'];
   $dest_opname = $thiscopy['dest_opname'];
   $innerHTML .= copySubComponent($src_elementid, $src_opname, $dest_elementid, $dest_opname);
}
/*
$listobject->querystring = " select elementid from scen_model_element where scenarioid = $scid ";
$listobject->performQuery();
$prop_array = array('log2db'=>2);

$elrecs = $listobject->queryrecords;
$debug = 0;
$i = 0;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];

   updateObjectProps($projectid, $elid, $prop_array);
   $i++;

   #print($ih);
   print("Element $elid Saved <br>");
}
*/
print("Finished: $innerHTML<br>");

?>
</body>

</html>