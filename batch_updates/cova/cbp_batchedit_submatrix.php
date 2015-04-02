<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');


if ( count($argv) < 6 ) {
   print("Usage: cbp_batchedit_submatrix.php scenid riverseg elementtype (withdrawal,discharge,) matrix_name \"prop=value\" [function (append,overwrite,delete)]\n");
   die;
}

$scenid = $argv[1];
$riverseg = $argv[2];
$eltype = $argv[3];
$subcomp_name = $argv[4];
list($prop,$value) = split('=', $argv[5]);

if (isset($argv[6])) {
   $function = $argv[6];
} else {
   $function = 'append';
}

if (strlen($riverseg) <= 3) {
   $segs = array();
   $listobject->querystring = " select riverseg from sc_cbp53 where riverseg ilike '$riverseg%' ";
   print("Looking for river abbreviation match <br>\n");
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $thisrec) {
      $segs[] = $thisrec['riverseg'];
   }
} else {
   $segs = split(',',$riverseg);
}
//print_r($segs);

foreach ($segs as $riverseg) {
   print("Looking for CBP parent of $riverseg <br>\n");
   $elid = getCOVACBPContainer($listobject, $scenid, $riverseg);
   print("CBP parent ID = $elid, looking for children of type $eltype <br>\n");
   
   switch ($eltype) {
      // add other types later
      case 'withdrawal':
      print("Searching for withdrawals\n");
      $recs = getCOVAWithdrawals($listobject, $elid);
      break;
      
      case 'discharge':
      $recs = getCOVAPointSources($listobject, $elid);
      break;
      
      default:
      $recs = array();
      break;
   }
   
   foreach ($recs as $thisrec) {
      $elid = $thisrec['elementid'];
      $elemname = $thisrec['elemname'];
      print("Editing $subcomp_name on $elemname ($elid) \n");
      $loadres = unSerializeSingleModelObject($elid);
      $thisobject = $loadres['object'];

      if (is_object($thisobject)) {
         if (isset($thisobject->processors[$subcomp_name])) {
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
            saveObjectSubComponents($listobject, $thisobject, $elid );
         }
      }
   }
}
   
print("Finished.\n");

?>