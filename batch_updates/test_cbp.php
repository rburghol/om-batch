<?php

$noajax = 1;
$projectid = 3;
$scid = 28;
include("./xajax_modeling.element.php");
include("./lib_verify.php");

error_reporting(E_ALL);

// patch ICPRB watershed linkages and names
// two modes: 
   // 1) check for dropped linkages (upstream), missing to_node (downstream) - check for unintended links to new records
   // 2) process changes

// types of changes that may take place:
// watershed object name
// downstream linkages
// upstream linkages
// land use update/insert

$cbp_listobject->querystring = "select shed_merge from icprb_watersheds limit 10";
$cbp_listobject->performQuery();
$cbp_listobject->showList();

?>