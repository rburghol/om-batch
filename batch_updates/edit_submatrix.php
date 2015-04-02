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
   print("Usage: edit_submatrix.php elementid matrix_name \"prop1=value1\"  [function (append,overwrite,delete)]\n");
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

if (isset($argv[4])) {
   $function = $argv[4];
} else {
   $function = 'append';
}



$loadres = unSerializeSingleModelObject($elid);
$thisobject = $loadres['object'];

if (is_object($thisobject)) {
   print("Editing Matrix $subcomp_name\n ");
   $thisobject->processors[$subcomp_name]->formatMatrix();
   $orig = $thisobject->processors[$subcomp_name]->matrix_formatted;
   print("Original Matrix: " . print_r($orig,1) . "\n");
   $orig[$prop] = $value;
   ksort($orig);
   print("Modified Matrix: " . print_r($orig,1) . "\n");
   $thisobject->processors[$subcomp_name]->oneDimArrayToMatrix($orig);
   $thisobject->processors[$subcomp_name]->formatMatrix();
   $mod = $thisobject->processors[$subcomp_name]->matrix_formatted;
   print("Final Matrix: " . print_r($mod,1) . "\n");
   //$thisobject->processors[$subcomp_name]->$prop = $value;
   saveObjectSubComponents($listobject, $thisobject, $elid );
}
   
print("Finished.\n");

?>