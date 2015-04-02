<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
error_reporting(E_ERROR);

if ( count($argv) < 4 ) {
   print("Usage: edit_subcomp_props.php elementid subcomp_name \"prop=value\" \n");
   die;
}

if (isset($argv[1])) {
   $elid = $argv[1];
} else {
   $elid = '';
}
if (isset($argv[2])) {
   $subcomp_name = $argv[2];
} else {
   $subcomp_name = '';
}
list($prop,$value) = split('=', $argv[3]);


$loadres = unSerializeSingleModelObject($elid);
$thisobject = $loadres['object'];

if (is_object($thisobject)) {
   print("Trying to set $subcomp_name -> $prop = $value \n");
   $thisobject->processors[$subcomp_name]->$prop = $value;
   saveObjectSubComponents($listobject, $thisobject, $elid );
}
   
print("Finished.\n");

?>