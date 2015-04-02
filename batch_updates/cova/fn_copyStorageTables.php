<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
include_once("./lib_batchmodel.php");

if (count($argv) < 1) {
   print("Usage: fn_shell.php param1 [param2,param3,...] \n");
   die;
}
$param1 = $argv[1];
$param2 = $argv[2];
$param3 = $argv[3];
$param4 = $argv[4];
$debug = 1;

// get submatrix from vwp project template and add it to a subcomponent hydoImpSmall
$obres = unserializeSingleModelObject($param1);
$srcob = $obres['object'];
if (!isset($srcob->processors['storage_stage_area'])) {
   print("Can't find storage table on source object\n");
} else {
/*
   print("Copying table " . print_r($src_tbl,1) . " \n to desti object\n");
            $srcob->processors['storage_stage_area']->formatMatrix();
            $orig = $srcob->processors['storage_stage_area']->matrix_formatted;
            print("Original Matrix: " . print_r($orig,1) . "\n");
   $obres2 = unserializeSingleModelObject($param2);
   $destob = $obres2['object'];
            $destob->processors[$param3]->storage_matrix->oneDimArrayToMatrix($orig);
            $destob->processors[$param3]->storage_matrix->formatMatrix();
            $mod = $destob->processors[$param3]->storage_matrix->matrix_formatted;
            print("Final Matrix: " . print_r($mod,1) . "\n");
*/
          
   $src_tbl = $srcob->processors['storage_stage_area']->matrix;
   print("Copying table " . print_r($src_tbl,1) . " \n to dest object\n");
   $obres2 = unserializeSingleModelObject($param2, array(), 0);
   //print(substr($obres2['debug'],0,255) . "\n");
   $destob = $obres2['object'];
   print"Dest Obj has Processor Names: " . print_r(array_keys($destob->processors),1) . " <br>\n";
   $exist = $destob->processors[$param3]->storage_matrix->matrix;
   $numrows = $srcob->processors['storage_stage_area']->matrix->numrows;
   $numcols = $srcob->processors['storage_stage_area']->matrix->numcols;
   print("Replacing " . print_r($exist,1) . " \n on dest object\n");
   $destob->processors[$param3]->matrix = $src_tbl;
   print("Final Matrix " . print_r($destob->processors[$param3]->matrix,1) . " \n on dest object\n");
   print"Preparing to save dest object with " . "Processor Names: " . print_r(array_keys($destob->processors),1) . " <br>\n";
   $res = saveObjectSubComponents($listobject, $destob, $param2, 1, 1);
   //print("Result of subobject save :\n $res \n");
}

?>
