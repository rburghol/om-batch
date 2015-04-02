<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");
$userid = 1;

$scid = 28;


$listobject->querystring = "  select elementid from scen_model_element ";
$listobject->querystring .= " where scenarioid = $scid ";
$listobject->querystring .= "    and objectclass = 'modelContainer' ";
$listobject->querystring .= "    and elemname = '' ";
print("$listobject->querystring \n");
$listobject->performQuery();
$heap = $listobject->queryrecords;

$current_order = 1;

while ( count($heap) > 0 ) {
   // if it is a 1st order segment, we should have no need to re-run it
   $thisrec = array_shift($heap);
   $recid = $thisrec['elementid'];
   print("Deleting $recid \n");
   $output = deleteModelElement($recid,1);
   print("Result: " . $output['innerHTML'] . " \n");
}

print("Done.\n");
   
?>